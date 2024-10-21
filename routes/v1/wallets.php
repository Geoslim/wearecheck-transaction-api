<?php

use App\Http\Controllers\API\v1\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WALLET Routes
|--------------------------------------------------------------------------
*/

Route::prefix('wallet')
    ->middleware('auth:sanctum')
    ->controller(WalletController::class)
    ->group(function () {
    Route::get('', 'index');
});
