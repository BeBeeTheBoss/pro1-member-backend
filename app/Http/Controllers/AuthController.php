<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{

    public function __construct(protected Admin $model) {}

    public function checkLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function loginPage()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {

        info($request->all());

        $request->validate([
            'empCode' => 'required',
            'password' => 'required'
        ]);

        $admin = $this->model->where('emp_code', $request->empCode)->first();

        if (!$admin) {
            return back()->withErrors([
                'message' => 'Employee not found'
            ]);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return back()->withErrors([
                'message' => 'Wrong password'
            ]);
        }

        info($admin->toArray());

        Auth::guard('admin')->loginUsingId($admin->id);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('login');
    }
}
