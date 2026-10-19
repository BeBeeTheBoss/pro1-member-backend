<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SpinRecord;
use App\Models\SpinWheelChance;
use App\Models\SpinWheelChanceDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpinWheelChanceController extends Controller
{
    public function __construct(protected SpinWheelChanceDaily $model) {}

    public function index(Request $request)
    {
        $chances = $this->model
            ->whereDate('date', now()->toDateString())
            ->orderBy('points')
            ->get();

        return sendResponse($chances, 200);
    }

    public function play(Request $request)
    {
        $user = User::find(Auth::user()->id);

        if (!$user) {
            return sendResponse(null, 401, 'Unauthorized');
        }

        info($user->keys);

        if((int) $user->keys < 1){
            return sendResponse(null, 400, 'Not enough keys to play');
        }

        info("Spin wheel play by user id: {$user->id}, keys left: {$user->keys}");

        $spin_count = SpinRecord::whereDate('date', now()->toDateString())->count() + 1;

        $super_prize = SpinWheelChanceDaily::whereDate('date', now()->toDateString())
            ->where('type', 'super_prize')
            ->where('max_times', '!=', 0)
            ->first();
        $super_prize_times = $super_prize ? $super_prize->max_times : 0;

        $mega_prize = SpinWheelChanceDaily::whereDate('date', now()->toDateString())
            ->where('type', 'mega_prize')
            ->where('max_times', '!=', 0)
            ->first();
        $mega_prize_times = $mega_prize ? $mega_prize->max_times : 0;

        $mega_prize_all_time = SpinWheelChance::where('type', 'mega_prize')->first();

        $target = Setting::where('attribute','super_prize_target')->first()->value ?? 200;
        $mega_prize_target = Setting::where('attribute','mega_prize_target')->first()->value ?? 20000;

        if($spin_count % $mega_prize_target === 0 && $mega_prize_times > 0) {

            $mega_prize->max_times -= 1;
            $mega_prize->save();
            $points = $mega_prize;
            $mega_prize_all_time->max_times -= 1;
            $mega_prize_all_time->save();

        } else if ($spin_count % $target === 0 && $super_prize_times > 0) {

            $super_prize->max_times -= 1;
            $super_prize->save();
            $points = $super_prize;
        } else {
            $points = SpinWheelChanceDaily::whereDate('date', now()->toDateString())
                ->whereIn('type', ['fix_prize', 'other'])
                ->where('max_times', '!=', 0)
                ->inRandomOrder()
                ->first();

            if (!$points) {
                $points = SpinWheelChanceDaily::whereDate('date', now()->toDateString())
                    ->where('type', 'fix_prize')
                    ->first();
            } else {
                $points->max_times -= 1;
                $points->save();
            }
        }

        SpinRecord::create([
            'user_id' => $user->id,
            'spin_wheel_chance_daily_id' => $points->id,
            'reward_points' => $points->points,
            'at_max_times' => $points->max_times,
            'date' => now(),
            'spun_at' => now(),
        ]);


        $user->keys -= 1;
        $user->used_keys += 1;
        $user->save();

        info($points->toArray());

        return sendResponse($points, 200);
    }
}
