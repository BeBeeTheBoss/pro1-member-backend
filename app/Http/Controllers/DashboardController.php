<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard(){

        $admin = Auth::guard('admin')->user();

        return Inertia::render('Dashboard/Index', [
            'user' => $admin
        ]);
    }
}
