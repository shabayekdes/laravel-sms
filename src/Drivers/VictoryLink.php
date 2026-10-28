<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;

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

        $success = ($response[0] ?? null) === '0';
        $message = $success ? 'Message sent successfully' : 'Message not sent successfully';

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

        $response = $this->sendSafely(function () use ($params) {
            return Http::get($this->base_url.'/CheckCredit?'.http_build_query($params));
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
     * @return array
     */
    private function sendMessageRequest($phone, $message)
    {
        $params = [
            'UserName' => $this->username,
            'Password' => $this->password,
            'SMSSender' => $this->sender_id,
            'SMSLang' => $this->getLanguage(),
            'SMSReceiver' => $phone,
            'SMSText' => $message,
        ];

        $response = $this->sendSafely(function () use ($params) {
            return Http::get($this->base_url.'/SendSMS?'.http_build_query($params));
        });

        if (! $response->successful()) {
            return [];
        }

        return $this->parseXml($response->body());
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
     * Get message language.
     *
     * @return string
     */
    private function getLanguage()
    {
        switch ($this->language) {
            case 'en':
                $locale = 'E';
                break;
            case 'ar':
                $locale = 'A';
                break;
            default:
                $locale = 'E';
                break;
        }

        return $locale;
    }
}
