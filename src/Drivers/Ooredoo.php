<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;

/**
 * Ooredoo class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class Ooredoo extends Driver implements SmsGatewayContract
{
    protected $base_url = 'https://messaging.ooredoo.qa/bms/soap/Messenger.asmx';

    /**
     * Username.
     *
     * @var string
     */
    private $username;
    /**
     * Password.
     *
     * @var string
     */
    private $password;
    /**
     * Sender ID.
     *
     * @var string
     */
    private $sender_id;

    /**
     * VictoryLink Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->base_url = $config['base_url'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->sender_id = $config['sender_id'];
        $this->customer_id = $config['customer_id'];
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

        $success = isset($response['Result']) && $response['Result'] == 'OK';

        return [
            'success' => $success,
            'message' => $success ? 'Message sent successfully' : 'Message not sent successfully'
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

        $code = $this->generateCode();
        if (is_null($message)) {
            $message = 'Your verification code is: '.$code;
        }
        $this->send($phone, $message);

        return $code;
    }

    /**
     * Get balance.
     *
     * @return int
     */
    public function balance()
    {
        $params = [
            "customerID" => $this->customer_id,
            "userName" => $this->username,
            "userPassword" => $this->password,
        ];

        $response = Http::get($this->base_url . '/HTTP_Authenticate2?'.http_build_query($params));

        $response = simplexml_load_string($response->body());
        $arr = json_decode(json_encode((array)$response), true);

        $filtered = collect($arr['Credits']['CreditPair'])->filter(function ($item) {
            return $item['Type'] == 'SMS';
        })->first();

        return abs($filtered['Value'] ?? 0);
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
            'userName' => $this->username,
            'userPassword' => $this->password,
            'originator' => $this->sender_id,
            'customerID' => $this->customer_id,
            'messageType' => $this->getLanguage(),
            'recipientPhone' => $phone,
            'smsText' => $message,
            'defDate' => '',
            'blink' => false,
            'flash' => false,
            'Private' => false,
        ];

        $response = Http::get($this->base_url . '/HTTP_SendSms?'.http_build_query($params));

        $xml = simplexml_load_string($response->body());

        return json_decode(json_encode((array) $xml), true);
    }

    /**
     * Get message language.
     *
     * @return string
     */
    private function getLanguage()
    {
        switch (config('sms.language')) {
            case 'en':
                $locale = 'Latin';
                break;
            case 'ar':
                $locale = 'ArabicWithArabicNumbers';
                break;
            default:
                $locale = 'ArabicWithArabicNumbers';
                break;
        }

        return $locale;
    }
}
