<?php

namespace Shabayek\Sms\Drivers;

/**
 * Driver class
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class Driver
{
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
