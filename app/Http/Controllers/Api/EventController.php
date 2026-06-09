<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function __construct(protected Event $model) {}

    public function index()
    {
        $now = now()->toDateTimeString();

        $events = $this->model
            ->with('platformLinks.platform')
            ->whereRaw("CONCAT(start_date, ' ', start_time) <= ?", [$now])
            ->whereRaw("CONCAT(end_date, ' ', end_time) >= ?", [$now])
            ->latest()
            ->get();

        return sendResponse(EventResource::collection($events), 200);
    }
}
