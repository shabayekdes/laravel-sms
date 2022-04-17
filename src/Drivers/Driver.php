<?php

namespace Shabayek\Sms\Drivers;

use Exception;

/**
 * Driver class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class Driver
{
    /**
     * Service of sms gateway
     * @var string
     */
    protected $service;

    /**
     * Verify phone number.
     *
     * @param string $phone
     * @param int $otp
     * @param int|null $actualOtp
     * @return bool
     * @throws Exception
     */
    public function verify(string $phone, int $otp, $actualOtp = null): bool
    {
        if(is_null($actualOtp)) {
            throw new \Exception('Actual otp is required for this service');
        }
        return $otp == $actualOtp;
    }

    /**
     * Generate otp code.
     *
     * @return int
     */
    protected function generateCode()
    {
        if (app()->environment('production')) {
            $code = rand(100000, 999999);
        } else {
            $code = 1234;
        }

        return $code;
    }
}
