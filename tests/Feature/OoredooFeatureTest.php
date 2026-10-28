<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Drivers\Ooredoo;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * OoredooFeatureTest class.
 *
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
     *
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
     *
     * @dataProvider unconfirmedResponses
     */
    public function it_returns_null_when_the_otp_message_is_not_confirmed($body, $status)
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response($body, $status),
        ]);

        $this->assertNull($this->driver()->sendOtp('0123456789'));
    }

    /** @test */
    public function it_sends_boolean_flags_as_literal_strings()
    {
        $this->stubHttp([
            'ooredoo.test/Messenger.asmx/HTTP_SendSms*' => Http::response('<?xml version="1.0" encoding="utf-8"?><SendResult><Result>OK</Result></SendResult>', 200),
        ]);

        $this->driver()->send('0123456789', 'test message');

        Http::assertSent(function (Request $request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query['blink'] === 'false'
                && $query['flash'] === 'false'
                && $query['Private'] === 'false';
        });
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
     * Resolve the Ooredoo driver through the manager.
     *
     * @return \Shabayek\Sms\Drivers\Ooredoo
     */
    private function driver(): Ooredoo
    {
        return Sms::driver('ooredoo');
    }
}
