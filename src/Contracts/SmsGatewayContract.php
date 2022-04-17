<?php

namespace Shabayek\Sms\Contracts;

/**
 * SmsGatewayContract interface.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
interface SmsGatewayContract
{
    /**
     * Send sms message.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    public function send($phone, $message): array;

    /**
     * send otp verification.
     *
     * @param  string  $phone
     * @param  string|null  $message
     * @return int|null
     */
    public function sendOtp($phone, $message = null);

    /**
     * Verify phone number.
     *
     * @param string $phone
     * @param int $otp
     * @param int|null $actualOtp
     * @return bool
     * @throws Exception
     */
    public function verify(string $phone, int $otp, $actualOtp = null): bool;

    /**
     * Get balance.
     *
     * @return int
     */
    public function balance();
}
