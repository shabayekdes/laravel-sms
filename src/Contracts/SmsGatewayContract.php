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
     * Returns the generated code once the gateway accepts the message, or
     * null when no code was sent or the gateway manages the code itself.
     *
     * @param  string  $phone
     * @return int|null
     */
    public function sendOtp($phone);

    /**
     * Verify phone number.
     *
     * @param  string  $phone
     * @param  int  $otp
     * @param  int|null  $actualOtp
     * @return bool
     *
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
