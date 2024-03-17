<?php

namespace Shabayek\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Contracts\SmsGatewayContract;

/**
 * Unifonic class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class Unifonic extends Driver implements SmsGatewayContract
{
    protected $base_url = 'https://api.unifonic.com';

    private $apps_id;

    /**
     * VictoryLink Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        parent::__construct($config['username'], $config['password'], $config['sender_id']);
        $this->apps_id = $config['apps_id'];
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

        $success = $response['success'] == 'true';
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
        $code = null;

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
    public function balance()
    {
        // Not Implement Yet
        return 0;
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
            'AppSid' => $this->apps_id,
            'SenderID' => $this->sender_id,
            'Password' => $this->password,
            'Recipient' => $phone,
            'Body' => $message,
            'responseType' => 'JSON',
        ];

        $response = Http::withBasicAuth($this->username, $this->password)
                        ->post($this->base_url.'/rest/SMS/messages', $params);

        return $response->json();
    }
}
