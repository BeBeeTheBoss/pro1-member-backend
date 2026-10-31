<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ServerTimeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $now = now();

        return sendResponse([
            'current_time' => $now->toIso8601String(),
            'timestamp' => $now->timestamp,
            'timezone' => $now->timezoneName,
        ], 200, 'Server time retrieved successfully');
    }
}
