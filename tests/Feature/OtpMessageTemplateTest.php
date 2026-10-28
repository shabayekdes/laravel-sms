<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * OtpMessageTemplateTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class OtpMessageTemplateTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider connections
     */
    #[Test]
    #[DataProvider('connections')]
    public function it_sends_nothing_when_the_template_has_no_code_placeholder($connection)
    {
        config()->set('sms.message', 'Your verification code is ready');

        try {
            Sms::driver($connection)->sendOtp('0123456789');
            $this->fail('Expected an InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('The sms.message template must contain the {code} placeholder.', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public static function connections(): array
    {
        return [
            'smseg' => ['smseg'],
            'smsmisr' => ['smsmisr'],
            'victorylink' => ['victorylink'],
            'ooredoo' => ['ooredoo'],
        ];
    }
}
