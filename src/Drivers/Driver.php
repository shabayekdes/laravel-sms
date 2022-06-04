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
    public function verify(string $phone, int $otp, $actualOtp = null): bool
    {
        if (is_null($actualOtp)) {
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
