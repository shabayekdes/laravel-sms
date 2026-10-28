<?php

namespace Shabayek\Sms\Drivers;

use Exception;
use Illuminate\Support\Str;
use Shabayek\Sms\Enums\Service;

/**
 * Driver class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
abstract class Driver
{
    /**
     * Username.
     *
     * @var string
     */
    protected $username;
    /**
     * Password.
     *
     * @var string
     */
    protected $password;
    /**
     * Sender ID.
     *
     * @var string
     */
    protected $sender_id;
    /**
     * Language.
     *
     * @var string
     */
    protected $language;
    /**
     * Service of sms gateway.
     *
     * @var string
     */
    protected $service = Service::SMS_NORMAL_SERVICE;

    /**
     * Driver Constructor.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  string  $sender_id
     * @return void
     */
    public function __construct($username, $password, $sender_id)
    {
        $this->username = $username;
        $this->password = $password;
        $this->sender_id = $sender_id;

        $this->language = config('sms.language');
    }

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
    public function verify($phone, $otp, $actualOtp = null): bool
    {
        if (is_null($actualOtp)) {
            throw new \Exception('Actual otp is required for this service');
        }

        if (! $this->isValidOtp($otp) || ! $this->isValidOtp($actualOtp)) {
            return false;
        }

        return hash_equals((string) $actualOtp, (string) $otp);
    }

    /**
     * Determine if the given value is a well-formed otp.
     *
     * Only non-negative integers and digit-only strings are accepted, so
     * booleans, floats and padded or numeric-looking strings never match.
     *
     * No return type is declared, so subclasses that already define this
     * method without one remain compatible.
     *
     * @param  mixed  $otp
     * @return bool
     */
    protected function isValidOtp($otp)
    {
        if (is_int($otp)) {
            return $otp >= 0;
        }

        return is_string($otp) && preg_match('/^\d+\z/', $otp) === 1;
    }

    /**
     * Generate otp code.
     *
     * The fixed development code is limited to the local and testing
     * environments; any other environment name gets a random code.
     *
     * @return int
     */
    protected function generateCode()
    {
        if (app()->environment('local', 'testing')) {
            $code = 1234;
        } else {
            $code = random_int(100000, 999999);
        }

        return $code;
    }

    /**
     * Send sms message.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    protected function getMessage($code): string
    {
        $message = config("sms.message.{$this->language}", 'Your verification code is: {code}');

        return Str::replace('{code}', $code, $message);
    }

    /**
     * Language.
     *
     * @param  string  $language  Language
     * @return self
     */
    public function setLanguage($language): self
    {
        $this->language = $language;

        return $this;
    }
}
