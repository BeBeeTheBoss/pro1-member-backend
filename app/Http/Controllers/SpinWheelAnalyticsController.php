<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SpinRecord;
use App\Models\SpinWheelChanceDaily;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SpinWheelAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $filters = Validator::make([
            'start_date' => $request->query('start_date', $today),
            'end_date' => $request->query('end_date', $today),
        ], [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ])->validate();

        $startDate = Carbon::createFromFormat('Y-m-d', $filters['start_date'])->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $filters['end_date'])->startOfDay();
        $dateBounds = [$startDate->toDateString(), $endDate->toDateString()];

        $summaryRow = SpinRecord::query()
            ->leftJoin(
                'spin_wheel_chances_daily',
                'spin_records.spin_wheel_chance_daily_id',
                '=',
                'spin_wheel_chances_daily.id'
            )
            ->whereBetween('spin_records.date', $dateBounds)
            ->selectRaw('COUNT(*) as total_spins')
            ->selectRaw('COUNT(DISTINCT spin_records.user_id) as unique_spinners')
            ->selectRaw('COALESCE(SUM(spin_records.reward_points), 0) as reward_points')
            ->selectRaw('COALESCE(AVG(spin_records.reward_points), 0) as average_reward_points')
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'super_prize' THEN 1 ELSE 0 END) as super_prize_count")
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'mega_prize' THEN 1 ELSE 0 END) as mega_prize_count")
            ->first();

        $summary = [
            'total_spins' => (int) ($summaryRow?->total_spins ?? 0),
            'unique_spinners' => (int) ($summaryRow?->unique_spinners ?? 0),
            'reward_points' => (int) ($summaryRow?->reward_points ?? 0),
            'average_reward_points' => round((float) ($summaryRow?->average_reward_points ?? 0), 1),
            'super_prize_count' => (int) ($summaryRow?->super_prize_count ?? 0),
            'mega_prize_count' => (int) ($summaryRow?->mega_prize_count ?? 0),
        ];

        $dailyRows = SpinRecord::query()
            ->leftJoin(
                'spin_wheel_chances_daily',
                'spin_records.spin_wheel_chance_daily_id',
                '=',
                'spin_wheel_chances_daily.id'
            )
            ->whereBetween('spin_records.date', $dateBounds)
            ->selectRaw('spin_records.date as spin_date')
            ->selectRaw('COUNT(*) as spin_count')
            ->selectRaw('COUNT(DISTINCT spin_records.user_id) as unique_spinners')
            ->selectRaw('COALESCE(SUM(spin_records.reward_points), 0) as reward_points')
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'super_prize' THEN 1 ELSE 0 END) as super_prize_count")
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'mega_prize' THEN 1 ELSE 0 END) as mega_prize_count")
            ->groupBy('spin_records.date')
            ->orderBy('spin_records.date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->spin_date)->toDateString());

        $dailyTrend = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(function (Carbon $date) use ($dailyRows) {
                $key = $date->toDateString();
                $row = $dailyRows->get($key);

                return [
                    'key' => $key,
                    'label' => $date->format('M j'),
                    'spin_count' => (int) ($row?->spin_count ?? 0),
                    'unique_spinners' => (int) ($row?->unique_spinners ?? 0),
                    'reward_points' => (int) ($row?->reward_points ?? 0),
                    'super_prize_count' => (int) ($row?->super_prize_count ?? 0),
                    'mega_prize_count' => (int) ($row?->mega_prize_count ?? 0),
                ];
            })
            ->values();

        $isSingleDay = $startDate->isSameDay($endDate);
        $trend = $isSingleDay
            ? $this->hourlyTrend($startDate->toDateString())
            : $dailyTrend;

        $prizeDistribution = SpinRecord::query()
            ->leftJoin(
                'spin_wheel_chances_daily',
                'spin_records.spin_wheel_chance_daily_id',
                '=',
                'spin_wheel_chances_daily.id'
            )
            ->whereBetween('spin_records.date', $dateBounds)
            ->selectRaw("COALESCE(spin_wheel_chances_daily.type, 'unknown') as type")
            ->selectRaw('COUNT(*) as spin_count')
            ->selectRaw('COALESCE(SUM(spin_records.reward_points), 0) as reward_points')
            ->groupBy(DB::raw("COALESCE(spin_wheel_chances_daily.type, 'unknown')"))
            ->orderByDesc('spin_count')
            ->get()
            ->map(fn ($row) => [
                'type' => $row->type,
                'spin_count' => (int) $row->spin_count,
                'reward_points' => (int) $row->reward_points,
            ])
            ->values();

        $remainingInventory = SpinWheelChanceDaily::query()
            ->whereBetween('date', $dateBounds)
            ->select('type', 'points')
            ->selectRaw('SUM(max_times) as remaining_times')
            ->groupBy('type', 'points')
            ->get()
            ->keyBy(fn ($row) => "{$row->type}:{$row->points}");

        $awardedInventory = SpinRecord::query()
            ->join(
                'spin_wheel_chances_daily',
                'spin_records.spin_wheel_chance_daily_id',
                '=',
                'spin_wheel_chances_daily.id'
            )
            ->whereBetween('spin_records.date', $dateBounds)
            ->select('spin_wheel_chances_daily.type', 'spin_wheel_chances_daily.points')
            ->selectRaw('COUNT(*) as awarded_times')
            ->groupBy('spin_wheel_chances_daily.type', 'spin_wheel_chances_daily.points')
            ->get()
            ->keyBy(fn ($row) => "{$row->type}:{$row->points}");

        $inventoryKeys = $remainingInventory->keys()
            ->merge($awardedInventory->keys())
            ->unique()
            ->values();

        $inventory = $inventoryKeys
            ->map(function ($key) use ($remainingInventory, $awardedInventory) {
                [$type, $points] = explode(':', $key, 2);
                $remaining = (int) ($remainingInventory->get($key)?->remaining_times ?? 0);
                $awarded = (int) ($awardedInventory->get($key)?->awarded_times ?? 0);
                $available = $remaining + $awarded;

                return [
                    'key' => $key,
                    'type' => $type,
                    'points' => (int) $points,
                    'available_times' => $available,
                    'awarded_times' => $awarded,
                    'remaining_times' => $remaining,
                    'utilization_percent' => $available > 0
                        ? round(($awarded / $available) * 100, 1)
                        : 0,
                ];
            })
            ->sortBy([
                ['type', 'asc'],
                ['points', 'asc'],
            ])
            ->values();

        $targetSettings = Setting::query()
            ->whereIn('attribute', ['super_prize_target', 'mega_prize_target'])
            ->pluck('value', 'attribute');

        $targets = [
            $this->targetMetrics(
                'super_prize',
                (int) ($targetSettings->get('super_prize_target') ?? 200),
                $summary['super_prize_count'],
                $dailyTrend,
                $endDate->toDateString()
            ),
            $this->targetMetrics(
                'mega_prize',
                (int) ($targetSettings->get('mega_prize_target') ?? 20000),
                $summary['mega_prize_count'],
                $dailyTrend,
                $endDate->toDateString()
            ),
        ];

        $recentRecords = SpinRecord::query()
            ->with(['user:id,name,phone', 'spinWheelChanceDaily:id,type,points,date'])
            ->whereBetween('date', $dateBounds)
            ->latest('spun_at')
            ->limit(15)
            ->get()
            ->map(fn ($record) => [
                'id' => $record->id,
                'member' => $record->user ? [
                    'name' => $record->user->name,
                    'phone' => $record->user->phone,
                ] : null,
                'type' => $record->spinWheelChanceDaily?->type ?? 'unknown',
                'reward_points' => (int) $record->reward_points,
                'remaining_after_spin' => $record->at_max_times !== null
                    ? (int) $record->at_max_times
                    : null,
                'spun_at' => $record->spun_at
                    ? Carbon::parse($record->spun_at)->format('Y-m-d H:i')
                    : null,
            ]);

        return Inertia::render('SpinWheelAnalytics/Index', [
            'user' => Auth::guard('admin')->user(),
            'analytics' => [
                'filters' => $filters,
                'summary' => $summary,
                'trend' => $trend,
                'trend_granularity' => $isSingleDay ? 'hourly' : 'daily',
                'prize_distribution' => $prizeDistribution,
                'inventory' => $inventory,
                'targets' => $targets,
                'recent_records' => $recentRecords,
            ],
        ]);
    }

    private function hourlyTrend(string $date)
    {
        $hourExpression = match (DB::connection()->getDriverName()) {
            'mysql', 'mariadb' => 'HOUR(spin_records.spun_at)',
            'sqlite' => "CAST(strftime('%H', spin_records.spun_at) AS INTEGER)",
            default => 'EXTRACT(HOUR FROM spin_records.spun_at)',
        };

        $hourlyRows = SpinRecord::query()
            ->leftJoin(
                'spin_wheel_chances_daily',
                'spin_records.spin_wheel_chance_daily_id',
                '=',
                'spin_wheel_chances_daily.id'
            )
            ->whereDate('spin_records.date', $date)
            ->selectRaw("{$hourExpression} as spin_hour")
            ->selectRaw('COUNT(*) as spin_count')
            ->selectRaw('COUNT(DISTINCT spin_records.user_id) as unique_spinners')
            ->selectRaw('COALESCE(SUM(spin_records.reward_points), 0) as reward_points')
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'super_prize' THEN 1 ELSE 0 END) as super_prize_count")
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'mega_prize' THEN 1 ELSE 0 END) as mega_prize_count")
            ->groupBy(DB::raw($hourExpression))
            ->orderBy(DB::raw($hourExpression))
            ->get()
            ->keyBy(fn ($row) => (int) $row->spin_hour);

        return collect(range(0, 23))
            ->map(function ($hour) use ($hourlyRows) {
                $row = $hourlyRows->get($hour);

                return [
                    'key' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
                    'label' => Carbon::createFromTime($hour)->format('g A'),
                    'spin_count' => (int) ($row?->spin_count ?? 0),
                    'unique_spinners' => (int) ($row?->unique_spinners ?? 0),
                    'reward_points' => (int) ($row?->reward_points ?? 0),
                    'super_prize_count' => (int) ($row?->super_prize_count ?? 0),
                    'mega_prize_count' => (int) ($row?->mega_prize_count ?? 0),
                ];
            })
            ->values();
    }

    private function targetMetrics(
        string $type,
        int $target,
        int $awardedCount,
        $dailyTrend,
        string $endDate
    ): array {
        $target = max(0, $target);
        $endDateSpins = (int) ($dailyTrend->firstWhere('key', $endDate)['spin_count'] ?? 0);
        $cycleProgress = $target > 0 ? $endDateSpins % $target : 0;
        $thresholdCrossings = $target > 0
            ? $dailyTrend->sum(fn ($row) => intdiv($row['spin_count'], $target))
            : 0;

        return [
            'type' => $type,
            'target' => $target,
            'awarded_count' => $awardedCount,
            'threshold_crossings' => $thresholdCrossings,
            'cycle_progress' => $cycleProgress,
            'progress_percent' => $target > 0
                ? round(($cycleProgress / $target) * 100, 1)
                : 0,
            'spins_until_next' => $target > 0 ? $target - $cycleProgress : null,
            'progress_date' => $endDate,
        ];
    }
}
