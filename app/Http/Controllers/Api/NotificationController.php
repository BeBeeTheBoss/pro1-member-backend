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
    public function __construct(protected Notification $model) {}

    public function index(Request $request)
    {
        $user = User::where('idcard', $request->idcard)->first();

        $notifications = UserNotification::where('user_id', $user->id)
            ->with('notification')
            ->orderByDesc(
                Notification::select('id')
                    ->whereColumn('notifications.id', 'user_notifications.notification_id')
            )
            ->get();


        foreach ($notifications as $notification) {
            $notification->notification->image = $notification->notification->image ? url("storage/notifications/" . $notification->notification->image) : null;
        }

        return sendResponse($notifications, 200);
    }


    public function show($id)
    {
        $notification = $this->model->find($id);

        if ($notification->recipient === 'specific') {
            $notification['user'] = User::where('id', UserNotification::where('notification_id', $notification->id)->first()->user_id)->select('id', 'name', 'idcard', 'phone')->first();
        }

        $notification->image = $notification->image ? url("storage/notifications/" . $notification->image) : null;

        return sendResponse($notification, 200);
    }

    public function read(Request $request)
    {
        $user_noti = UserNotification::find($request->id);

        if (!$user_noti) {
            return sendResponse(null, 404, "Notification not found");
        }

        $user_noti->is_read = true;
        $user_noti->save();

        return sendResponse(null, 200);
    }

    public function markAllAsRead(Request $request)
    {

        $user = User::where('idcard', $request->idcard)->first();

        $user_notifications = UserNotification::where('user_id', $user->id)->get();

        foreach ($user_notifications as $user_noti) {
            $user_noti->is_read = true;
            $user_noti->save();
        }

        return sendResponse(null, 200);
    }
}
