<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FAQController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PointTransferController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register'])->name('register');
Route::post('/resend-otp',[UserController::class,'resendOtp']);
Route::post('/verify-otp',[UserController::class,'verifyOtp']);

Route::group(['prefix' => 'users', 'controller' => UserController::class], function () {
    Route::get('/get', 'getUser')->name('getUser');
    Route::get('/search', 'searchUser')->name('searchUser');
    Route::post('/update', 'updateUser')->name('updateUser');
    Route::post('/find-member','findMember')->name('findMember');
    Route::post('/change-password', 'changePassword')->name('changePassword');
    Route::post('/forgot-password', 'forgotPassword')->name('forgotPassword');
    Route::post('/set-push-token', 'setPushToken')->name('setPushToken');
    Route::post('/logout', 'logout')->name('logout');
});

Route::post('/get-points', [UserController::class, 'getPoints'])->name('points');

Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions');
Route::get('/promotions/{id}', [PromotionController::class, 'show'])->name('promotion');

Route::group(['prefix' => 'histories', 'controller' => HistoryController::class], function () {
    Route::get('/{idcard}', 'index');
    Route::get('/{idcard}/{month}', 'show');
});

Route::group(['prefix' => 'coupons', 'controller' => UserController::class], function () {
    Route::get('/selected/{idcard}', 'getMyCoupon');
    Route::post('/use', 'useCoupon');
    Route::get('/histories/{idcard}/{limit}', 'getHistories');
    Route::get('/{idcard}', 'getAvailableCupons');
    Route::post('/select', 'selectCoupon');
    Route::get('/details/{id}', 'getCouponDetails');
});

Route::group(['prefix' => 'points', 'controller' => PointTransferController::class], function () {
    Route::post('/transfer', 'transfer');
});

Route::group(['prefix' => 'branches', 'controller' => BranchController::class], function () {
    Route::get('/', 'index');
});

Route::group(['prefix' => 'faqs', 'controller' => FAQController::class],function(){
    Route::get('/', 'index');
});

Route::group(['prefix' => 'notifications', 'controller' => NotificationController::class],function(){
    Route::get('/','index');
    Route::post('/read','read');
    Route::get('/{id}','show');
    Route::post('/mark-all-as-read','markAllAsRead');
});

Route::post('/storePointRedemptionQR', [UserController::class, 'storePointRedemptionQR'])->name('storePointRedemptionQR');
Route::post('/validatePointRedemptionQR', [UserController::class, 'validatePointRedemptionQR'])->name('validatePointRedemptionQR');
Route::post('/storeCouponQR', [UserController::class, 'storeCouponQR'])->name('storeCouponQR');
Route::post('/validateCouponQR', [UserController::class, 'validateCouponQR'])->name('validateCouponQR');

Route::post('/sendReceivePointNotification', [UserController::class, 'sendReceivePointNotification'])->name('sendReceivePointNotification');
Route::post('/sendClaimPointNotification', [UserController::class, 'sendClaimPointNotification'])->name('sendClaimPointNotification');
Route::post('/sendTransferPointNotification', [UserController::class, 'sendTransferPointNotification'])->name('sendTransferPointNotification');
Route::post('/sendUsePointNotification', [UserController::class, 'sendUsePointNotification'])->name('sendUsePointNotification');
Route::post('/sendNewPointRedemptionProgramNotification', [UserController::class, 'sendNewPointRedemptionProgramNotification'])->name('sendNewPointRedemptionProgramNotification');
Route::post('/sendNewCouponNotification', [UserController::class, 'sendNewCouponNotification'])->name('sendNewCouponNotification');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
