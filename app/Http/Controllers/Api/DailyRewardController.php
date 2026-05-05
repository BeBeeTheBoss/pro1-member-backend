<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyRewardController extends Controller
{
    public function claim(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return sendResponse(null, 401, "Unauthorized");
        }

        $today = Carbon::today();

        $alreadyClaimed = DailyReward::where('user_id', $user->id)
            ->whereDate('claimed_date', $today)
            ->first();

        if ($alreadyClaimed) {
            return sendResponse(null, 405, "Daily reward already claimed");
        }

        $result = DB::transaction(function () use ($user, $today) {
            $lastReward = DailyReward::where('user_id', $user->id)
                ->orderByDesc('claimed_date')
                ->first();

            $newCurrentStreak = 1;
            if ($lastReward) {
                $lastDate = Carbon::parse($lastReward->claimed_date);
                if ($lastDate->isSameDay($today->copy()->subDay())) {
                    $newCurrentStreak = (int) $user->current_streak + 1;
                }
            }

            $user->current_streak = $newCurrentStreak;
            // $user->streak_challenge_count = (int) $user->streak_challenge_count + 1;
            $user->save();

            $dailyReward = DailyReward::create([
                'user_id' => $user->id,
                'claimed_date' => $today->toDateString(),
                'day_count' => $newCurrentStreak,
            ]);

            return [$dailyReward, $user];
        });

        [$dailyReward, $user] = $result;

        return sendResponse([
            'daily_reward' => $dailyReward,
            'current_streak' => $user->current_streak,
            'streak_challenge_count' => $user->streak_challenge_count,
        ], 200, "Daily reward claimed successfully");
    }
}
