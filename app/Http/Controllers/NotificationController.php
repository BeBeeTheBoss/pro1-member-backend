<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function __construct(protected Notification $model) {}

    public function index()
    {

        $notifications = $this->model->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('Notifications/Index', [
            'notifications' => NotificationResource::collection($notifications),
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('Notifications/Create', [
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {

        $notification = $this->model->create([
            'title' => $request->title,
            'message' => $request->message,
            'recipient' => $request->choice
        ]);

        $userIds_for_notification = $request->choice === 'all' ? User::pluck('id') : User::where('id', $request->user_id)->pluck('id');

        foreach ($userIds_for_notification as $user_id) {

            UserNotification::create([
                'user_id' => $user_id,
                'notification_id' => $notification->id
            ]);
        }

        return redirect()->route('notifications')->with('success', 'Notification created successfully');
    }

    public function edit($id)
    {

        $notification = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        if ($notification->recipient === 'specific') {
            $notification['user'] = User::where('id', UserNotification::where('notification_id', $notification->id)->first()->user_id)->select('id', 'name', 'idcard', 'phone')->first();
        }

        return Inertia::render('Notifications/Edit', [
            'notification' => $notification,
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {

        $notification = $this->model->find($request->id);

        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
            'recipient' => $request->choice
        ]);

        UserNotification::where('notification_id', $notification->id)->delete();

        $userIds_for_notification = $request->choice === 'all' ? User::pluck('id') : User::where('id', $request->user_id)->pluck('id');

        foreach ($userIds_for_notification as $user_id) {

            UserNotification::create([
                'user_id' => $user_id,
                'notification_id' => $notification->id
            ]);
        }

        return redirect()->route('notifications')->with('success', 'Notification updated successfully');
    }

    public function destroy($id){
        $notification = $this->model->find($id);

        UserNotification::where('notification_id', $notification->id)->delete();

        $notification->delete();
        return redirect()->route('notifications')->with('success', 'Notification deleted successfully');
    }

}
