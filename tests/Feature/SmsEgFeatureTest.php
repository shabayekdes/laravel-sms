<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * SmsEgFeatureTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsEgFeatureTest extends TestCase
{
    /** @test */
    public function it_can_smseg_send_sms_message_successfully()
    {
        $this->setConfig('smseg');

        Http::fake([
            'https://smssmartegypt.com/sms/api' => Http::response(
                [
                    'type' => 'success',
                ], 200),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertTrue($sms['success']);
        $this->assertEquals('Message sent successfully', $sms['message']);
    }

    /** @test */
    public function test_smseg_not_sent_message()
    {
        $this->setConfig('smseg');

        Http::fake([
            'https://smssmartegypt.com/sms/api' => Http::response(
                [
                    'type' => 'error',
                ], 200),
        ]);

        $sms = Sms::send('0123456789', 'test message');

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
