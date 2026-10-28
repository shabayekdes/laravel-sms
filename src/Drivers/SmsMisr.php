<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Shabayek\Sms\Contracts\SmsGatewayContract;
use Shabayek\Sms\Enums\ResponseCode;
use Shabayek\Sms\Enums\Service;

class SmsMisr extends Driver implements SmsGatewayContract
{
    protected $base_url = 'https://smsmisr.com/api';

    /**
     * Msignature.
     *
     * @var string
     */
    private $msignature;
    /**
     * Sms ID.
     *
     * @var string
     */
    private $sms_id;
    /**
     * Token.
     *
     * @var string
     */
    private $token;

    /**
     * SmsMisr Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->service = $config['service'];

        $this->token = $config['token'];
        $this->msignature = $config['msignature'];
        $this->sms_id = $config['sms_id'];

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
        $code = Arr::get($response->json(), 'code');
        $providerCode = is_scalar($code) ? (string) $code : null;

        if (! $response->successful()) {
            return $this->response(ResponseCode::HttpError, $providerCode);
        }

        if ($providerCode === null) {
            return $this->response(ResponseCode::InvalidResponse);
        }

        if ($providerCode !== '1901') {
            return $this->response(ResponseCode::Rejected, $providerCode);
        }

        return $this->response(ResponseCode::Sent, $providerCode);
    }

    /**
     * send otp verification.
     *
     * @param  string|int  $phone
     * @return int|null
     */
    public function sendOtp($phone)
    {
        $code = $this->generateCode();
        $sent = false;

        if ($this->service == Service::SMS_OTP_SERVICE) {
            $sent = $this->sendOtpRequest($phone, $code);
        }

        if ($this->service == Service::SMS_NORMAL_SERVICE) {
            $message = $this->getMessage($code);
            $sent = $this->send($phone, $message)['success'];
        }

        return $sent ? $code : null;
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
            'SMSID' => $this->sms_id,
            'request' => 'status',
        ];

        $response = $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->post($this->base_url.'/Request?'.http_build_query($params));
        });
        $result = $response->json();

        if (isset($result['code']) && $result['code'] == 'Error') {
            return 0;
        }

        return Arr::get($result, 'Balance', 0);
    }

    /**
     * Send sms with otp services.
     *
     * Only the HTTP status is checked: the vSMS response format is not
     * documented in this package, so any 2xx response counts as accepted.
     *
     * @param  string  $phone
     * @param  int  $code
     * @return bool
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

        return $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->post($this->base_url.'/vSMS', $params);
        })->successful();
    }

    /**
     * Send sms with message services.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return \Illuminate\Http\Client\Response
     */
    private function sendMessageRequest($phone, $message)
    {
        $params = [
            'Username' => $this->username,
            'password' => $this->password,
            'language' => $this->getLanguage($message),
            'sender' => $this->sender_id,
            'Mobile' => $phone,
            'message' => $message,
            'DelayUntil' => null,
        ];

        return $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->post($this->base_url.'/v2', $params);
        });
    }

    /**
     * Get message language from its text.
     *
     * 1 is English, 2 is Arabic and 3 is Unicode.
     *
     * @param  string  $message
     * @return int
     */
    private function getLanguage($message)
    {
        if (! $this->isUnicode($message)) {
            return 1;
        }

        return $this->isArabic($message) ? 2 : 3;
    }
}
