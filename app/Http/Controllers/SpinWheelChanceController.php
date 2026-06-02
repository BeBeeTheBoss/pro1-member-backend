<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SpinWheelChance;
use Illuminate\Support\Facades\Auth;

class SpinWheelChanceController extends Controller
{
    public function __construct(protected SpinWheelChance $model) {}

    public function index()
    {
        $chances = $this->model->latest()->get();
        $user = Auth::guard('admin')->user();

        return Inertia::render('SpinWheelChances/Index', [
            'chances' => $chances,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('SpinWheelChances/Create', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
            'max_times' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:super_prize,fix_prize,other'],
        ]);

        $this->model->create($validated);

        return redirect()->route('spin-wheel-chances')->with('success', 'Spin wheel chance created successfully');
    }

    public function edit($id)
    {
        $chance = $this->model->findOrFail($id);
        $user = Auth::guard('admin')->user();

        return Inertia::render('SpinWheelChances/Edit', [
            'chance' => $chance,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'exists:spin_wheel_chances,id'],
            'points' => ['required', 'integer', 'min:1'],
            'max_times' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:super_prize,fix_prize,other'],
        ]);

        $chance = $this->model->findOrFail($validated['id']);
        $chance->update($validated);

        return redirect()->route('spin-wheel-chances')->with('success', 'Spin wheel chance updated successfully');
    }

    public function destroy($id)
    {
        $chance = $this->model->findOrFail($id);
        $chance->delete();

        return redirect()->route('spin-wheel-chances')->with('success', 'Spin wheel chance deleted successfully');
    }
}
