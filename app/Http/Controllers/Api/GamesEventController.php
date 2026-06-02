<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GamesEventResource;
use App\Models\GamesEvent;
use Illuminate\Http\Request;

class GamesEventController extends Controller
{
    public function __construct(protected GamesEvent $model) {}

    public function index(Request $request)
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        $user = $request->user('sanctum');
        $branchCode = $user?->branch_code
            ?: $request->input('branch_code')
            ?: $request->input('branchCode');
        $branchId = $branchCode ? null : $request->input('branch_id');

        $gamesEvents = $this->model
            ->with('branches')
            ->where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            })
            ->where(function ($query) use ($currentTime) {
                $query
                    ->where(function ($query) {
                        $query->whereNull('start_time')
                            ->whereNull('end_time');
                    })
                    ->orWhere(function ($query) use ($currentTime) {
                        $query->whereNotNull('start_time')
                            ->whereNull('end_time')
                            ->whereTime('start_time', '<=', $currentTime);
                    })
                    ->orWhere(function ($query) use ($currentTime) {
                        $query->whereNull('start_time')
                            ->whereNotNull('end_time')
                            ->whereTime('end_time', '>=', $currentTime);
                    })
                    ->orWhere(function ($query) use ($currentTime) {
                        $query->whereNotNull('start_time')
                            ->whereNotNull('end_time')
                            ->whereColumn('start_time', '<=', 'end_time')
                            ->whereTime('start_time', '<=', $currentTime)
                            ->whereTime('end_time', '>=', $currentTime);
                    })
                    ->orWhere(function ($query) use ($currentTime) {
                        $query->whereNotNull('start_time')
                            ->whereNotNull('end_time')
                            ->whereColumn('start_time', '>', 'end_time')
                            ->where(function ($query) use ($currentTime) {
                                $query->whereTime('start_time', '<=', $currentTime)
                                    ->orWhereTime('end_time', '>=', $currentTime);
                            });
                    });
            })
            ->when($branchCode || $branchId, function ($query) use ($branchCode, $branchId) {
                $query->where(function ($query) use ($branchCode, $branchId) {
                    $query->where('all_branches', true)
                        ->orWhereHas('branches', function ($query) use ($branchCode, $branchId) {
                            $query
                                ->when($branchId, function ($query) use ($branchId) {
                                    $query->where('branches.id', $branchId);
                                })
                                ->when($branchCode, function ($query) use ($branchCode) {
                                    $query->where('branches.branch_code', $branchCode);
                                });
                        });
                });
            })
            ->latest()
            ->get();

        return sendResponse(GamesEventResource::collection($gamesEvents), 200);
    }
}
