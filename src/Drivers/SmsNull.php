<?php

namespace Shabayek\Sms\Drivers;

use Shabayek\Sms\Contracts\SmsGatewayContract;

/**
 * SMS Null class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsNull implements SmsGatewayContract
{
    /**
     * Send sms message
     *
     * @param  string $phone
     * @param  string $message
     * @return  array
     */
    public function send($phone, $message): array
    {
        return [
            'success' => false,
            'message' => 'SMS driver is disabled'
        ];
    }
    /**
     * send otp verification
     *
     * @param  string $phone
     * @param  string|null $message
     * @return integer|null
     */
    public function sendOtp($phone, $message = null)
    {
        return null;
    }
    /**
     * Verify phone number
     *
     * @param string|int $phone
     * @param  string $otp
     * @return bool
     */
    public function verify($phone, $otp): bool
    {
        return true;
    }
    /**
     * Get balance
     *
     * @return int
     */
    public function balance()
    {
        return 0;
    }
}
