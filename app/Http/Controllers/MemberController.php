<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class MemberController extends Controller
{
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
        $filename = "members_{$fromDate->toDateString()}_to_{$toDate->toDateString()}.csv";
        $columns = array_values(array_diff(
            Schema::getColumnListing($this->model->getTable()),
            self::EXPORT_EXCLUDED_COLUMNS
        ));

        return response()->streamDownload(function () use ($columns, $fromDate, $toDate) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM lets Microsoft Excel display Burmese and other Unicode text correctly.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);

            $this->model
                ->query()
                ->select($columns)
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->orderBy('created_at')
                ->chunk(500, function ($members) use ($columns, $output) {
                    foreach ($members as $member) {
                        fputcsv($output, array_map(
                            fn ($column) => $this->excelSafeValue($member->getRawOriginal($column)),
                            $columns
                        ));
                    }
                });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function excelSafeValue(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
