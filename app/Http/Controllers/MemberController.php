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
        $search = trim((string) $request->query('search', ''));

        $members = $this->model
            ->query()
            ->select('id', 'name', 'idcard', 'phone', 'gender', 'birth_date', 'image', 'created_at')
            ->when($search !== '', function ($query) use ($search) {
                $normalizedSearch = strtolower(str_replace(' ', '', $search));

                $query->where(function ($q) use ($search, $normalizedSearch) {
                    $q->whereRaw("LOWER(REPLACE(name, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhereRaw("LOWER(REPLACE(idcard, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhereRaw("LOWER(REPLACE(phone, ' ', '')) LIKE ?", ["%{$normalizedSearch}%"])
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('idcard', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(function ($member) {
                $member->image = $member->image ? url("storage/profile_images/" . $member->image) : null;

                return $member;
            });

        $user = Auth::guard('admin')->user();
        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
            ],
            'user' => $user
        ]);
    }

    public function destroy($id){
        info($id);
        return redirect()->route('members')->with('success', 'Member deleted successfully');
    }

}
