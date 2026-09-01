<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class MemberController extends Controller
{
    private const EXPORT_HEADINGS = [
        'branch_code' => 'Branch',
        'device_name' => 'Device Type',
    ];

    private const BRANCH_NAMES = [
        'MM-101' => 'Lanthit Branch',
        'MM-102' => 'Theikpan Branch',
        'MM-103' => 'Satsan Branch',
        'MM-104' => 'East Dagon Branch',
        'MM-105' => 'Mawlamyine Branch',
        'MM-106' => 'Tampawaddy Branch',
        'MM-107' => 'Hlaing Tharyar Branch',
        'MM-108' => 'Aye Tharyar Branch',
        'MM-109' => 'Minglardon Branch',
        'MM-110' => 'Bago Branch',
        'MM-112' => 'Pro1 Plus (Terminal-M)',
        'MM-113' => 'South Dagon Branch',
        'MM-114' => 'Da Nyin Gone Branch',
        'MM-115' => 'Nay Pyi Taw Branch',
    ];

    private const EXPORT_EXCLUDED_COLUMNS = [
        'id',
        'password',
        'remember_token',
        'updated_at',
        'image',
        'expo_push_token',
        'device_id',
        'is_active',
        'device_type',
        'total_usage_in_seconds',
        'total_usage_time_in_seconds',
        'streak_chanllenge_count',
        'streak_challenge_count',
        'current_streak',
        'used_keys',
        'keys',
        'address',
        'nationality',
        'nrc',
        'nrc_division_id',
        'nrc_name_id',
        'nrc_type',
        'nrc_number',
        'passport',
        'division_id',
        'township_id',
        'branch_id',
        'record_noti_status',
        'register_type',
        'reset_password',
        'register_device_type',
        'created_by',
        'updated_by',
        'remember_token_old',
        'expo_push_token_old',
    ];

    public function __construct(protected User $model) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $members = $this->model
            ->query()
            ->select('id', 'name', 'idcard', 'phone', 'gender', 'birth_date', 'image', 'created_at')
            ->when($search !== '', function ($query) use ($search) {
                $normalizedSearch = strtolower(str_replace(' ', '', $search));

                $query->where(function ($q) use ($search, $normalizedSearch) {
                    $q->whereRaw("LOWER(REPLACE(name, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhereRaw("LOWER(REPLACE(idcard, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhereRaw("LOWER(REPLACE(phone, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('idcard', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(function ($member) {
                $member->image = $member->image ? url('storage/profile_images/'.$member->image) : null;

                return $member;
            });

        $user = Auth::guard('admin')->user();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
            ],
            'user' => $user,
        ]);
    }

    public function destroy($id)
    {
        info($id);

        return redirect()->route('members')->with('success', 'Member deleted successfully');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $fromDate = Carbon::parse($validated['from_date'])->startOfDay();
        $toDate = Carbon::parse($validated['to_date'])->endOfDay();
        $filename = "members_{$fromDate->toDateString()}_to_{$toDate->toDateString()}.xlsx";
        $columns = array_values(array_diff(
            Schema::getColumnListing($this->model->getTable()),
            self::EXPORT_EXCLUDED_COLUMNS
        ));
        $columnTypes = array_combine(
            $columns,
            array_map(
                fn ($column) => Schema::getColumnType($this->model->getTable(), $column),
                $columns
            )
        );

        $headings = array_map(
            fn ($column) => self::EXPORT_HEADINGS[$column]
                ?? str($column)->replace('_', ' ')->title()->toString(),
            $columns
        );
        $rows = $this->model
            ->query()
            ->select($columns)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at')
            ->cursor()
            ->map(fn ($member) => array_map(
                fn ($column) => $this->formatExportValue(
                    $column,
                    $columnTypes[$column],
                    $member->getRawOriginal($column)
                ),
                $columns
            ));
        $path = app(SimpleXlsxWriter::class)->write($headings, $rows);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function formatExportValue(string $column, string $type, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($column === 'branch_code') {
            return self::BRANCH_NAMES[$value] ?? $value;
        }

        if ($column === 'device_name') {
            return str_contains(strtolower((string) $value), 'iphone') ? 'IOS' : 'Android';
        }

        if (in_array($type, ['datetime', 'timestamp', 'datetime_tz', 'timestamp_tz'], true)) {
            return Carbon::parse($value)->format('d-M-Y h:i A');
        }

        if ($type === 'date') {
            return Carbon::parse($value)->format('d-M-Y');
        }

        return $value;
    }
}
