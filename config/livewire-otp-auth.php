<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Expiration Time
    |--------------------------------------------------------------------------
    |
    | This value determines how long an OTP is valid for in minutes.
    |
    */
    'expiration_time' => 10,

    /*
    |--------------------------------------------------------------------------
    | OTP Length
    |--------------------------------------------------------------------------
    |
    | This value determines the length of the OTP code.
    |
    */
    'otp_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | Resend Cooldown
    |--------------------------------------------------------------------------
    |
    | Cooldown time in seconds before a user can request another OTP.
    |
    */
    'resend_cooldown' => 60,

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Number of OTP attempts before rate limiting is applied.
    |
    */
    'rate_limit_attempts' => 5,

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Duration
    |--------------------------------------------------------------------------
    |
    | Duration in minutes for which rate limiting is applied.
    |
    */
    'rate_limit_duration' => 5,
];
