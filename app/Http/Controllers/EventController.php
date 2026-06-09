<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPlatform;
use App\Models\EventPlatformLink;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
        $validated = $this->validateEventRequest($request, true);

        $event = $this->model->create(collect($validated)->only(['name', 'description', 'start_date', 'start_time', 'end_date', 'end_time'])->all());

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('events', $image, $filename);
            $event->image = $filename;
            $event->save();
        }

        foreach ($validated['platforms'] as $row) {
            EventPlatformLink::create([
                'event_id' => $event->id,
                'event_platform_id' => $row['event_platform_id'],
                'link' => $row['link'],
            ]);
        }

        return redirect()->route('events')->with('success', 'Event created successfully');
    }

    private function validateEventRequest(Request $request, bool $imageRequired): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date' => ['required', 'date'],
            'end_time' => ['required', 'date_format:H:i'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*.event_platform_id' => ['required', 'exists:event_platforms,id'],
            'platforms.*.link' => ['required', 'string'],
        ]);

        $startsAt = Carbon::parse($validated['start_date'] . ' ' . $validated['start_time']);
        $endsAt = Carbon::parse($validated['end_date'] . ' ' . $validated['end_time']);

        if ($endsAt->lt($startsAt)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date and time must be after or equal to start date and time.',
            ]);
        }

        return $validated;
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
        $validated = $this->validateEventRequest($request, false);

        $request->validate([
            'id' => ['required', 'exists:events,id'],
        ]);

        $event = $this->model->find($request->id);
        $event->update(collect($validated)->only(['name', 'description', 'start_date', 'start_time', 'end_date', 'end_time'])->all());

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

        foreach ($validated['platforms'] as $row) {
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
