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
            "SMSLang" => 'A',
            'SMSReceiver' => '0123456789',
            'SMSText' => 'test message'
        ];
        config()->set('sms.connections.victorylink.username', $params['UserName']);
        config()->set('sms.connections.victorylink.password', $params['Password']);
        config()->set('sms.connections.victorylink.sender_id', $params['SMSSender']);

        Http::fake([
            'https://smsvas.vlserv.com/KannelSending/service.asmx/SendSMS?'. http_build_query($params) => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">0</int>', 200),
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
            "SMSLang" => 'A',
            'SMSReceiver' => '0123456789',
            'SMSText' => 'test message'
        ];
        config()->set('sms.connections.victorylink.username', $params['UserName']);
        config()->set('sms.connections.victorylink.password', $params['Password']);
        config()->set('sms.connections.victorylink.sender_id', $params['SMSSender']);

        Http::fake([
            'https://smsvas.vlserv.com/KannelSending/service.asmx/SendSMS?'. http_build_query($params) => Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">-1</int>', 200),
        ]);

        $sms = Sms::send($params['SMSReceiver'], $params['SMSText']);

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
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
