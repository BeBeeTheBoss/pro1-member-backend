<?php

namespace App\Http\Controllers;

use App\Models\Privilege;
use App\Models\PrivilegeCategory;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrivilegeController extends Controller
{
    public function __construct(protected Privilege $model) {}

    public function index()
    {
        $privileges = $this->model->with('category')->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('Privileges/Index', [
            'privileges' => $privileges,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();
        $categories = PrivilegeCategory::orderBy('name')->get();

        return Inertia::render('Privileges/Create', [
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:privilege_categories,id'],
            'image' => ['required', 'image'],
            'is_active' => ['required', 'boolean'],
        ]);

        $privilege = $this->model->create($request->except('image'));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('privileges', $image, $filename);
            $privilege->image = $filename;
            $privilege->save();
        }

        return redirect()->route('privileges')->with('success', 'Privilege created successfully');
    }

    public function edit($id)
    {
        $privilege = $this->model->with('category')->find($id);

        $user = Auth::guard('admin')->user();
        $categories = PrivilegeCategory::orderBy('name')->get();

        return Inertia::render('Privileges/Edit', [
            'privilege' => $privilege,
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:privileges,id'],
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:privilege_categories,id'],
            'image' => ['required', 'image'],
            'is_active' => ['required', 'boolean'],
        ]);

        $privilege = $this->model->find($request->id);

        $privilege->update($request->except('image'));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            if ($privilege->image) {
                Storage::disk('public')->delete('privileges/' . $privilege->image);
            }
            Storage::disk('public')->putFileAs('privileges', $image, $filename);
            $privilege->image = $filename;
            $privilege->save();
        }

        return redirect()->route('privileges')->with('success', 'Privilege updated successfully');
    }

    public function destroy($id)
    {
        $privilege = $this->model->find($id);
        if ($privilege?->image) {
            Storage::disk('public')->delete('privileges/' . $privilege->image);
        }
        $privilege->delete();

        return redirect()->route('privileges')->with('success', 'Privilege deleted successfully');
    }
}
