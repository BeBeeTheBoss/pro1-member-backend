<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function startSession(Request $request)
    {
        $session = UserSession::create([
            'user_id' => Auth::user()->id,
            'session_start' => now()
        ]);

        return response()->json($session);
    }

    public function endSession(Request $request)
    {
        $session = UserSession::find($request->session_id);

        $session->session_end = now();
        $session->duration_in_seconds = (int) Carbon::parse($session->session_start)->diffInSeconds(now());
        $session->save();

        $user = User::find(Auth::user()->id);
        $user->last_logged_in_time = now();
        $user->total_usage_time_in_seconds += $session->duration_in_seconds;
        $user->save();

        return response()->json(['success' => true]);
    }
}
