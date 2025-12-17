<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\PointTransferController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register'])->name('register');

Route::group(['prefix' => 'users', 'controller' => UserController::class], function () {
    Route::get('/get', 'getUser')->name('getUser');
    Route::get('/search', 'searchUser')->name('searchUser');
    Route::post('/update', 'updateUser')->name('updateUser');
    Route::post('/find-member','findMember')->name('findMember');
    Route::post('/change-password', 'changePassword')->name('changePassword');
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

//QR
// Route::group(['prefix' => 'qr', 'controller' => UserController::class],function(){
//     Route::store('/')
// });
Route::post('/storePointRedemptionQR', [UserController::class, 'storePointRedemptionQR'])->name('storePointRedemptionQR');
Route::post('/validatePointRedemptionQR', [UserController::class, 'validatePointRedemptionQR'])->name('validatePointRedemptionQR');
Route::post('/validateCouponQR', [UserController::class, 'validateCouponQR'])->name('validateCouponQR');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
