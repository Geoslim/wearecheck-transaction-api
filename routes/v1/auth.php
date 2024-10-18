<?php

use App\Http\Controllers\API\v1\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', 'logout');
    });
});
