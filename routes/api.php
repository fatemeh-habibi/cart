<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\V1\AuthController;
use App\Http\Controllers\Admin\V1\UserController;
use App\Http\Controllers\Admin\V1\ProductController;

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

Route::group([
    // 'middleware' => Transaction::class
], function () {

    //auth
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::group(['middleware' => 'auth:api'], function(){
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::group(['prefix' => 'user/profile'], function () {
            Route::get('/', [UserController::class, 'profile_show'])->name('profile_show');
            Route::put('update', [UserController::class, 'profile_update'])->name('profile_update');
            Route::put('change_password', [UserController::class, 'profile_change_password'])->name('profile_change_password');
        });
        Route::group(['middleware' => 'role'], function(){
            //user
            Route::group(['prefix' => 'user'], function () {
                Route::get('list', [UserController::class, 'index'])->name('user_list');
                Route::post('permission_assign', [UserController::class, 'permission_store'])->name('user_permission_store');
                Route::post('/', [UserController::class, 'store'])->name('user_store');
                Route::put('{user_id}', [UserController::class, 'update'])->name('user_update');
                Route::delete('{user_id}', [UserController::class, 'delete'])->name('user_delete');
            });
            //product
            Route::group(['prefix' => 'product'], function () {
                Route::get('list', [ProductController::class, 'index'])->name('product_list');
                Route::post('/', [ProductController::class, 'store'])->name('product_store');
                Route::put('{product_id}', [ProductController::class, 'update'])->name('product_update');
                Route::delete('{product_id}', [ProductController::class, 'delete'])->name('product_delete');
                Route::patch('{product_id}', [ProductController::class, 'restore'])->name('product_restore');
                Route::get('{product_id}', [ProductController::class, 'show'])->name('product_show');
            });
            
        });
    });

    //forget password
    Route::post('send_otp', [AuthController::class, 'send_otp'])->name('send_otp');
    Route::post('verify_otp', [AuthController::class, 'verify_otp'])->name('verify_otp');
    Route::post('reset_password', [AuthController::class, 'reset_password'])->name('reset_password');
});
