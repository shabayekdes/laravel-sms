<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Every gateway request is sent with the configured timeouts.
 *
 * The stubs record the Guzzle options each request is sent with.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class RequestTimeoutTest extends TestCase
{
    /**
     * Options of the requests sent during the test.
     *
     * @var array
     */
    private $sentOptions = [];

    /**
     * @test
     *
     * @dataProvider operations
     */
    #[Test]
    #[DataProvider('operations')]
    public function it_sends_requests_with_the_default_timeouts($connection, $service, $url, $body, $operation)
    {
        $this->runOperation($connection, $service, $url, $body, $operation);

        $this->assertCount(1, $this->sentOptions);
        $this->assertSame(30, $this->sentOptions[0]['timeout']);
        $this->assertSame(10, $this->sentOptions[0]['connect_timeout']);
    }

    /**
     * @test
     *
     * @dataProvider operations
     */
    #[Test]
    #[DataProvider('operations')]
    public function it_sends_requests_with_the_configured_timeouts($connection, $service, $url, $body, $operation)
    {
        // Values read from the environment arrive as strings.
        config()->set('sms.timeout', '5');
        config()->set('sms.connect_timeout', '2');

        $this->runOperation($connection, $service, $url, $body, $operation);

        $this->assertCount(1, $this->sentOptions);
        $this->assertSame(5, $this->sentOptions[0]['timeout']);
        $this->assertSame(2, $this->sentOptions[0]['connect_timeout']);
    }

    /** @test */
    #[Test]
    public function it_falls_back_to_the_default_timeouts_when_the_keys_are_missing()
    {
        config()->set('sms', array_diff_key(config('sms'), ['timeout' => true, 'connect_timeout' => true]));

        $this->runOperation('smsmisr', 'normal', 'smsmisr.com/api/v2', ['code' => 1901], 'send');

        $this->assertSame(30, $this->sentOptions[0]['timeout']);
        $this->assertSame(10, $this->sentOptions[0]['connect_timeout']);
    }

    public static function operations(): array
    {
        $vlSent = '<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">0</int>';
        $ooSent = '<?xml version="1.0" encoding="utf-8"?><SendResult><Result>OK</Result></SendResult>';
        $ooCredits = '<AuthResult2><Credits><CreditPair><Type>SMS</Type><Value>5</Value></CreditPair><CreditPair><Type>MMS</Type><Value>1</Value></CreditPair></Credits></AuthResult2>';

        return [
            'smseg send' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/?*', ['type' => 'success'], 'send'],
            'smseg normal otp' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/?*', ['type' => 'success'], 'sendOtp'],
            'smseg provider otp' => ['smseg', 'otp', 'smssmartegypt.com/sms/api/otp-send', ['type' => 'success'], 'sendOtp'],
            'smseg provider verify' => ['smseg', 'otp', 'smssmartegypt.com/sms/api/otp-check', ['type' => 'success'], 'verify'],
            'smseg balance' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/getBalance*', ['data' => ['points' => 5]], 'balance'],
            'smsmisr send' => ['smsmisr', 'normal', 'smsmisr.com/api/v2', ['code' => 1901], 'send'],
            'smsmisr normal otp' => ['smsmisr', 'normal', 'smsmisr.com/api/v2', ['code' => 1901], 'sendOtp'],
            'smsmisr otp service' => ['smsmisr', 'otp', 'smsmisr.com/api/vSMS', [], 'sendOtp'],
            'smsmisr balance' => ['smsmisr', 'normal', 'smsmisr.com/api/Request*', ['Balance' => 5], 'balance'],
            'victorylink send' => ['victorylink', null, 'smsvas.vlserv.com/*/SendSMS*', $vlSent, 'send'],
            'victorylink otp' => ['victorylink', null, 'smsvas.vlserv.com/*/SendSMS*', $vlSent, 'sendOtp'],
            'victorylink balance' => ['victorylink', null, 'smsvas.vlserv.com/*/CheckCredit*', '<int xmlns="http://tempuri.org/">5</int>', 'balance'],
            'ooredoo send' => ['ooredoo', null, 'ooredoo.test/Messenger.asmx/HTTP_SendSms*', $ooSent, 'send'],
            'ooredoo otp' => ['ooredoo', null, 'ooredoo.test/Messenger.asmx/HTTP_SendSms*', $ooSent, 'sendOtp'],
            'ooredoo balance' => ['ooredoo', null, 'ooredoo.test/Messenger.asmx/HTTP_Authenticate2*', $ooCredits, 'balance'],
        ];
    }

    /**
     * Run a driver operation against a stub that records the request options.
     *
     * @param  string  $connection
     * @param  string|null  $service
     * @param  string  $url
     * @param  array|string  $body
     * @param  string  $operation
     * @return void
     */
    private function runOperation($connection, $service, $url, $body, $operation): void
    {
        if ($service) {
            config()->set("sms.connections.{$connection}.service", $service);
        }

        $this->stubHttp([
            $url => function (Request $request, array $options) use ($body) {
                $this->sentOptions[] = $options;

                return Http::response($body, 200);
            },
        ]);

        $driver = Sms::driver($connection);

        switch ($operation) {
            case 'send':
                $driver->send('0123456789', 'test message');
                break;
            case 'sendOtp':
                $driver->sendOtp('0123456789');
                break;
            case 'verify':
                $driver->verify('0123456789', '123456');
                break;
            case 'balance':
                $driver->balance();
                break;
        }
    }
}
