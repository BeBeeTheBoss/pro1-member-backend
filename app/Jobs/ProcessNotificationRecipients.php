<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessNotificationRecipients implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public int $tries = 3;

    public function __construct(
        private readonly int $notificationId,
        private readonly string $choice,
        private readonly ?int $userId = null,
        private readonly bool $resetRecipients = false,
        private readonly bool $sendPush = true,
    ) {}

    public function handle(): void
    {
        $notification = Notification::find($this->notificationId);

        if (!$notification) {
            return;
        }

        if ($this->resetRecipients) {
            UserNotification::where('notification_id', $notification->id)->delete();
        }

        match ($this->choice) {
            'all' => $this->processQuery(
                User::query()->whereNotNull('id'),
                $notification
            ),
            'specific' => $this->processQuery(
                User::query()->where('id', $this->userId),
                $notification
            ),
            'excel' => $this->processRecipientFile($notification),
            default => null,
        };
    }

    private function processQuery($query, Notification $notification): void
    {
        $query
            ->select('id', 'expo_push_token')
            ->orderBy('id')
            ->chunkById(1000, function ($users) use ($notification) {
                $this->storeUserNotifications($users, $notification);
                if ($this->sendPush) {
                    $this->sendPushNotifications($users, $notification);
                }
            });
    }

    private function processRecipientFile(Notification $notification): void
    {
        if (!$notification->recipient_file || !Storage::disk('public')->exists($notification->recipient_file)) {
            return;
        }

        $path = Storage::disk('public')->path($notification->recipient_file);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $rows = $extension === 'csv' || $extension === 'txt'
            ? $this->csvRows($path)
            : $this->xlsxRows($path);

        $firstRow = null;
        $header = null;
        $phones = [];
        $idCards = [];
        $processedUserIds = [];

        foreach ($rows as $index => $row) {
            if ($firstRow === null) {
                $firstRow = $row;
                $hasHeader = collect($firstRow)->contains(function ($value) {
                    return in_array($this->normalizeCell($value), ['phone', 'idcard', 'id_card', 'phone_number'], true);
                });

                if ($hasHeader) {
                    $header = array_map(fn($h) => $this->normalizeCell($h), $firstRow);
                    continue;
                }
            }

            [$phone, $idCard] = $this->extractRecipientKeys($row, $header);

            if ($phone !== '') {
                $phones[$phone] = $phone;
            }

            if ($idCard !== '') {
                $idCards[$idCard] = $idCard;
            }

            if (($index + 1) % 1000 === 0) {
                $this->processRecipientKeys($phones, $idCards, $processedUserIds, $notification);
                $phones = [];
                $idCards = [];
            }
        }

        $this->processRecipientKeys($phones, $idCards, $processedUserIds, $notification);
    }

    private function processRecipientKeys(array $phones, array $idCards, array &$processedUserIds, Notification $notification): void
    {
        if (empty($phones) && empty($idCards)) {
            return;
        }

        $query = User::query()
            ->where(function ($query) use ($phones, $idCards) {
                if (!empty($phones)) {
                    $query->whereIn('phone', array_values($phones));
                }

                if (!empty($idCards)) {
                    if (!empty($phones)) {
                        $query->orWhereIn('idcard', array_values($idCards));
                    } else {
                        $query->whereIn('idcard', array_values($idCards));
                    }
                }
            });

        $query
            ->select('id', 'expo_push_token')
            ->orderBy('id')
            ->chunkById(1000, function ($users) use (&$processedUserIds, $notification) {
                $users = $users->reject(function ($user) use (&$processedUserIds) {
                    if (isset($processedUserIds[$user->id])) {
                        return true;
                    }

                    $processedUserIds[$user->id] = true;

                    return false;
                });

                if ($users->isEmpty()) {
                    return;
                }

                $this->storeUserNotifications($users, $notification);
                if ($this->sendPush) {
                    $this->sendPushNotifications($users, $notification);
                }
            });
    }

    private function storeUserNotifications($users, Notification $notification): void
    {
        $now = now();
        $rows = [];

        foreach ($users as $user) {
            $rows[] = [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            UserNotification::insert($chunk);
        }
    }

    private function sendPushNotifications($users, Notification $notification): void
    {
        $tokens = $users
            ->pluck('expo_push_token')
            ->filter()
            ->values()
            ->all();

        if (!empty($tokens)) {
            sendPushNotification($tokens, $notification->title, $notification->message);
        }
    }

    private function extractRecipientKeys(array $row, ?array $header): array
    {
        if ($header) {
            $mapped = [];

            foreach ($header as $colIndex => $key) {
                $mapped[$key] = $row[$colIndex] ?? null;
            }

            return [
                $this->normalizeCell($mapped['phone'] ?? $mapped['phone_number'] ?? null),
                $this->normalizeCell($mapped['idcard'] ?? $mapped['id_card'] ?? null),
            ];
        }

        return [
            $this->normalizeCell($row[0] ?? null),
            $this->normalizeCell($row[1] ?? null),
        ];
    }

    private function csvRows(string $path): \Generator
    {
        if (($handle = fopen($path, 'r')) === false) {
            return;
        }

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null]) {
                    continue;
                }

                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }

    private function xlsxRows(string $path): \Generator
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return;
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                        continue;
                    }

                    $text = '';
                    if (isset($si->r)) {
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return;
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (!$sheet || !isset($sheet->sheetData)) {
            return;
        }

        foreach ($sheet->sheetData->row as $rowNode) {
            $rowData = [];
            foreach ($rowNode->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                if ($ref !== '') {
                    $columnIndex = $this->columnIndexFromRef($ref);
                    while (count($rowData) < $columnIndex) {
                        $rowData[] = '';
                    }
                }

                $value = isset($cell->v) ? (string) $cell->v : '';
                $type = (string) ($cell['t'] ?? '');
                $rowData[] = $type === 's'
                    ? ($sharedStrings[(int) $value] ?? '')
                    : $value;
            }

            yield $rowData;
        }
    }

    private function normalizeCell($value): string
    {
        return strtolower(trim((string) ($value ?? '')));
    }

    private function columnIndexFromRef(string $cellRef): int
    {
        if (!preg_match('/^([A-Z]+)/i', $cellRef, $matches)) {
            return 0;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = ($index * 26) + (ord($char) - ord('A') + 1);
        }

        return max(0, $index - 1);
    }
}
