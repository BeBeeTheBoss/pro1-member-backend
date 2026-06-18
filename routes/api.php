<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ClaimedKeyController;
use App\Http\Controllers\Api\FAQController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PointTransferController;
use App\Http\Controllers\Api\PopupController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\PrivilegeController;
use App\Http\Controllers\Api\PrivilegeCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\DailyRewardController;
use App\Http\Controllers\Api\GamesEventController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SpinWheelChanceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/resend-otp', [UserController::class, 'resendOtp']);
Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
Route::get('/games-events', [GamesEventController::class, 'index']);

Route::group(['prefix' => 'users', 'controller' => UserController::class], function () {
    Route::get('/get', 'getUser')->name('getUser');
    Route::get('/search', 'searchUser')->name('searchUser');
    Route::post('/update', 'updateUser')->name('updateUser');
    Route::post('/find-member', 'findMember')->name('findMember');
    Route::post('/change-password', 'changePassword')->name('changePassword');
    Route::post('/forgot-password', 'forgotPassword')->name('forgotPassword');
    // Route::post('/logout', 'logout')->name('logout');
});

Route::get('/events', [EventController::class, 'index']);

Route::get('/spin-wheel-chances', [SpinWheelChanceController::class, 'index']);
Route::get('/getLatestVersion', [SettingController::class, 'getLatestVersion'])->name('getLatestVersion');



Route::middleware('auth:sanctum')->group(function () {

    Route::group(['prefix' => 'users', 'controller' => UserController::class], function () {
        Route::post('/set-push-token', 'setPushToken')->name('setPushToken');
        Route::post('/set-app-version', 'setAppVersion')->name('setAppVersion');
        Route::post('/logout', 'logout')->name('logout');
    });

    Route::post('/session/start', [SessionController::class, 'startSession']);
    Route::post('/session/end', [SessionController::class, 'endSession']);
    Route::post('/daily-rewards/claim', [DailyRewardController::class, 'claim']);
    Route::get('/claimed-keys', [ClaimedKeyController::class, 'get']);
    Route::post('/claimed-keys', [ClaimedKeyController::class, 'store']);

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
        Route::get('/select', 'select');
    });


    Route::group(['prefix' => 'faqs', 'controller' => FAQController::class], function () {
        Route::get('/', 'index');
    });

    Route::group(['prefix' => 'feedbacks', 'controller' => FeedbackController::class], function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });

    Route::group(['prefix' => 'notifications', 'controller' => NotificationController::class], function () {
        Route::get('/', 'index');
        Route::post('/read', 'read');
        Route::get('/{id}', 'show');
        Route::post('/mark-all-as-read', 'markAllAsRead');
        Route::post('/delete', 'destroyMultiple');
        Route::delete('/{id}', 'destroy');
    });

    Route::get('/popups', [PopupController::class, 'index']);

    Route::get('/privileges', [PrivilegeController::class, 'index']);
    Route::get('/privilege-categories', [PrivilegeCategoryController::class, 'index']);

    Route::post('/spin-wheel-play', [SpinWheelChanceController::class, 'play']);

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
Route::post('/sendUseCouponNotification', [UserController::class, 'sendUseCouponNotification'])->name('sendUseCouponNotification');
Route::post('/sendSpinWheelPointNotification', [UserController::class, 'sendSpinWheelPointNotification'])->name('sendSpinWheelPointNotification');

Route::post('/updateMemberData', [UserController::class, 'updateMemberData'])->name('updateMemberData');
Route::post('/toggleActivateMember', [UserController::class, 'toggleActivateMember'])->name('toggleActivateMember');

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
