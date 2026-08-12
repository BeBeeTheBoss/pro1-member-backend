<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BranchResource;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    public function __construct(protected Branch $model) {}

    public function index()
    {

        $branches = $this->model->latest()->get();

        $user = Auth::guard('admin')->user();

        $branches = BranchResource::collection($branches);

        return Inertia::render('Branches/Index', [
            'branches' => $branches,
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = Auth::guard('admin')->user();

        return Inertia::render('Branches/Create', [
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_code' => ['required', 'string', 'max:255', 'unique:branches,branch_code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact' => ['required', 'string', 'max:255'],
            'opening_time' => ['required'],
            'closing_time' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'region' => ['required', 'string', 'max:255'],
            'township' => ['required', 'string', 'max:255'],
            'image' => ['required', 'file', 'extensions:jpg,jpeg,png,webp'],
        ]);

        $branch = $this->model->create($request->except('image'));

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('branches', $image, $filename);
            $branch->image = $filename;
            $branch->save();
        }

        return redirect()->route('branches')->with('success', 'Branch created successfully');
    }

    public function edit($id){
        $branch = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        return Inertia::render('Branches/Edit', [
            'branch' => new BranchResource($branch),
            'user' => $user
        ]);
    }

    public function update(Request $request){
        $request->validate([
            'id' => ['required', 'exists:branches,id'],
            'branch_code' => ['required', 'string', 'max:255', 'unique:branches,branch_code,' . $request->id],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact' => ['required', 'string', 'max:255'],
            'opening_time' => ['required'],
            'closing_time' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'region' => ['required', 'string', 'max:255'],
            'township' => ['required', 'string', 'max:255'],
            'image' => ['nullable'],
        ]);

        $branch = $this->model->find($request->id);

        $branch->update($request->except('image'));

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->delete('branches/'.$branch->image);
            Storage::disk('public')->putFileAs('branches', $image, $filename);
            $branch->image = $filename;
            $branch->save();
        }

        return redirect()->route('branches')->with('success', 'Branch updated successfully');

    }

    public function destroy($id){

        // $branch = $this->model->find($id);

        // Storage::disk('public')->delete('branches/'.$branch->image);

        // $branch->delete();

        return redirect()->route('branches')->with('success', 'Branch deleted successfully');
    }
}
