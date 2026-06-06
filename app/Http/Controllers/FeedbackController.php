<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function __construct(protected Feedback $model) {}

    public function index()
    {
        $feedbacks = $this->model
            ->with('user:id,name,phone,idcard', 'branch:id,name,branch_code', 'images')
            ->latest()
            ->get();

        return Inertia::render('Feedbacks/Index', [
            'feedbacks' => $feedbacks,
            'user' => Auth::guard('admin')->user(),
        ]);
    }

    public function destroy($id)
    {
        $feedback = $this->model->with('images')->findOrFail($id);

        foreach ($feedback->images as $image) {
            Storage::disk('public')->delete('feedbacks/' . $image->image);
        }

        $feedback->delete();

        return redirect()->route('feedbacks')->with('success', 'Feedback deleted successfully');
    }
}
