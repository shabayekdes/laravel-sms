<?php

namespace Shabayek\Sms\Drivers;

use Shabayek\Sms\Enums\Service;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;

/**
 * SmsEg class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsEg extends Driver implements SmsGatewayContract
{
    /**
     * Base url.
     * @var string
     */
    protected $base_url = 'https://smssmartegypt.com/sms/api';

    /**
     * Username.
     * @var string
     */
    private $username;
    /**
     * Password.
     * @var string
     */
    private $password;
    /**
     * Sender ID.
     * @var string
     */
    private $sender_id;

    /**
     * SmsEg Constructor.
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

        if (isset($response['type']) && $response['type'] == 'error') {
            $success = false;
            $message = data_get($response, 'error.msg', 'Message not sent successfully');
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
        $code = null;

        if ($this->service == Service::SMS_OTP_SERVICE) {
            $this->sendOtpRequest($phone);
        }

        if ($this->service == Service::SMS_NORMAL_SERVICE) {
            $code = $this->generateCode();
            if (is_null($message)) {
                $message = 'Your verification code is: '.$code;
            }
            $this->send($phone, $message);
        }

        return $code;
    }

    /**
     * Verify phone number
     *
     * @param string $phone
     * @param int $otp
     * @param int|null $actualOtp
     * @return bool
     */
    public function verify(string $phone, $otp, $actualOtp = null): bool
    {
        if ($this->service == Service::SMS_OTP_SERVICE) {
            return $this->verifyOtpRequest($phone, $otp);
        }
        return parent::verify($phone, $otp, $actualOtp);
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
        ];
        $response = Http::post($this->base_url.'/getBalance', $params);
        $result = $response->json();
        if (isset($result['type']) && $result['type'] == 'error') {
            return 0;
        }

        return $result['data']['points'];
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
            'username' => $this->username,
            'password' => $this->password,
            'sendername' => $this->sender_id,
            'mobiles' => $phone,
            'message' => $message,
        ];

        $response = Http::post($this->base_url, $params);

        return $response->json();
    }

    /**
     * Send sms with otp services.
     *
     * @param  string  $phone
     * @return void
     */
    private function sendOtpRequest($phone)
    {
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'sender' => $this->sender_id,
            'mobile' => $phone,
            'lang' => 'ar',
        ];
        Http::post($this->base_url.'/otp-send', $params);
    }

    /**
     * Verify otp for user.
     *
     * @param  string|int  $phone
     * @param  string  $otp
     * @return bool
     */
    private function verifyOtpRequest($phone, $otp)
    {
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'mobile' => $phone,
            'otp' => $otp,
            'verify' => true,
        ];
        $response = Http::post($this->base_url.'/otp-check', $params);

        $result = $response->json();

        if (isset($result['type']) && $result['type'] == 'success') {
            return true;
        }

        return false;
    }
}
