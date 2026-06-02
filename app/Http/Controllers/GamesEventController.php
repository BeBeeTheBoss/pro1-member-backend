<?php

namespace App\Http\Controllers;

use App\Models\GamesEvent;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GamesEventController extends Controller
{
    public function __construct(protected GamesEvent $model) {}

    public function index()
    {
        $gamesEvents = $this->model->with('branches')->latest()->get();
        $user = Auth::guard('admin')->user();

        return Inertia::render('GamesEvents/Index', [
            'gamesEvents' => $gamesEvents,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('GamesEvents/Create', [
            'user' => $user,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'minimum_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'is_active' => ['required', 'boolean'],
            'all_branches' => ['required', 'boolean'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
            'image' => ['required', 'file', 'extensions:jpg,jpeg,png,webp'],
        ]);

        if (! $request->boolean('all_branches') && empty($validated['branch_ids'])) {
            return back()->withErrors(['branch_ids' => 'Please choose at least one branch.'])->withInput();
        }

        $gameEvent = $this->model->create(collect($validated)->except(['image', 'branch_ids'])->toArray());
        $gameEvent->branches()->sync($request->boolean('all_branches') ? [] : $validated['branch_ids']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('games-events', $image, $filename);
            $gameEvent->image = $filename;
            $gameEvent->save();
        }

        return redirect()->route('games-events')->with('success', 'Game event created successfully');
    }

    public function edit($id)
    {
        $gameEvent = $this->model->with('branches')->findOrFail($id);
        $user = Auth::guard('admin')->user();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('GamesEvents/Edit', [
            'gameEvent' => $gameEvent,
            'user' => $user,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'exists:games_event,id'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'minimum_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'is_active' => ['required', 'boolean'],
            'all_branches' => ['required', 'boolean'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => ['file', 'extensions:jpg,jpeg,png,webp'],
            ]);
        }

        if (! $request->boolean('all_branches') && empty($validated['branch_ids'])) {
            return back()->withErrors(['branch_ids' => 'Please choose at least one branch.'])->withInput();
        }

        $gameEvent = $this->model->findOrFail($validated['id']);
        $gameEvent->update(collect($validated)->except(['id', 'image', 'branch_ids'])->toArray());
        $gameEvent->branches()->sync($request->boolean('all_branches') ? [] : $validated['branch_ids']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            if ($gameEvent->image) {
                Storage::disk('public')->delete('games-events/' . $gameEvent->image);
            }
            Storage::disk('public')->putFileAs('games-events', $image, $filename);
            $gameEvent->image = $filename;
            $gameEvent->save();
        }

        return redirect()->route('games-events')->with('success', 'Game event updated successfully');
    }

    public function destroy($id)
    {
        $gameEvent = $this->model->findOrFail($id);

        if ($gameEvent->image) {
            Storage::disk('public')->delete('games-events/' . $gameEvent->image);
        }

        $gameEvent->delete();

        return redirect()->route('games-events')->with('success', 'Game event deleted successfully');
    }
}
