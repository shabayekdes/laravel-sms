<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Shabayek\Sms\Contracts\SmsGatewayContract;
use Shabayek\Sms\Enums\ResponseCode;

/**
 * Ooredoo class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class Ooredoo extends Driver implements SmsGatewayContract
{
    protected $base_url = 'https://messaging.ooredoo.qa/bms/soap/Messenger.asmx';

    /**
     * Customer ID.
     *
     * @var string
     */
    protected $customer_id;

    /**
     * Ooredoo Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->base_url = $config['base_url'] ?? $this->base_url;
        $this->customer_id = $config['customer_id'] ?? null;

        parent::__construct($config['username'] ?? null, $config['password'] ?? null, $config['sender_id'] ?? null);
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

        if (! $response->successful()) {
            return $this->response(ResponseCode::HttpError);
        }

        $result = $this->parseXml($response->body())['Result'] ?? null;

        if (! is_string($result)) {
            return $this->response(ResponseCode::InvalidResponse);
        }

        if ($result !== 'OK') {
            return $this->response(ResponseCode::Rejected, $result);
        }

        return $this->response(ResponseCode::Sent, $result);
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
        $message = $this->getMessage($code);

        if (! $this->send($phone, $message)['success']) {
            return null;
        }

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
            'customerID' => $this->customer_id,
            'userName' => $this->username,
            'userPassword' => $this->password,
        ];

        $response = $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->get($this->base_url.'/HTTP_Authenticate2?'.http_build_query($params));
        });

        $response = simplexml_load_string($response->body());
        $arr = json_decode(json_encode((array) $response), true);

        $pairs = $arr['Credits']['CreditPair'];

        // A single CreditPair element decodes to the pair itself, not a list.
        if (isset($pairs['Type'])) {
            $pairs = [$pairs];
        }

        $filtered = collect($pairs)->filter(function ($item) {
            return $item['Type'] == 'SMS';
        })->first();

        return abs($filtered['Value'] ?? 0);
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
            'userName' => $this->username,
            'userPassword' => $this->password,
            'originator' => $this->sender_id,
            'customerID' => $this->customer_id,
            'messageType' => $this->getLanguage($message),
            'recipientPhone' => $phone,
            'smsText' => $message,
            'defDate' => '',
            // Literal strings, since http_build_query() turns false into 0.
            'blink' => 'false',
            'flash' => 'false',
            'Private' => 'false',
        ];

        return $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->get($this->base_url.'/HTTP_SendSms?'.http_build_query($params));
        });
    }

    /**
     * Parse an xml response body.
     *
     * Bodies that are not well-formed xml give an empty array instead of
     * a PHP warning, which Laravel would turn into an ErrorException.
     *
     * @param  string  $body
     * @return array
     */
    private function parseXml($body): array
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            return [];
        }

        return json_decode(json_encode((array) $xml), true);
    }

    /**
     * Get message type from its text.
     *
     * @param  string  $message
     * @return string
     */
    private function getLanguage($message)
    {
        return $this->isUnicode($message) ? 'ArabicWithArabicNumbers' : 'Latin';
    }
}
