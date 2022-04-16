<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the sms connections company below you wish
    | to use as your default connection for all companies work. Of course
    |
    | Supported companies: "smseg", "smsmisr", "null"
    | if you choosing null, sms will be disabled
    |
    */

    'default' => env('SMS_CONNECTION', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Sms Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the sms connections companies setup for your application.
    |
    */

    'connections' => [

        /*
        |--------------------------------------------------------------------------
        | Sms EG Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for sms eg company. Of course There are
        | two types of service you can choose one of:
        | normal => using message service
        | otp => using otp service
        |
        */

        'smseg' => [
            'driver'    => 'smseg',
            'username'  => env('SMS_USERNAME'),
            'password'  => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
            'service'   => env('SMS_SERVICE', 'normal'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Sms EG Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for sms misr company. Of course There are
        | two types of service you can choose one of:
        | normal => using message service
        | otp => using otp service
        |
        */

        'smsmisr' => [
            'driver'    => 'smsmisr',
            'username'  => env('SMS_USERNAME'),
            'password'  => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
            'service'   => env('SMS_SERVICE', 'normal'),
            'token' => env('SMS_TOKEN'),
            'msignature' => env('SMS_MSIGNATURE'),
            'sms_id' => env('SMS_SMS_ID'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Sms EG Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for sms victory link company.
        |
        */

        'victorylink' => [
            'driver'    => 'victorylink',
            'username'  => env('SMS_USERNAME'),
            'password'  => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Message Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The sms message locale determines the default locale that will be used
    |
    */

    'language' => env('SMS_LANGUAGE', 'ar'),

];
