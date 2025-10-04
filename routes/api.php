<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',[AuthController::class,'login'])->name('login');
Route::post('/get-points',[AuthController::class,'getPoints'])->name('points');
Route::get('/promotions',[PromotionController::class,'index'])->name('promotions');
Route::get('/promotions/{id}',[PromotionController::class,'show'])->name('promotion');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
