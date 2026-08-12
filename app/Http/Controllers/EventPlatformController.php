<?php

namespace App\Http\Controllers;

use App\Models\EventPlatform;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventPlatformController extends Controller
{
    public function __construct(protected EventPlatform $model) {}

    public function index()
    {
        $platforms = $this->model->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('EventPlatforms/Index', [
            'platforms' => $platforms,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('EventPlatforms/Create', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $this->model->create($request->all());

        return redirect()->route('event-platforms')->with('success', 'Event platform created successfully');
    }

    public function edit($id)
    {
        $platform = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        return Inertia::render('EventPlatforms/Edit', [
            'platform' => $platform,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $platform = $this->model->find($request->id);

        $platform->update($request->all());

        return redirect()->route('event-platforms')->with('success', 'Event platform updated successfully');
    }

    public function destroy($id)
    {
        $platform = $this->model->find($id);
        $platform->delete();

        return redirect()->route('event-platforms')->with('success', 'Event platform deleted successfully');
    }
}
