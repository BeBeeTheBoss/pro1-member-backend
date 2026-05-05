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
        $events = $this->model->with('platformLinks.platform')->latest()->get();

        return sendResponse(EventResource::collection($events), 200);
    }
}
