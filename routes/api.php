<?php

use App\Http\Controllers\Api\ApiAuthenticateController;
use App\Http\Controllers\Api\ApiPermissionController;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Middleware\AccountAuthenticate;
use App\Http\Middleware\EncryptDecryptMiddleware;
use App\Http\Middleware\LoginThrottle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\PermissionsAuthenticate;

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

// Route::middleware(EncryptDecryptMiddleware::class)->group(function () {
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('login', [ApiAuthenticateController::class, 'login'])->middleware(LoginThrottle::class);
    Route::post('request-reset-password', [ApiAuthenticateController::class, 'requestResetPassword']);
    Route::post('verify-otp', [ApiAuthenticateController::class, 'verifyOtp']);
    Route::post('change-password', [ApiAuthenticateController::class, 'changePassword']);

    Route::middleware(AccountAuthenticate::class)->group(function () {
        Route::post('logout', [ApiAuthenticateController::class, 'logout']);
    });
});

Route::middleware(AccountAuthenticate::class)->group(function () {
    Route::prefix('dashboard')->middleware([PermissionsAuthenticate::class])->group(function () {
        Route::prefix('user')->name("user.")->group(function () {
            Route::post('create', [ApiUserController::class, 'create']);
            Route::delete('delete', [ApiUserController::class, 'delete']);
            Route::prefix('{id}')->group(function () {
                Route::post('update', [ApiUserController::class, 'update']);
                Route::get('detail', [ApiUserController::class, 'detail']);
            });
            Route::name('list')->get('list', [ApiUserController::class, 'list']);
        });

        Route::prefix('permission')->name("permission.")->group(function () {
            Route::get('list', [ApiPermissionController::class, 'list']);
            Route::post('create', [ApiPermissionController::class, 'create']);
            Route::prefix('{id}')->group(function () {
                Route::post('update', [ApiPermissionController::class, 'update']);
                Route::get('detail', [ApiPermissionController::class, 'detail']);
            });
            Route::delete('delete', [ApiPermissionController::class, 'delete']);
        });
    });
});
// });
