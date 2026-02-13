<?php

namespace App\Http\Controllers;

use App\Http\Resources\PopupResource;
use Inertia\Inertia;
use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PopupController extends Controller
{
    public function __construct(protected Popup $model) {}

    public function index()
    {
        $popups = $this->model->latest()->get();


        $user = Auth::guard('admin')->user();

        return Inertia::render('Popups/Index', [
            'popups' => PopupResource::collection($popups),
            'user' => $user
        ]);
    }

    public function create()
    {

        $user = Auth::guard('admin')->user();

        return Inertia::render('Popups/Create', [
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {

        $image = $request->file('image');
        $filename = time() . '.' . $request->image->extension();
        Storage::disk('public')->putFileAs('popups', $image, $filename);

        $this->model->create([
            'image' => $filename,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active
        ]);

        return redirect()->route('popups');
    }

    public function edit($id)
    {
        $popup = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        return Inertia::render('Popups/Edit', [
            'popup' => new PopupResource($popup),
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {

        $popup = $this->model->find($request->id);
        $popup->start_date = $request->start_date;
        $popup->end_date = $request->end_date;
        $popup->is_active = $request->is_active;

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete('popups/'.$popup->image);
            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('popups', $image, $filename);
            $popup->image = $filename;
        }

        $popup->save();

        return redirect()->route('popups');
    }

    public function destroy($id)
    {
        $popup = $this->model->find($id);
        Storage::disk('public')->delete('popups/'.$popup->image);
        $popup->delete();
        return redirect()->route('popups');
    }

}
