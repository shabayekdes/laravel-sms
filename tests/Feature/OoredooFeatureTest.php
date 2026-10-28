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
     * Build the Ooredoo driver from the test configuration.
     *
     * @return \Shabayek\Sms\Drivers\Ooredoo
     */
    private function driver(): Ooredoo
    {
        return new Ooredoo(config('sms.connections.ooredoo'));
    }
}
