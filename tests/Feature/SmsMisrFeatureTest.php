<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * SmsMisrFeatureTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsMisrFeatureTest extends TestCase
{
    /** @test */
    public function it_can_smsmisr_send_sms_message_successfully()
    {
        $this->setConfig('smsmisr');

        Http::fake([
            'https://smsmisr.com/api/v2' => Http::response(
                [
                    'code' => 1901,
                ], 200),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertTrue($sms['success']);
        $this->assertEquals('Message sent successfully', $sms['message']);
    }

    /** @test */
    public function test_smsmisr_not_sent_message()
    {
        $this->setConfig('smsmisr');

        Http::fake([
            'https://smsmisr.com/api/v2' => Http::response(
                [
                    'code' => 1902,
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
