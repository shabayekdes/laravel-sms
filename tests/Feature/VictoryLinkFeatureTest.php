<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * VictoryLinkFeatureTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class VictoryLinkFeatureTest extends TestCase
{
    /** @test */
    public function it_can_victory_link_send_sms_message_successfully()
    {
        $this->setConfig('victorylink');
        $params = [
            'UserName' => 'test',
            'Password' => 'secret',
            'SMSSender' => 'test',
            'SMSLang' => 'E',
            'SMSReceiver' => '0123456789',
            'SMSText' => 'test message',
        ];
        config()->set('sms.connections.victorylink.username', $params['UserName']);
        config()->set('sms.connections.victorylink.password', $params['Password']);
        config()->set('sms.connections.victorylink.sender_id', $params['SMSSender']);

        $this->stubHttp([
            'https://smsvas.vlserv.com/KannelSending/service.asmx/SendSMS?'.http_build_query($params) => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">0</int>', 200),
        ]);

        $sms = Sms::send($params['SMSReceiver'], $params['SMSText']);

        $this->assertTrue($sms['success']);
        $this->assertEquals('Message sent successfully', $sms['message']);
    }

    /** @test */
    public function test_victory_link_not_sent_message()
    {
        $this->setConfig('victorylink');
        $params = [
            'UserName' => 'test',
            'Password' => 'secret',
            'SMSSender' => 'test',
            'SMSLang' => 'E',
            'SMSReceiver' => '0123456789',
            'SMSText' => 'test message',
        ];
        config()->set('sms.connections.victorylink.username', $params['UserName']);
        config()->set('sms.connections.victorylink.password', $params['Password']);
        config()->set('sms.connections.victorylink.sender_id', $params['SMSSender']);

        $this->stubHttp([
            'https://smsvas.vlserv.com/KannelSending/service.asmx/SendSMS?'.http_build_query($params) => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">-1</int>', 200),
        ]);

        $sms = Sms::send($params['SMSReceiver'], $params['SMSText']);

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /** @test */
    public function it_returns_the_code_when_the_otp_message_is_accepted()
    {
        $this->setConfig('victorylink');

        $this->stubHttp([
            'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*' => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">0</int>', 200),
        ]);

        $this->assertSame(1234, Sms::sendOtp('0123456789'));
    }

    /** @test */
    public function it_returns_null_when_the_otp_message_is_rejected()
    {
        $this->setConfig('victorylink');

        $this->stubHttp([
            'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*' => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">-1</int>', 200),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));
    }

    /**
     * @test
     *
     * @dataProvider unconfirmedResponses
     */
    public function it_does_not_report_success_without_provider_confirmation($body, $status)
    {
        $this->setConfig('victorylink');

        $this->stubHttp([
            'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*' => Http::response($body, $status),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /**
     * @test
     *
     * @dataProvider unconfirmedResponses
     */
    public function it_returns_null_when_the_otp_message_is_not_confirmed($body, $status)
    {
        $this->setConfig('victorylink');

        $this->stubHttp([
            'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*' => Http::response($body, $status),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));
    }

    public static function unconfirmedResponses(): array
    {
        return [
            'plain text server error' => ['Service Unavailable', 503],
            'plain text body' => ['Service Unavailable', 200],
            'empty body' => ['', 200],
            'malformed xml' => ['<int xmlns="http://tempuri.org/">0', 200],
            'html body' => ['<html><body>oops</body></html>', 200],
            'server error with success body' => ['<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">0</int>', 500],
        ];
    }

    /**
     * Set config.
     *
     * @param  string  $config
     * @return void
     */
    private function setConfig(string $config)
    {
        config()->set('sms.default', $config);
    }
}
