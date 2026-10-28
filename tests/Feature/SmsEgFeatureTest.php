<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Http\Client\Request;
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

        $phone = '0123456789';
        $message = 'test message';

        $params = [
            'username' => self::TEST_USERNAME,
            'password' => self::TEST_PASSWORD,
            'sendername' => self::TEST_SENDER_ID,
            'mobiles' => $phone,
            'message' => $message,
        ];

        $this->stubHttp([
            'https://smssmartegypt.com/sms/api/?'.http_build_query($params) => Http::response(
                [
                    'type' => 'success',
                ], 200),
        ]);

        $sms = Sms::send($phone, 'test message');

        $this->assertTrue($sms['success']);
        $this->assertEquals('Message sent successfully', $sms['message']);
    }

    /** @test */
    public function test_smseg_not_sent_message()
    {
        $this->setConfig('smseg');

        $phone = '0123456789';
        $message = 'test message';

        $params = [
            'username' => self::TEST_USERNAME,
            'password' => self::TEST_PASSWORD,
            'sendername' => self::TEST_SENDER_ID,
            'mobiles' => $phone,
            'message' => $message,
        ];

        $this->stubHttp([
            'https://smssmartegypt.com/sms/api/?'.http_build_query($params) => Http::response(
                [
                    'type' => 'error',
                ], 200),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /** @test */
    public function it_returns_the_smseg_error_message_on_rejection()
    {
        $this->setConfig('smseg');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response(['type' => 'error', 'error' => ['msg' => 'Invalid sender']], 200),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Invalid sender', $sms['message']);
    }

    /**
     * @test
     * @dataProvider unconfirmedResponses
     */
    public function it_does_not_report_success_without_provider_confirmation($body, $status)
    {
        $this->setConfig('smseg');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response($body, $status),
        ]);

        $sms = Sms::send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /** @test */
    public function it_returns_the_code_when_the_otp_message_is_accepted()
    {
        $this->setConfig('smseg');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response(['type' => 'success'], 200),
        ]);

        $this->assertSame(1234, Sms::sendOtp('0123456789'));

        Http::assertSent(function (Request $request) {
            return $request['message'] === 'كود التحقق الخاص بك هو: 1234';
        });
    }

    /** @test */
    public function it_returns_null_when_the_otp_message_is_rejected()
    {
        $this->setConfig('smseg');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response(['type' => 'error'], 200),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));
    }

    /** @test */
    public function it_returns_null_when_the_otp_message_gets_a_server_error()
    {
        $this->setConfig('smseg');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response('<html>Server Error</html>', 500),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));
    }

    /** @test */
    public function it_sends_a_provider_managed_otp_with_the_otp_service()
    {
        $this->setConfig('smseg');
        config()->set('sms.connections.smseg.service', 'otp');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/otp-send' => Http::response(['type' => 'success'], 200),
        ]);

        $this->assertNull(Sms::sendOtp('0123456789'));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://smssmartegypt.com/sms/api/otp-send'
                && $request['mobile'] === '0123456789';
        });
    }

    /** @test */
    public function it_verifies_a_provider_managed_otp()
    {
        $this->setConfig('smseg');
        config()->set('sms.connections.smseg.service', 'otp');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/otp-check' => Http::response(['type' => 'success'], 200),
        ]);

        $this->assertTrue(Sms::verify('0123456789', '123456'));
    }

    /** @test */
    public function it_rejects_a_provider_managed_otp_the_provider_rejects()
    {
        $this->setConfig('smseg');
        config()->set('sms.connections.smseg.service', 'otp');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/otp-check' => Http::response(['type' => 'error'], 200),
        ]);

        $this->assertFalse(Sms::verify('0123456789', '123456'));
    }

    /** @test */
    public function it_does_not_send_a_malformed_otp_to_the_provider()
    {
        $this->setConfig('smseg');
        config()->set('sms.connections.smseg.service', 'otp');

        $this->stubHttp([
            'smssmartegypt.com/sms/api/otp-check' => Http::response(['type' => 'success'], 200),
        ]);

        $this->assertFalse(Sms::verify('0123456789', true));

        Http::assertNothingSent();
    }

    public static function unconfirmedResponses(): array
    {
        return [
            'empty json object' => [[], 200],
            'unexpected structure' => [['status' => 'failed'], 200],
            'malformed json' => ['{"type": "succ', 200],
            'html body' => ['<html>oops</html>', 200],
            'empty body' => ['', 200],
            'server error page' => ['<html>Server Error</html>', 500],
            'server error with success body' => [['type' => 'success'], 503],
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
