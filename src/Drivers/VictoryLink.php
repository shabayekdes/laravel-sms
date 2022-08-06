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

        $success = $response[0] === '0';
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
        $this->send($phone, $message);

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

        $response = Http::get($this->base_url.'/CheckCredit?'.http_build_query($params));

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

        $response = Http::get($this->base_url.'/SendSMS?'.http_build_query($params));

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
