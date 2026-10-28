<?php

namespace Shabayek\Sms\Tests\Fixtures;

use Shabayek\Sms\Drivers\Driver;

/**
 * A consumer driver written before Driver::isValidOtp() existed, declaring
 * its own method of the same name without a return type.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class LegacyOtpDriver extends Driver
{
    protected function isValidOtp($otp)
    {
        return is_string($otp) && preg_match('/^\d{6}\z/', $otp) === 1;
    }
}
