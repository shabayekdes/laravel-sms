<?php

namespace Shabayek\Sms\Drivers;

use Shabayek\Sms\Contracts\SmsGatewayContract;
use Shabayek\Sms\Enums\ResponseCode;

/**
 * SMS Null class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsNull extends Driver implements SmsGatewayContract
{
    /**
     * Connection config.
     *
     * @var array
     */
    protected $config;

    /**
     * SmsNull Constructor.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
        return $this->response(ResponseCode::Disabled);
    }

    /**
     * send otp verification.
     *
     * @param  string  $phone
     * @return int|null
     */
    public function sendOtp($phone)
    {
        return null;
    }

    /**
     * Get balance.
     *
     * @return int
     */
    public function balance()
    {
        return 0;
    }
}
