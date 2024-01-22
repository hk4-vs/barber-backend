<?php

use App\Http\Controllers\ShopOwnerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SheetBookingModelController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ########################### AUTH USERS  ##################################
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('user/update', [UserController::class, 'updateUserData']);
    Route::get('shops', [ShopController::class, 'index']);
    Route::get('shops/{id}', [ShopController::class, 'show']);


    // ########################### ONLY SHOP OWNERS  ##################################
    Route::middleware('user.type:shopOwner')->group(function () {
        Route::get('/shop-owner/profile', [ShopOwnerController::class, 'getShopOwnerProfile']);
        Route::post('/shop/upload-images', [ShopController::class, 'uploadShopImages']);
        Route::delete('/shop/delete-images/{id}', [ShopController::class, 'deleteImage']);
        Route::get('/services', [ServicesController::class, 'index']);
        Route::post('/services/create', [ServicesController::class, 'create']);
        Route::put('/services/update/{id}', [ServicesController::class, 'update']);
        Route::delete('/services/delete/{id}', [ServicesController::class, 'delete']);
    });

    // ########################### ONLY USERS  ##################################
    Route::middleware('user.type:user')->group(function () {
        Route::post('user/booking', [SheetBookingModelController::class, 'create']);
    });
});



// ########################### ALL USERS ##################################
Route::post('user/register', [UserController::class, 'createUser']);
Route::post('user/verify-otp', [UserController::class, 'verifyOtp']);


Route::post('login', [UserController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/password/reset', [ForgotPasswordController::class, 'sendResetLink']);




// ########################### SHOP OWNERS  ##################################
Route::post('shop-owner/register', [ShopOwnerController::class, 'createShopOwner']);
Route::post('shop-owner/verify-otp', [ShopOwnerController::class, 'verifyOtp']);
