<?php

use App\Http\Controllers\API\v1\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TRANSACTION Routes
|--------------------------------------------------------------------------
*/

Route::prefix('transactions')
    ->middleware('auth:sanctum')
    ->controller(TransactionController::class)
    ->group(function () {
        Route::post('create', 'createTransaction');
    });
