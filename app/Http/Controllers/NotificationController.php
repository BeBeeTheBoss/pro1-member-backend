<?php

namespace App\Http\Controllers;

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

        $users = match ($request->choice) {
            'all' => User::get(),
            'specific' => User::where('id', $request->user_id)->get(),
            'excel' => $this->resolveUsersFromRecipientFile($request),
            default => collect(),
        };

        if ($request->choice === 'excel' && $users->isEmpty()) {
            return back()
                ->withErrors(['recipient_file' => 'No users matched from the uploaded file.'])
                ->withInput();
        }

        $notification = $this->model->create([
            'title' => $request->title,
            'message' => $request->message,
            'recipient' => $request->choice,
            'is_manual' => true
        ]);

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('notifications', $image, $filename);
            $notification->image = $filename;
            $notification->save();
        }

        $tokens = [];

        foreach ($users as $user) {

            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id
            ]);

            if (!empty($user->expo_push_token)) {
                array_push($tokens, $user->expo_push_token);
            }
        }

        if (!empty($tokens)) {
            sendPushNotification($tokens, $request->title, $request->message);
        }

        return redirect()->route('notifications')->with('success', 'Notification created successfully');
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
            $notification['user'] = User::where('id', UserNotification::where('notification_id', $notification->id)->first()->user_id)->select('id', 'name', 'idcard', 'phone')->first();
        }

        $notification->image = $notification->image ? url("storage/notifications/" . $notification->image) : null;

        return Inertia::render('Notifications/Edit', [
            'notification' => $notification,
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {

        $notification = $this->model->find($request->id);

        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
            'recipient' => $request->choice
        ]);

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

        UserNotification::where('notification_id', $notification->id)->delete();

        $userIds_for_notification = $request->choice === 'all' ? User::pluck('id') : User::where('id', $request->user_id)->pluck('id');

        foreach ($userIds_for_notification as $user_id) {

            UserNotification::create([
                'user_id' => $user_id,
                'notification_id' => $notification->id
            ]);
        }

        return redirect()->route('notifications')->with('success', 'Notification updated successfully');
    }

    public function destroy($id)
    {
        $notification = $this->model->find($id);

        UserNotification::where('notification_id', $notification->id)->delete();

        $notification->delete();
        return redirect()->route('notifications')->with('success', 'Notification deleted successfully');
    }
}
