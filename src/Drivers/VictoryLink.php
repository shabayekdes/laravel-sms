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
    /**
     * Base url.
     *
     * @var string
     */
    protected $base_url = 'https://smsvas.vlserv.com/KannelSending/service.asmx';
    /**
     * Driver name.
     *
     * @var string
     */
    private $driver;

    /**
     * VictoryLink Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
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

        $this->log('debug', "{$this->driver} balance", $result);

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

        $result = json_decode(json_encode((array) $xml), true);
        $this->log('debug', "{$this->driver} send message", $result);

        return $result;
    }

    /**
     * Get message language.
     *
     * @return string
     */
    private function getLanguage()
    {
        if ($this->language == 'ar') {
            $locale = 'A';
        } else {
            $locale = 'E';
        }

        return $locale;
    }
}
