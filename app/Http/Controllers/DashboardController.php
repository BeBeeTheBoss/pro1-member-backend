<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
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

        $dailyUsageRows = UserSession::query()
            ->selectRaw('DATE(session_start) as activity_date')
            ->selectRaw('COUNT(*) as session_count')
            ->selectRaw('SUM(duration_in_seconds) as total_seconds')
            ->whereDate('session_start', '>=', $startDate)
            ->groupBy(DB::raw('DATE(session_start)'))
            ->orderBy('activity_date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->activity_date)->format('Y-m-d'));

        $dailyUsage = collect(range(0, 13))->map(function ($day) use ($startDate, $dailyUsageRows) {
            $date = $startDate->copy()->addDays($day);
            $key = $date->format('Y-m-d');
            $row = $dailyUsageRows->get($key);

            return [
                'date' => $key,
                'label' => $date->format('M d'),
                'session_count' => (int) ($row?->session_count ?? 0),
                'total_seconds' => (int) ($row?->total_seconds ?? 0),
            ];
        })->values();

        $topMembers = User::query()
            ->select('id', 'name', 'phone', 'last_logged_in_time', 'total_usage_time_in_seconds')
            ->orderByDesc('total_usage_time_in_seconds')
            ->limit(8)
            ->get()
            ->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'last_logged_in_time' => optional($member->last_logged_in_time)->format('Y-m-d H:i'),
                'total_usage_time_in_seconds' => (int) $member->total_usage_time_in_seconds,
            ]);

        $recentMembers = User::query()
            ->select('id', 'name', 'phone', 'last_logged_in_time', 'total_usage_time_in_seconds')
            ->orderByDesc('last_logged_in_time')
            ->limit(10)
            ->get()
            ->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'last_logged_in_time' => optional($member->last_logged_in_time)->format('Y-m-d H:i'),
                'total_usage_time_in_seconds' => (int) $member->total_usage_time_in_seconds,
            ]);

        $dashboard = [
            'stats' => [
                'total_members' => User::count(),
                'active_sessions' => UserSession::whereNull('session_end')->count(),
                'today_sessions' => UserSession::whereDate('session_start', $today)->count(),
                'today_usage_seconds' => (int) UserSession::whereDate('session_start', $today)->sum('duration_in_seconds'),
                'total_usage_seconds' => (int) User::sum('total_usage_time_in_seconds'),
                'recent_active_members' => User::whereDate('last_logged_in_time', '>=', Carbon::today()->subDays(6))->count(),
            ],
            'daily_usage' => $dailyUsage,
            'top_members' => $topMembers,
            'recent_members' => $recentMembers,
        ];

        return Inertia::render('Dashboard/Index', [
            'user' => $admin,
            'dashboard' => $dashboard,
        ]);
    }
}
