<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct(protected Setting $model) {}

    public function index()
    {
        $settings = $this->model->orderBy('attribute')->get();
        $user = Auth::guard('admin')->user();

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('Settings/Create', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'attribute' => ['required', 'string', 'max:255', 'unique:settings,attribute'],
            'value' => ['nullable', 'string'],
        ]);

        $this->model->create($validated);

        return redirect()->route('settings')->with('success', 'Setting created successfully');
    }

    public function edit($attribute)
    {
        $setting = $this->model->findOrFail($attribute);
        $user = Auth::guard('admin')->user();

        return Inertia::render('Settings/Edit', [
            'setting' => $setting,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'original_attribute' => ['required', 'exists:settings,attribute'],
            'attribute' => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings', 'attribute')->ignore($request->original_attribute, 'attribute'),
            ],
            'value' => ['nullable', 'string'],
        ]);

        $setting = $this->model->findOrFail($validated['original_attribute']);
        $setting->update([
            'attribute' => $validated['attribute'],
            'value' => $validated['value'] ?? null,
        ]);

        return redirect()->route('settings')->with('success', 'Setting updated successfully');
    }

    public function destroy($attribute)
    {
        $setting = $this->model->findOrFail($attribute);
        $setting->delete();

        return redirect()->route('settings')->with('success', 'Setting deleted successfully');
    }
}
