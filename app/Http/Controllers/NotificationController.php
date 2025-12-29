<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('notifications', $image, $filename);
            $notification->image = $filename;
            $notification->save();
        }

        $users = $request->choice === 'all' ? User::get() : User::where('id', $request->user_id)->get();

        foreach ($users as $user) {

            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id
            ]);

            Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://exp.host/--/api/v2/push/send', [
                'to' => $user->expo_push_token,
                'sound' => 'default',
                'title' => $request->title,
                'body' => $request->message ?? '',
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

        $notification->image = $notification->image ? url("storage/notifications/" . $notification->image) : null;

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

        if ($notification->image && !$request->hasFile('image') && $request->image === null) {
            Storage::disk('public')->delete('notifications/' . $notification->image);
            $notification->image = null;
            $notification->save();
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();

            if ($notification->image) {
                Storage::disk('public')->delete('notifications/' . $notification->image);
            }

            Storage::disk('public')->putFileAs('notifications', $image, $filename);
            $notification->image = $filename;
            $notification->save();
        }

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

    public function destroy($id)
    {
        $notification = $this->model->find($id);

        UserNotification::where('notification_id', $notification->id)->delete();

        $notification->delete();
        return redirect()->route('notifications')->with('success', 'Notification deleted successfully');
    }
}
