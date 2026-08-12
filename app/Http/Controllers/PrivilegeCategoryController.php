<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeCategory;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivilegeCategoryController extends Controller
{
    public function __construct(protected PrivilegeCategory $model) {}

    public function index()
    {
        $categories = $this->model->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('PrivilegeCategories/Index', [
            'categories' => $categories,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('PrivilegeCategories/Create', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $this->model->create($request->all());

        return redirect()->route('privilege-categories')->with('success', 'Privilege category created successfully');
    }

    public function edit($id)
    {
        $category = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        return Inertia::render('PrivilegeCategories/Edit', [
            'category' => $category,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $category = $this->model->find($request->id);

        $category->update($request->all());

        return redirect()->route('privilege-categories')->with('success', 'Privilege category updated successfully');
    }

    public function destroy($id)
    {
        $category = $this->model->find($id);
        $category->delete();

        return redirect()->route('privilege-categories')->with('success', 'Privilege category deleted successfully');
    }
}
