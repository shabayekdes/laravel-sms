<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Connection Name
    |--------------------------------------------------------------------------
    |
    | The connection below that the Sms facade uses by default.
    |
    | Supported: "smseg", "smsmisr", "victorylink", "ooredoo", "null"
    | Choose "null" to turn sending off.
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
        | SMS EG Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for sms eg gateway. Of course There are
        | two types of service you can choose one of:
        | normal => using message service
        | otp => using otp service
        |
        */

        'smseg' => [
            'driver' => 'smseg',
            'username' => env('SMS_USERNAME'),
            'password' => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
            'service' => env('SMS_SERVICE', 'normal'),
        ],

        /*
        |--------------------------------------------------------------------------
        | SMS Misr Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for sms misr gateway. Of course There are
        | two types of service you can choose one of:
        | normal => using message service
        | otp => using otp service
        |
        */

        'smsmisr' => [
            'driver' => 'smsmisr',
            'username' => env('SMS_USERNAME'),
            'password' => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
            'service' => env('SMS_SERVICE', 'normal'),
            'token' => env('SMS_TOKEN'),
            'msignature' => env('SMS_MSIGNATURE'),
            'sms_id' => env('SMS_SMS_ID'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Victory Link Connection
        |--------------------------------------------------------------------------
        |
        | Here are credentials for sms victory link gateway.
        |
        */

        'victorylink' => [
            'driver' => 'victorylink',
            'username' => env('SMS_USERNAME'),
            'password' => env('SMS_PASSWORD'),
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
            'driver' => 'ooredoo',
            'base_url' => env('SMS_BASE_URL', 'https://messaging.ooredoo.qa/bms/soap/Messenger.asmx'),
            'username' => env('SMS_USERNAME'),
            'password' => env('SMS_PASSWORD'),
            'sender_id' => env('SMS_SENDER_ID'),
            'customer_id' => env('SMS_CUSTOMER_ID'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Custom Message For Otp Service
    |--------------------------------------------------------------------------
    |
    | The message sent with the otp code. Put your custom message here and
    | use the keyword {code} to replace it with the verification code.
    | A message without {code} makes sendOtp() throw before sending.
    | The encoding sent to the gateway is detected from the text.
    |
    */

    'message' => env('SMS_MESSAGE', 'Your verification code is: {code}'),

    /*
    |--------------------------------------------------------------------------
    | Response Messages
    |--------------------------------------------------------------------------
    |
    | The text returned under the "message" key of a send() result for each
    | response code. Check the "code" key in your application and change
    | these texts freely. Codes are listed in Shabayek\Sms\Enums\ResponseCode.
    |
    */

    'messages' => [
        'sent' => 'Message sent successfully',
        'rejected' => 'Message not sent successfully',
        'http_error' => 'Message not sent successfully',
        'invalid_response' => 'Message not sent successfully',
        'disabled' => 'SMS driver is disabled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway Request Timeouts
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a gateway to connect and to
    | answer a request. A timed out request throws a ConnectionException.
    | The gateway may still have accepted the message, so do not resend
    | automatically. Set a value to 0 to wait indefinitely.
    |
    */

    'timeout' => env('SMS_TIMEOUT', 30),

    'connect_timeout' => env('SMS_CONNECT_TIMEOUT', 10),

];
