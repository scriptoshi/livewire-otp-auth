<?php

use Illuminate\Support\Facades\Route;
use Scriptoshi\LivewireOtpAuth\Http\Controllers\OtpController;

// Only register routes if they don't already exist
Route::group(['prefix' => 'otp', 'middleware' => ['web']], function () {
    if (!Route::has('otp.verify')) {
        Route::get('verify/{code}', [OtpController::class, 'verify'])->name('otp.verify');
    }
});
