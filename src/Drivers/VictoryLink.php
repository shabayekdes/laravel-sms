<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Shabayek\Sms\Contracts\SmsGatewayContract;
use Shabayek\Sms\Enums\ResponseCode;

/**
 * VictoryLink class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class VictoryLink extends Driver implements SmsGatewayContract
{
    protected $base_url = 'https://smsvas.vlserv.com/KannelSending/service.asmx';

    /**
     * VictoryLink Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
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

        $result = $this->parseXml($response->body())[0] ?? null;

        if (! is_string($result)) {
            return $this->response(ResponseCode::InvalidResponse);
        }

        if ($result !== '0') {
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
    public function balance(): int
    {
        $params = [
            'UserName' => $this->username,
            'Password' => $this->password,
        ];

        $response = $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->get($this->base_url.'/CheckCredit?'.http_build_query($params));
        });

        $xml = simplexml_load_string($response->body());
        $result = json_decode(json_encode((array) $xml), true);

        return $result[0] ?? 0;
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
            'UserName' => $this->username,
            'Password' => $this->password,
            'SMSSender' => $this->sender_id,
            'SMSLang' => $this->getLanguage($message),
            'SMSReceiver' => $phone,
            'SMSText' => $message,
        ];

        return $this->sendSafely(function (PendingRequest $http) use ($params) {
            return $http->get($this->base_url.'/SendSMS?'.http_build_query($params));
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
     * Get message language from its text.
     *
     * @param  string  $message
     * @return string
     */
    private function getLanguage($message)
    {
        return $this->isUnicode($message) ? 'A' : 'E';
    }
}
