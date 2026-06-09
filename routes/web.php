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
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\PrivilegeCategoryController;
use App\Http\Controllers\EventPlatformController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GamesEventController;
use App\Http\Controllers\SpinWheelChanceController;

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
        Route::get('/{id}/recipient-file','downloadRecipientFile')->name('notifications.recipient-file');
        Route::get('/edit/{id}','edit')->name('notifications.edit');
        Route::post('/update','update')->name('notifications.update');
        Route::delete('/{id}','destroy')->name('notifications.destroy');
    });

    Route::group(['prefix' => 'feedbacks', 'controller' => FeedbackController::class],function(){
        Route::get('/','index')->name('feedbacks');
        Route::delete('/{id}','destroy')->name('feedbacks.destroy');
    });

    Route::group(['prefix' => 'popups','controller' => PopupController::class],function(){
        Route::get('/','index')->name('popups');
        Route::get('/create','create')->name('popups.create');
        Route::post('/','store')->name('popups.store');
        Route::get('/edit/{id}','edit')->name('popups.edit');
        Route::post('/update','update')->name('popups.update');
        Route::delete('/{id}','destroy')->name('popups.destroy');
    });

    Route::group(['prefix' => 'privileges','controller' => PrivilegeController::class],function(){
        Route::get('/','index')->name('privileges');
        Route::get('/create','create')->name('privileges.create');
        Route::post('/','store')->name('privileges.store');
        Route::get('/edit/{id}','edit')->name('privileges.edit');
        Route::post('/update','update')->name('privileges.update');
        Route::delete('/{id}','destroy')->name('privileges.destroy');
    });

    Route::group(['prefix' => 'privilege-categories','controller' => PrivilegeCategoryController::class],function(){
        Route::get('/','index')->name('privilege-categories');
        Route::get('/create','create')->name('privilege-categories.create');
        Route::post('/','store')->name('privilege-categories.store');
        Route::get('/edit/{id}','edit')->name('privilege-categories.edit');
        Route::post('/update','update')->name('privilege-categories.update');
        Route::delete('/{id}','destroy')->name('privilege-categories.destroy');
    });

    Route::group(['prefix' => 'event-platforms','controller' => EventPlatformController::class],function(){
        Route::get('/','index')->name('event-platforms');
        Route::get('/create','create')->name('event-platforms.create');
        Route::post('/','store')->name('event-platforms.store');
        Route::get('/edit/{id}','edit')->name('event-platforms.edit');
        Route::post('/update','update')->name('event-platforms.update');
        Route::delete('/{id}','destroy')->name('event-platforms.destroy');
    });

    Route::group(['prefix' => 'events','controller' => EventController::class],function(){
        Route::get('/','index')->name('events');
        Route::get('/create','create')->name('events.create');
        Route::post('/','store')->name('events.store');
        Route::get('/edit/{id}','edit')->name('events.edit');
        Route::post('/update','update')->name('events.update');
        Route::delete('/{id}','destroy')->name('events.destroy');
    });

    Route::group(['prefix' => 'games-events', 'controller' => GamesEventController::class], function () {
        Route::get('/', 'index')->name('games-events');
        Route::get('/create', 'create')->name('games-events.create');
        Route::post('/', 'store')->name('games-events.store');
        Route::get('/edit/{id}', 'edit')->name('games-events.edit');
        Route::post('/update', 'update')->name('games-events.update');
        Route::delete('/{id}', 'destroy')->name('games-events.destroy');
    });

    Route::group(['prefix' => 'spin-wheel-chances', 'controller' => SpinWheelChanceController::class], function () {
        Route::get('/', 'index')->name('spin-wheel-chances');
        Route::get('/create', 'create')->name('spin-wheel-chances.create');
        Route::post('/', 'store')->name('spin-wheel-chances.store');
        Route::get('/edit/{id}', 'edit')->name('spin-wheel-chances.edit');
        Route::post('/update', 'update')->name('spin-wheel-chances.update');
        Route::delete('/{id}', 'destroy')->name('spin-wheel-chances.destroy');
    });

});
