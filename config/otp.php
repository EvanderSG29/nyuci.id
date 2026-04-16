<?php

use App\Notifications\RegisterOtpNotification;
use App\Otp\Stores\MailOtpCacheStore;

return [
    /*
    |--------------------------------------------------------------------------
    | OTP store
    |--------------------------------------------------------------------------
    |
    | Store for OTP
    |
    */
    'store' => MailOtpCacheStore::class,

    /*
    |--------------------------------------------------------------------------
    | OTP key
    |--------------------------------------------------------------------------
    |
    | Key used to store and retrieve OTP
    |
    */
    'store_key' => env('OTP_STORE_KEY', 'otp'),

    /*
    |--------------------------------------------------------------------------
    | OTP format
    |--------------------------------------------------------------------------
    |
    | Can be one of alpha, alphanumeric, numeric
    | See: https://github.com/Hi-Folks/rando-php#random-string
    |
    */
    'format' => env('OTP_FORMAT', 'numeric'),

    /*
    |--------------------------------------------------------------------------
    | OTP characters length
    |--------------------------------------------------------------------------
    |
    | Number of characters of OTP
    |
    */
    'length' => env('OTP_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | OTP expiration
    |--------------------------------------------------------------------------
    |
    | Number of minutes before OTP expires
    |
    */
    'expires' => env('OTP_EXPIRES', 15),

    /*
    |--------------------------------------------------------------------------
    | OTP notification
    |--------------------------------------------------------------------------
    |
    | Notification to use for OTP
    |
    */
    'notification' => RegisterOtpNotification::class,

    /*
    |--------------------------------------------------------------------------
    | OTP resend cooldown
    |--------------------------------------------------------------------------
    |
    | Number of seconds before users can request another OTP code
    |
    */
    'resend_cooldown' => env('OTP_RESEND_COOLDOWN', 60),
];
