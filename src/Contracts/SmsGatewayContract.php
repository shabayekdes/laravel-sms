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
     * Returns success, a \Shabayek\Sms\Enums\ResponseCode case, the
     * message configured in sms.messages for that code, and the provider's
     * own code and message when the gateway returned them:
     * ['success' => bool, 'code' => ResponseCode, 'message' => string,
     *  'provider' => ['code' => mixed, 'message' => mixed]].
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    public function send($phone, $message): array;

    /**
     * send otp verification.
     *
     * Returns the generated code when the driver treats the request as sent,
     * or null when it does not. How much that proves depends on the driver:
     * some check a provider success value, the SMS Misr otp service checks
     * only the HTTP status, and none of them confirm delivery. Drivers where
     * the gateway manages the code itself (SMS EG otp service) return null
     * whether or not the request succeeded. Connection failures throw.
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
