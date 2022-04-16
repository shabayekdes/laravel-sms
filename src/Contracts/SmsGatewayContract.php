<?php

namespace Shabayek\Sms\Contracts;

/**
 * SmsGatewayContract interface.
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
interface SmsGatewayContract
{
    /**
     * Send sms message
     *
     * @param  string $phone
     * @param  string $message
     * @return  array
     */
    public function send($phone, $message): array;
    /**
     * send otp verification
     *
     * @param  string $phone
     * @param  string|null $message
     * @return integer|null
     */
    public function sendOtp($phone, $message = null);
    /**
     * Verify phone number
     *
     * @param string|int $phone
     * @param  string $otp
     * @return bool
     */
    public function verify($phone, $otp): bool;
    /**
     * Get balance
     *
     * @return int
     */
    public function balance();
}
