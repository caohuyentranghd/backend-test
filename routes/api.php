<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MovieController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1', 'middleware' => []], function () {
    // For movie
    Route::group(['prefix' => 'movie', 'middleware' => []], function () {
        Route::get('/', [MovieController::class, 'index']);
        Route::get('/show/{id}', [MovieController::class, 'show']);
        // For movie admin
        Route::group(['middleware' => ['auth:sanctum', 'admin_api']], function () {
            Route::post('/store', [MovieController::class, 'store']);
            Route::put('/update/{id}', [MovieController::class, 'update']);
            Route::delete('/destroy/{id}', [MovieController::class, 'destroy']);
        });
    });

    // FOR AUTH
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/login', [AuthController::class, 'login']);

    // For User
    Route::group(['prefix' => 'user', 'middleware' => ['auth:sanctum']], function () {
        Route::get('/info', [UserController::class, 'getInfo']);
        Route::put('/update-profile', [UserController::class, 'updateProfile']);
    });

    // For Favorite
    Route::group(['prefix' => 'favorite', 'middleware' => ['auth:sanctum']], function () {
        Route::post('/store', [FavoriteController::class, 'store']);
        Route::delete('/remove', [FavoriteController::class, 'destroy']);
    });
});
