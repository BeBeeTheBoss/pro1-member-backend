<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Setting;
use App\Models\SpinRecord;
use App\Models\SpinWheelChance;
use App\Models\SpinWheelChanceDaily;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(){

        $admin = Auth::guard('admin')->user();
        $today = Carbon::today();
        $startDate = Carbon::today()->subDays(13);

        $dailySpinRows = SpinRecord::query()
            ->leftJoin('spin_wheel_chances_daily', 'spin_records.spin_wheel_chance_daily_id', '=', 'spin_wheel_chances_daily.id')
            ->selectRaw('spin_records.date as spin_date')
            ->selectRaw('COUNT(*) as spin_count')
            ->selectRaw('SUM(spin_records.reward_points) as reward_points')
            ->selectRaw("SUM(CASE WHEN spin_wheel_chances_daily.type = 'super_prize' THEN 1 ELSE 0 END) as super_prize_count")
            ->whereDate('spin_records.date', '>=', $startDate)
            ->groupBy('spin_records.date')
            ->orderBy('spin_date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->spin_date)->format('Y-m-d'));

        $dailySpins = collect(range(0, 13))->map(function ($day) use ($startDate, $dailySpinRows) {
            $date = $startDate->copy()->addDays($day);
            $key = $date->format('Y-m-d');
            $row = $dailySpinRows->get($key);

            return [
                'date' => $key,
                'label' => $date->format('M d'),
                'spin_count' => (int) ($row?->spin_count ?? 0),
                'reward_points' => (int) ($row?->reward_points ?? 0),
                'super_prize_count' => (int) ($row?->super_prize_count ?? 0),
            ];
        })->values();

        $configuredChances = SpinWheelChance::query()
            ->select('type', 'points')
            ->selectRaw('SUM(max_times) as configured_times')
            ->groupBy('type', 'points')
            ->get()
            ->keyBy(fn ($chance) => "{$chance->type}:{$chance->points}");

        $todayDailyChances = SpinWheelChanceDaily::query()
            ->select('type', 'points')
            ->selectRaw('SUM(max_times) as remaining_times')
            ->whereDate('date', $today)
            ->groupBy('type', 'points')
            ->get()
            ->keyBy(fn ($chance) => "{$chance->type}:{$chance->points}");

        $todayAwarded = SpinRecord::query()
            ->leftJoin('spin_wheel_chances_daily', 'spin_records.spin_wheel_chance_daily_id', '=', 'spin_wheel_chances_daily.id')
            ->selectRaw("COALESCE(spin_wheel_chances_daily.type, 'unknown') as type")
            ->selectRaw('spin_records.reward_points as points')
            ->selectRaw('COUNT(*) as awarded_times')
            ->whereDate('spin_records.date', $today)
            ->groupBy(DB::raw("COALESCE(spin_wheel_chances_daily.type, 'unknown')"), 'spin_records.reward_points')
            ->get()
            ->keyBy(fn ($chance) => "{$chance->type}:{$chance->points}");

        $chanceKeys = $configuredChances->keys()
            ->merge($todayDailyChances->keys())
            ->merge($todayAwarded->keys())
            ->unique()
            ->values();

        $todayChances = $chanceKeys->map(function ($key) use ($configuredChances, $todayDailyChances, $todayAwarded) {
            [$type, $points] = explode(':', $key);
            $configured = (int) ($configuredChances->get($key)?->configured_times ?? 0);
            $remaining = (int) ($todayDailyChances->get($key)?->remaining_times ?? 0);
            $awarded = (int) ($todayAwarded->get($key)?->awarded_times ?? 0);

            return [
                'key' => $key,
                'type' => $type,
                'points' => (int) $points,
                'configured_times' => $configured,
                'remaining_times' => $remaining,
                'awarded_times' => $awarded,
                'used_times' => max($awarded, $configured - $remaining),
            ];
        })
            ->sortBy([
                ['type', 'asc'],
                ['points', 'asc'],
            ])
            ->values();

        $recentSpinRecords = SpinRecord::query()
            ->with(['user:id,name,phone', 'spinWheelChanceDaily:id,type,points,date'])
            ->latest('spun_at')
            ->limit(12)
            ->get()
            ->map(fn ($record) => [
                'id' => $record->id,
                'member' => $record->user ? [
                    'name' => $record->user->name,
                    'phone' => $record->user->phone,
                ] : null,
                'type' => $record->spinWheelChanceDaily?->type ?? 'unknown',
                'reward_points' => (int) $record->reward_points,
                'remaining_after_spin' => $record->at_max_times !== null ? (int) $record->at_max_times : null,
                'spun_at' => $record->spun_at ? Carbon::parse($record->spun_at)->format('Y-m-d H:i') : null,
            ]);

        $superPrizeTarget = (int) (Setting::where('attribute', 'super_prize_target')->value('value') ?? 200);
        $todaySpinCount = SpinRecord::whereDate('date', $today)->count();
        $targetProgress = $superPrizeTarget > 0 ? $todaySpinCount % $superPrizeTarget : 0;

        $recentFeedbacks = Feedback::query()
            ->with('user:id,name,phone', 'branch:id,name,branch_code', 'images')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn ($feedback) => [
                'id' => $feedback->id,
                'branch' => $feedback->branch ? [
                    'name' => $feedback->branch->name,
                    'branch_code' => $feedback->branch->branch_code,
                ] : null,
                'date' => optional($feedback->date)->format('Y-m-d'),
                'rating' => $feedback->rating,
                'message' => $feedback->message,
                'created_at' => optional($feedback->created_at)->format('Y-m-d H:i'),
                'images_count' => $feedback->images->count(),
                'user' => $feedback->user ? [
                    'name' => $feedback->user->name,
                    'phone' => $feedback->user->phone,
                ] : null,
            ]);

        $dashboard = [
            'stats' => [
                'total_members' => User::count(),
                'today_spins' => $todaySpinCount,
                'total_spins' => SpinRecord::count(),
                'today_reward_points' => (int) SpinRecord::whereDate('date', $today)->sum('reward_points'),
                'today_remaining_chances' => (int) SpinWheelChanceDaily::whereDate('date', $today)->sum('max_times'),
                'today_super_prize_remaining' => (int) SpinWheelChanceDaily::whereDate('date', $today)->where('type', 'super_prize')->sum('max_times'),
                'super_prize_target' => $superPrizeTarget,
                'super_prize_progress' => $targetProgress,
                'spins_until_super_prize' => $superPrizeTarget > 0 ? $superPrizeTarget - $targetProgress : 0,
                'total_feedbacks' => Feedback::count(),
                'average_feedback_rating' => round((float) Feedback::whereNotNull('rating')->avg('rating'), 1),
            ],
            'daily_spins' => $dailySpins,
            'today_chances' => $todayChances,
            'recent_spin_records' => $recentSpinRecords,
            'recent_feedbacks' => $recentFeedbacks,
        ];

        return Inertia::render('Dashboard/Index', [
            'user' => $admin,
            'dashboard' => $dashboard,
        ]);
    }
}
