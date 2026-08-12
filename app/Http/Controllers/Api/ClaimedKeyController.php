<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClaimedKey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimedKeyController extends Controller
{

    public function get(Request $request)
    {
        $user = User::find(Auth::user()->id);

        if (!$user) {
            return sendResponse(null, 401, "Unauthorized");
        }

        $claimedKeys = ClaimedKey::where('user_id', $user->id)->get();

        return sendResponse([
            'claimed_keys' => $claimedKeys,
        ], 200);
    }

    public function store(Request $request)
    {
        $user = User::find(Auth::user()->id);

        if (!$user) {
            return sendResponse(null, 401, "Unauthorized");
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'keys' => ['required'],
            'claimed_at' => ['nullable', 'date'],
        ]);

        $claimedKey = ClaimedKey::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'keys' => $validated['keys'],
            'claimed_at' => $validated['claimed_at'] ?? now(),
        ]);

        $user->keys += $validated['keys'];
        $user->save();

        return sendResponse([
            'claimed_key' => $claimedKey,
        ], 200, "Key claimed successfully");
    }
}
