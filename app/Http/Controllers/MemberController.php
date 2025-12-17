<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function __construct(protected User  $model){}

    public function index(Request $request){
        $members = $this->model->latest()->get();

        foreach($members as $member){
            $member->image = $member->image != null ? url("storage/profile_images/" . $member->image) : null;
        }

        $user = Auth::guard('admin')->user();
        return Inertia::render('Members/Index', [
            'members' => $members,
            'user' => $user
        ]);
    }

    public function destroy($id){
        info($id);
        return redirect()->route('members')->with('success', 'Member deleted successfully');
    }

}
