<?php

namespace Shabayek\Sms\Drivers;

use Exception;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;

class SmsMisr implements SmsGatewayContract
{
    const SMS_NORMAL_SERVICE = 'normal';
    const SMS_OTP_SERVICE = 'otp';

    private $base_url = 'https://smsmisr.com/api';
    private $username;
    private $password;
    private $sender_id;

    private $msignature;
    private $sms_id;
    private $token;
    private $language;

    /**
     * SmsMisr Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->service = $config['service'];

        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->sender_id = $config['sender_id'];

        $this->language = $this->getLanguage();
        $this->token = $config['token'];
        $this->msignature = $config['msignature'];
        $this->sms_id = $config['sms_id'];
    }

    /**
     * Send sms message.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    public function send($phone, $message): array
    {
        $response = $this->sendMessageRequest($phone, $message);
        $success = true;
        $message = 'Message sent successfully';

        if (isset($response['code']) && $response['code'] != 1901) {
            $success = false;
            $message = 'Message not sent successfully';
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    /**
     * send otp verification.
     *
     * @param  string|int  $phone
     * @param  string|null  $message
     * @return int|null
     */
    public function sendOtp($phone, $message = null)
    {
        $code = $this->generateCode();

        if ($this->service == self::SMS_OTP_SERVICE) {
            $this->sendOtpRequest($phone, $code);
        }

        if ($this->service == self::SMS_NORMAL_SERVICE) {
            if (is_null($message)) {
                $message = 'Your verification code is: '.$code;
            }
            $this->send($phone, $message);
        }

        return $code;
    }

    /**
     * Verify phone number.
     *
     * @param  string|int  $phone
     * @param  string  $otp
     * @return bool
     */
    public function verify($phone, $otp): bool
    {
        throw new Exception('This service is not supported');
    }

    /**
     * Get balance.
     *
     * @return int
     */
    public function balance()
    {
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'SMSID' => $this->sms_id,
            'request' => 'status',
        ];

        $response = Http::post($this->base_url.'/Request?'.http_build_query($params));
        $result = $response->json();

        if (isset($result['code']) && $result['code'] == 'Error') {
            return 0;
        }

        return $result['balance'];
    }

    /**
     * Send sms with otp services.
     *
     * @param  string  $phone
     * @param  int  $code
     * @return void
     */
    private function sendOtpRequest($phone, $code)
    {
        $params = [
            'Username' => $this->username,
            'password' => $this->password,
            'SMSID' => $this->sms_id,
            'Msignature' => $this->msignature,
            'Token' => $this->token,
            'Mobile' => $phone,
            'Code' => $code,
        ];
        Http::post($this->base_url.'/vSMS', $params);
    }

    /**
     * Send sms with message services.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    private function sendMessageRequest($phone, $message)
    {
        $params = [
            'Username' => $this->username,
            'password' => $this->password,
            'language' => $this->language,
            'sender' => $this->sender_id,
            'Mobile' => $phone,
            'message' => $message,
            'DelayUntil' => null,
        ];

        $response = Http::post($this->base_url.'/v2', $params);

        return $response->json();
    }

    /**
     * Generate otp code.
     *
     * @return int
     */
    private function generateCode()
    {
        if (app()->environment('production')) {
            $code = rand(100000, 999999);
        } else {
            $code = 1234;
        }

        return $code;
    }

    /**
     * Get message language.
     *
     * @return int
     */
    private function getLanguage()
    {
        switch (config('sms.language')) {
            case 'en':
                $locale = 1;
                break;
            case 'ar':
                $locale = 2;
                break;
            default:
                $locale = 3;
                break;
        }

        return $locale;
    }
}
