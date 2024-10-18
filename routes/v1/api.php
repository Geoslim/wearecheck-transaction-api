<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('', function () {
    return [
        'message' => 'We Are Check Transaction API ' . config('app.env') . ' Server',
    ];
});
