<?php

use App\Http\Controllers\API\v1\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WALLET Routes
|--------------------------------------------------------------------------
*/

Route::prefix('wallets')
    ->middleware('auth:sanctum')
    ->controller(WalletController::class)
    ->group(function () {
    Route::get('', 'index');
});
