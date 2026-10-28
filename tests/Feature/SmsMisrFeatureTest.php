<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Http\Client\Request;
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

        $this->stubHttp([
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

        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response(
                [
                    'code' => 1902,
                ], 200),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /** @test */
    public function it_accepts_the_success_code_as_a_string()
    {
        $this->setConfig('smsmisr');

        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response(['code' => '1901'], 200),
        ]);

        $this->assertTrue(Sms::send('0123456789', 'test message')['success']);
    }

    /**
     * @test
     * @dataProvider unconfirmedResponses
     */
    public function it_does_not_report_success_without_provider_confirmation($body, $status)
    {
        $this->setConfig('smsmisr');

        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response($body, $status),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /** @test */
    public function it_returns_the_code_when_the_otp_message_is_accepted()
    {
        $this->setConfig('smsmisr');

        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response(['code' => 1901], 200),
        ]);

        $this->assertSame(1234, Sms::sendOtp('0123456789'));
    }

    /** @test */
    public function it_returns_null_when_the_otp_message_is_rejected()
    {
        $this->setConfig('smsmisr');

        $this->stubHttp([
            'https://smsmisr.com/api/v2' => Http::response(['code' => 1902], 200),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));
    }

    /**
     * The vSMS response body is not interpreted because its format is not
     * verified. This covers transport-level behavior only: a 2xx response
     * returns the code, which does not prove the provider accepted it.
     *
     * @test
     */
    public function it_sends_the_generated_code_with_the_otp_service()
    {
        $this->setConfig('smsmisr');
        config()->set('sms.connections.smsmisr.service', 'otp');

        $this->stubHttp([
            'https://smsmisr.com/api/vSMS' => Http::response([], 200),
        ]);

        $this->assertSame(1234, Sms::sendOtp('0123456789'));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://smsmisr.com/api/vSMS'
                && $request['Mobile'] === '0123456789'
                && $request['Code'] === 1234;
        });
    }

    /** @test */
    public function it_returns_null_when_the_otp_service_request_fails()
    {
        $this->setConfig('smsmisr');
        config()->set('sms.connections.smsmisr.service', 'otp');

        $this->stubHttp([
            'https://smsmisr.com/api/vSMS' => Http::response('<html>Server Error</html>', 500),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));
    }

    /** @test */
    public function it_returns_null_and_sends_nothing_for_an_unknown_service()
    {
        $this->setConfig('smsmisr');
        config()->set('sms.connections.smsmisr.service', 'OTP');

        $this->assertNull(Sms::sendOtp('0123456789'));

        Http::assertNothingSent();
    }

    public static function unconfirmedResponses(): array
    {
        return [
            'empty json object' => [[], 200],
            'unexpected structure' => [['status' => 'failed'], 200],
            'malformed json' => ['{"code": 19', 200],
            'html body' => ['<html>oops</html>', 200],
            'empty body' => ['', 200],
            'server error page' => ['<html>Server Error</html>', 500],
            'server error with success code' => [['code' => 1901], 503],
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
