<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the sms connections gateway below you wish
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
        | Here are credentials for sms eg gateway. Of course There are
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
        | Here are credentials for sms misr gateway. Of course There are
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
        | Here are credentials for sms victory link gateway.
        |
        */

        'victorylink' => [
            'driver'    => 'victorylink',
            'username'  => env('SMS_USERNAME'),
            'password'  => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Ooredoo Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for Ooredoo gateway.
        |
        */

        'ooredoo' => [
            'driver'      => 'ooredoo',
            'base_url'    => env('SMS_BASE_URL', 'https://messaging.ooredoo.qa/bms/soap/Messenger.asmx'),
            'username'    => env('SMS_USERNAME'),
            'password'    => env('SMS_PASSWORD'),
            'sender_id'   => env('SMS_SENDER_ID'),
            'customer_id' => env('SMS_CUSTOMER_ID'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Unifonic Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for Unifonic gateway.
        |
        */

        'unifonic' => [
            'driver'    => 'unifonic',
            'username'    => env('SMS_USERNAME'),
            'password'    => env('SMS_PASSWORD'),
            'apps_id'   => env('SMS_APPS_ID'),
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

    /*
    |--------------------------------------------------------------------------
    | SMS Custom Message For Otp Service
    |--------------------------------------------------------------------------
    |
    | The sms custom message that will be used it will be replaced with
    | the default message "Your verification code is: {code}"
    | put your custom message here and use the keyword
    | {code} to replace it with the verification code
    |
    */

    'message' => [
        'ar' => env('SMS_MESSAGE_AR', 'كود التحقق الخاص بك هو: {code}'),
        'en' => env('SMS_MESSAGE_EN', 'Your verification code is: {code}'),
    ],

];
