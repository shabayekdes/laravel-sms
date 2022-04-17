<?php

namespace Shabayek\Sms\Drivers;

use Shabayek\Sms\Contracts\SmsGatewayContract;

/**
 * SMS Null class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsNull extends Driver implements SmsGatewayContract
{
    /**
     * Send sms message.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    public function send($phone, $message): array
    {
        return [
            'success' => false,
            'message' => 'SMS driver is disabled',
        ];
    }

    /**
     * send otp verification.
     *
     * @param  string  $phone
     * @param  string|null  $message
     * @return int|null
     */
    public function sendOtp($phone, $message = null)
    {
        return null;
    }

    /**
     * Get balance.
     *
     * @return int
     */
    public function balance()
    {
        return 0;
    }
}
