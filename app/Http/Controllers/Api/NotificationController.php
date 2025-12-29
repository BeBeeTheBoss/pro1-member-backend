<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;

class NotificationController extends Controller
{
    public function __construct(protected Notification $model){}

    public function index(Request $request){
        $user = User::where('idcard', $request->idcard)->first();

        $notifications =UserNotification::where('user_id', $user->id)->with('notification')->get();

        foreach($notifications as $notification){
            $notification->notification->image = $notification->notification->image ? url("storage/notifications/".$notification->notification->image) : null;
        }

        return sendResponse($notifications, 200);
    }

}
