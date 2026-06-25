<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessNotificationRecipients;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function __construct(protected Notification $model) {}

    public function index()
    {

        $notifications = $this->model->where('is_manual', true)->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('Notifications/Index', [
            'notifications' => NotificationResource::collection($notifications),
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('Notifications/Create', [
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'choice' => 'required|in:all,specific,excel',
            'user_id' => 'required_if:choice,specific|nullable|exists:users,id',
            'recipient_file' => 'required_if:choice,excel|nullable|file|mimes:xlsx,csv,txt',
            'image' => 'nullable|image',
        ]);

        $notification = $this->model->create([
            'title' => $request->title,
            'message' => $request->message,
            'recipient' => $request->choice,
            'is_manual' => true
        ]);

        if ($request->choice === 'excel') {
            $this->storeRecipientFile($notification, $request);
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('notifications', $image, $filename);
            $notification->image = $filename;
            $notification->save();
        }

        ProcessNotificationRecipients::dispatch(
            $notification->id,
            $request->choice,
            $request->choice === 'specific' ? (int) $request->user_id : null,
        );

        return redirect()->route('notifications')->with('success', 'Notification created successfully. Recipients are being processed in the background.');
    }

    private function resolveUsersFromRecipientFile(Request $request): Collection
    {
        $file = $request->file('recipient_file');
        if (!$file) {
            return collect();
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $rows = $extension === 'csv' || $extension === 'txt'
            ? $this->parseCsvRows($file->getRealPath())
            : $this->parseXlsxRows($file->getRealPath());

        if (empty($rows)) {
            return collect();
        }

        $firstRow = $rows[0] ?? [];
        $hasHeader = collect($firstRow)->contains(function ($value) {
            $normalized = $this->normalizeCell($value);
            return in_array($normalized, ['phone', 'idcard', 'id_card', 'phone_number'], true);
        });

        $phoneSet = [];
        $idCardSet = [];

        foreach ($rows as $index => $row) {
            if ($hasHeader && $index === 0) {
                continue;
            }

            if ($hasHeader) {
                $header = array_map(fn($h) => $this->normalizeCell($h), $firstRow);
                $mapped = [];
                foreach ($header as $colIndex => $key) {
                    $mapped[$key] = $row[$colIndex] ?? null;
                }

                $phone = $this->normalizeCell($mapped['phone'] ?? $mapped['phone_number'] ?? null);
                $idCard = $this->normalizeCell($mapped['idcard'] ?? $mapped['id_card'] ?? null);
            } else {
                $phone = $this->normalizeCell($row[0] ?? null);
                $idCard = $this->normalizeCell($row[1] ?? null);
            }

            if ($phone !== '') {
                $phoneSet[] = $phone;
            }

            if ($idCard !== '') {
                $idCardSet[] = $idCard;
            }
        }

        $phones = array_values(array_unique($phoneSet));
        $idCards = array_values(array_unique($idCardSet));

        if (empty($phones) && empty($idCards)) {
            return collect();
        }

        return User::query()
            ->where(function ($query) use ($phones, $idCards) {
                if (!empty($phones)) {
                    $query->whereIn('phone', $phones);
                }

                if (!empty($idCards)) {
                    if (!empty($phones)) {
                        $query->orWhereIn('idcard', $idCards);
                    } else {
                        $query->whereIn('idcard', $idCards);
                    }
                }
            })
            ->get();
    }

    private function storeRecipientFile(Notification $notification, Request $request): void
    {
        if (!$request->hasFile('recipient_file')) {
            return;
        }

        $file = $request->file('recipient_file');
        $filename = $notification->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        if ($notification->recipient_file) {
            Storage::disk('public')->delete($notification->recipient_file);
        }

        $path = Storage::disk('public')->putFileAs('notification-recipient-files', $file, $filename);

        $notification->update([
            'recipient_file' => $path,
            'recipient_file_original_name' => $file->getClientOriginalName(),
            'recipient_file_mime_type' => $file->getClientMimeType(),
            'recipient_file_size' => $file->getSize(),
        ]);
    }

    private function parseCsvRows(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if ($row === [null] || $row === false) {
                    continue;
                }
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }

    private function parseXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
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
            return [];
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (!$sheet || !isset($sheet->sheetData)) {
            return [];
        }

        $rows = [];
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

                if ($type === 's') {
                    $index = (int) $value;
                    $rowData[] = $sharedStrings[$index] ?? '';
                } else {
                    $rowData[] = $value;
                }
            }
            $rows[] = $rowData;
        }

        return $rows;
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

    public function edit($id)
    {

        $notification = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        if ($notification->recipient === 'specific') {
            $userId = UserNotification::where('notification_id', $notification->id)->value('user_id');
            $notification['user'] = $userId
                ? User::where('id', $userId)->select('id', 'name', 'idcard', 'phone')->first()
                : null;
        }

        $notification->image = $notification->image ? url("storage/notifications/" . $notification->image) : null;
        $notification->recipient_file_url = $notification->recipient_file
            ? route('notifications.recipient-file', $notification->id)
            : null;

        return Inertia::render('Notifications/Edit', [
            'notification' => $notification,
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notifications,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'choice' => 'required|in:all,specific,excel',
            'user_id' => 'required_if:choice,specific|nullable|exists:users,id',
            'recipient_file' => 'nullable|file|mimes:xlsx,csv,txt',
            'image' => 'nullable',
        ]);

        $notification = $this->model->find($request->id);

        if ($request->choice === 'excel' && !$request->hasFile('recipient_file') && !$notification->recipient_file) {
            return back()
                ->withErrors(['recipient_file' => 'Please upload an Excel or CSV file.'])
                ->withInput();
        }

        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
            'recipient' => $request->choice
        ]);

        if ($request->choice === 'excel' && $request->hasFile('recipient_file')) {
            $this->storeRecipientFile($notification, $request);
        }

        if ($request->choice !== 'excel' && $notification->recipient_file) {
            Storage::disk('public')->delete($notification->recipient_file);
            $notification->update([
                'recipient_file' => null,
                'recipient_file_original_name' => null,
                'recipient_file_mime_type' => null,
                'recipient_file_size' => null,
            ]);
        }

        if ($notification->image && !$request->hasFile('image') && $request->image === null) {
            Storage::disk('public')->delete('notifications/' . $notification->image);
            $notification->image = null;
            $notification->save();
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();

            if ($notification->image) {
                Storage::disk('public')->delete('notifications/' . $notification->image);
            }

            Storage::disk('public')->putFileAs('notifications', $image, $filename);
            $notification->image = $filename;
            $notification->save();
        }

        if ($request->choice !== 'excel' || $request->hasFile('recipient_file')) {
            ProcessNotificationRecipients::dispatch(
                $notification->id,
                $request->choice,
                $request->choice === 'specific' ? (int) $request->user_id : null,
                true,
                false,
            );
        }

        return redirect()->route('notifications')->with('success', 'Notification updated successfully. Recipients are being processed in the background.');
    }

    public function downloadRecipientFile($id)
    {
        $notification = $this->model->findOrFail($id);

        if (!$notification->recipient_file || !Storage::disk('public')->exists($notification->recipient_file)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $notification->recipient_file,
            $notification->recipient_file_original_name ?? basename($notification->recipient_file)
        );
    }

    public function destroy($id)
    {
        $notification = $this->model->find($id);

        UserNotification::where('notification_id', $notification->id)->delete();

        if ($notification->image) {
            Storage::disk('public')->delete('notifications/' . $notification->image);
        }

        if ($notification->recipient_file) {
            Storage::disk('public')->delete($notification->recipient_file);
        }

        $notification->delete();
        return redirect()->route('notifications')->with('success', 'Notification deleted successfully');
    }
}
