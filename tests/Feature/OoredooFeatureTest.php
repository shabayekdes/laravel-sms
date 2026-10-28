<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Drivers\Ooredoo;
use Shabayek\Sms\Tests\TestCase;

/**
 * OoredooFeatureTest class.
 *
 * The manager can't resolve Ooredoo yet, so the driver is built directly.
 * The XML fixtures follow the shape the driver parses, not provider docs.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class OoredooFeatureTest extends TestCase
{
    /** @test */
    public function it_returns_the_code_when_the_otp_message_is_accepted()
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response('<?xml version="1.0" encoding="utf-8"?><SendResult><Result>OK</Result></SendResult>', 200),
        ]);

        $this->assertSame(1234, $this->driver()->sendOtp('0123456789'));
    }

    /** @test */
    public function it_returns_null_when_the_otp_message_is_rejected()
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response('<?xml version="1.0" encoding="utf-8"?><SendResult><Result>Failed</Result></SendResult>', 200),
        ]);

        $this->assertNull($this->driver()->sendOtp('0123456789'));
    }

    /**
     * @test
     * @dataProvider unconfirmedResponses
     */
    public function it_does_not_report_success_without_provider_confirmation($body, $status)
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response($body, $status),
        ]);

        $sms = $this->driver()->send('0123456789', 'test message');

        $this->assertFalse($sms['success']);
        $this->assertEquals('Message not sent successfully', $sms['message']);
    }

    /**
     * @test
     * @dataProvider unconfirmedResponses
     */
    public function it_returns_null_when_the_otp_message_is_not_confirmed($body, $status)
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response($body, $status),
        ]);

        $this->assertNull($this->driver()->sendOtp('0123456789'));
    }

    public static function unconfirmedResponses(): array
    {
        return [
            'plain text server error' => ['Service Unavailable', 503],
            'plain text body' => ['Service Unavailable', 200],
            'empty body' => ['', 200],
            'malformed xml' => ['<SendResult><Result>OK</Result>', 200],
            'html body' => ['<html><body>oops</body></html>', 200],
            'server error with success body' => ['<?xml version="1.0" encoding="utf-8"?><SendResult><Result>OK</Result></SendResult>', 500],
        ];
    }

    /**
     * Build the Ooredoo driver from the test configuration.
     *
     * @return \Shabayek\Sms\Drivers\Ooredoo
     */
    private function driver(): Ooredoo
    {
        return new Ooredoo(config('sms.connections.ooredoo'));
    }
}
