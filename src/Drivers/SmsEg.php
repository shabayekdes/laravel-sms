<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;
use Shabayek\Sms\Enums\Service;

/**
 * SmsEg class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsEg extends Driver implements SmsGatewayContract
{
    /**
     * Base url.
     *
     * @var string
     */
    protected $base_url = 'https://smssmartegypt.com/sms/api';

    /**
     * Driver name.
     *
     * @var string
     */
    private $driver;

    /**
     * SmsEg Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->service = $config['service'];
        $this->driver = $config['driver'];

        parent::__construct($config['username'], $config['password'], $config['sender_id']);
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
        $msg = 'Message sent successfully';

        if (isset($response['type']) && $response['type'] === 'error') {
            $success = false;
            $msg = Arr::get($response, 'error.msg', 'Message not sent successfully');
        }

        return [
            'success' => $success,
            'message' => $msg,
        ];
    }

    /**
     * send otp verification.
     *
     * @param  string|int  $phone
     * @return int|null
     */
    public function sendOtp($phone)
    {
        $code = null;

        if ($this->service == Service::SMS_OTP_SERVICE) {
            $this->sendOtpRequest($phone);
        }

        if ($this->service == Service::SMS_NORMAL_SERVICE) {
            $code = $this->generateCode();
            $message = $this->getMessage($code);
            $this->send($phone, $message);
        }

        return $code;
    }

    /**
     * Verify phone number.
     *
     * @param  string  $phone
     * @param  int  $otp
     * @param  int|null  $actualOtp
     * @return bool
     *
     * @throws \Exception
     */
    public function verify($phone, $otp, $actualOtp = null): bool
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
    public function balance(): int
    {
        $params = [
            'username' => $this->username,
            'password' => $this->password,
        ];
        $response = Http::get($this->base_url.'/getBalance?'.http_build_query($params));
        $result = $response->json();

        $this->log('debug', "{$this->driver} balance", $result);
        if (isset($result['type']) && $result['type'] === 'error') {
            return 0;
        }

        return Arr::get($result, 'data.points', 0);
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

        $response = Http::get($this->base_url.'/?'.http_build_query($params))->json();
        $this->log('debug', "{$this->driver} send message", $response);

        return $response;
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
        $response = Http::post($this->base_url.'/otp-send', $params)->json();
        $this->log('debug', "{$this->driver} send message", $response);
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
        $response = Http::post($this->base_url.'/otp-check', $params)->json();
        $this->log('debug', "{$this->driver} send message", $response);

        return isset($response['type']) && $response['type'] === 'success';
    }
}
