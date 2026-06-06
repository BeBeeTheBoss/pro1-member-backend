<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::where('user_id', Auth::id())
            ->with('branch:id,name,branch_code', 'images')
            ->latest()
            ->get();

        return sendResponse([
            'feedbacks' => $feedbacks,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'date' => ['required', 'date'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string'],
        ]);

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'branch_id' => $validated['branch_id'],
            'date' => $validated['date'],
            'rating' => $validated['rating'],
            'message' => $validated['message'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid('feedback_', true) . '.' . $image->extension();
                Storage::disk('public')->putFileAs('feedbacks', $image, $filename);

                $feedback->images()->create([
                    'image' => $filename,
                ]);
            }
        }

        $feedback->load('branch:id,name,branch_code', 'images');

        return sendResponse([
            'feedback' => $feedback,
        ], 200, 'Feedback submitted successfully');
    }
}
