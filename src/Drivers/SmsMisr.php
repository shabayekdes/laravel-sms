<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;
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

        $this->language = $this->getLanguage();
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
     * @return int|null
     */
    public function sendOtp($phone)
    {
        $code = $this->generateCode();

        if ($this->service == Service::SMS_OTP_SERVICE) {
            $this->sendOtpRequest($phone, $code);
        }

        if ($this->service == Service::SMS_NORMAL_SERVICE) {
            $message = $this->getMessage($code);
            $this->send($phone, $message);
        }

        return $code;
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

        $response = Http::post($this->base_url.'/Request?'.http_build_query($params));
        $result = $response->json();

        if (isset($result['code']) && $result['code'] == 'Error') {
            return 0;
        }

        return Arr::get($result, 'balance', 0);
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
            'language' => $this->getLanguage(),
            'sender' => $this->sender_id,
            'Mobile' => $phone,
            'message' => $message,
            'DelayUntil' => null,
        ];

        return Http::post($this->base_url.'/v2', $params)->json();
    }

    /**
     * Get message language.
     *
     * @return int
     */
    private function getLanguage()
    {
        switch ($this->language) {
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
