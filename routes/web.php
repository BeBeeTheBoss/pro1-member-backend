<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PopupController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/',[AuthController::class,'checkLogin'])->name('checkLogin');
Route::get('/login',[AuthController::class,'loginPage'])->name('loginPage');
Route::post('/login',[AuthController::class,'login'])->name('login');

Route::middleware('admin.auth')->group(function () {
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');

    Route::get('/dashboard',[DashboardController::class,'dashboard'])->name('dashboard');

    Route::group(['prefix' => 'members', 'controller' => MemberController::class],function(){
        Route::get('/','index')->name('members');
        Route::delete('/{id}','destroy')->name('members.destroy');
    });

    Route::group(['prefix' => 'branches', 'controller' => BranchController::class],function(){
        Route::get('/','index')->name('branches');
        Route::get('/create','create')->name('branches.create');
        Route::post('/','store')->name('branches.store');
        Route::get('/edit/{id}','edit')->name('branches.edit');
        Route::post('/update','update')->name('branches.update');
        Route::delete('/{id}','destroy')->name('branches.destroy');
    });

    Route::group(['prefix' => 'faqs', 'controller' => FAQController::class],function(){
        Route::get('/','index')->name('faqs');
        Route::get('/create','create')->name('faqs.create');
        Route::post('/','store')->name('faqs.store');
        Route::get('/edit/{id}','edit')->name('faqs.edit');
        Route::post('/update','update')->name('faqs.update');
        Route::delete('/{id}','destroy')->name('faqs.destroy');
    });

    Route::group(['prefix' => 'notifications', 'controller' => NotificationController::class],function(){
        Route::get('/','index')->name('notifications');
        Route::get('/create','create')->name('notifications.create');
        Route::post('/','store')->name('notifications.store');
        Route::get('/edit/{id}','edit')->name('notifications.edit');
        Route::post('/update','update')->name('notifications.update');
        Route::delete('/{id}','destroy')->name('notifications.destroy');
    });

    Route::group(['prefix' => 'popups','controller' => PopupController::class],function(){
        Route::get('/','index')->name('popups');
        Route::get('/create','create')->name('popups.create');
        Route::post('/','store')->name('popups.store');
        Route::get('/edit/{id}','edit')->name('popups.edit');
        Route::post('/update','update')->name('popups.update');
        Route::delete('/{id}','destroy')->name('popups.destroy');
    });

});
