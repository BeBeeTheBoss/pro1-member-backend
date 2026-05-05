<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPlatform;
use App\Models\EventPlatformLink;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function __construct(protected Event $model) {}

    public function index()
    {
        $events = $this->model->with('platformLinks.platform')->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();
        $platforms = EventPlatform::orderBy('name')->get();

        return Inertia::render('Events/Create', [
            'user' => $user,
            'platforms' => $platforms,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'image' => ['required', 'image'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*.event_platform_id' => ['required', 'exists:event_platforms,id'],
            'platforms.*.link' => ['required', 'string'],
        ]);

        $event = $this->model->create($request->only(['name', 'description', 'start_date', 'end_date']));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('events', $image, $filename);
            $event->image = $filename;
            $event->save();
        }

        foreach ($request->platforms as $row) {
            EventPlatformLink::create([
                'event_id' => $event->id,
                'event_platform_id' => $row['event_platform_id'],
                'link' => $row['link'],
            ]);
        }

        return redirect()->route('events')->with('success', 'Event created successfully');
    }

    public function edit($id)
    {
        $event = $this->model->with('platformLinks')->find($id);
        $user = Auth::guard('admin')->user();
        $platforms = EventPlatform::orderBy('name')->get();

        return Inertia::render('Events/Edit', [
            'event' => $event,
            'user' => $user,
            'platforms' => $platforms,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'image' => ['nullable', 'image'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*.event_platform_id' => ['required', 'exists:event_platforms,id'],
            'platforms.*.link' => ['required', 'string'],
        ]);

        $event = $this->model->find($request->id);
        $event->update($request->only(['name', 'description', 'start_date', 'end_date']));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            if ($event->image) {
                Storage::disk('public')->delete('events/' . $event->image);
            }
            Storage::disk('public')->putFileAs('events', $image, $filename);
            $event->image = $filename;
            $event->save();
        }

        EventPlatformLink::where('event_id', $event->id)->delete();

        foreach ($request->platforms as $row) {
            EventPlatformLink::create([
                'event_id' => $event->id,
                'event_platform_id' => $row['event_platform_id'],
                'link' => $row['link'],
            ]);
        }

        return redirect()->route('events')->with('success', 'Event updated successfully');
    }

    public function destroy($id)
    {
        $event = $this->model->find($id);
        if ($event?->image) {
            Storage::disk('public')->delete('events/' . $event->image);
        }
        $event->delete();

        return redirect()->route('events')->with('success', 'Event deleted successfully');
    }
}
