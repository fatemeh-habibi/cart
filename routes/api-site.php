<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Site\V1\AuthController;
use App\Http\Controllers\Site\V1\CartController;
use App\Http\Controllers\Site\V1\ProductController;
use App\Http\Controllers\Site\V1\ProfileController;

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
    Route::group(['prefix' => 'auth'], function () {
        Route::post('send_otp', [AuthController::class, 'send_otp'])->name('site_send_otp');
        Route::post('verify_otp', [AuthController::class, 'verify_otp'])->name('site_verify_otp');
    });
    Route::group(['middleware' => ['auth:customer']], function () {
        
        //profile
        Route::group(['prefix' => 'profile'], function () {
            Route::get("/", [ProfileController::class, "index"])->name("site_profile");
            Route::put("/update", [ProfileController::class, "update"])->name("site_profile_update");
        });

        //product
        Route::group(['prefix' => 'product'], function () {
            Route::get('list', [ProductController::class, 'index'])->name('site_product_list');
            Route::get('/{product_url}', [ProductController::class, 'show'])->name('site_product_show');
        });

        //cart
        Route::group(['prefix' => 'cart'], function () {
            Route::get('list', [CartController::class, 'index'])->name('site_cart_list');
            Route::post('/', [CartController::class, 'store'])->name('site_cart_store');
            Route::put("/update", [CartController::class, "update"])->name("site_cart_update");
            Route::delete('{cart_product_id}', [CartController::class, 'delete'])->name('site_cart_delete');
        });
        
    });
});
