<?php

namespace Shabayek\Sms\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * SMS_* variables set before the application boots must not change the
 * configuration tests run with.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class EnvironmentIsolationFeatureTest extends TestCase
{
    private const ENVIRONMENT = [
        'SMS_CONNECTION' => 'smseg',
        'SMS_LANGUAGE' => 'en',
        'SMS_MESSAGE' => 'Env message {code}',
        'SMS_USERNAME' => 'env-user',
        'SMS_PASSWORD' => 'env-password',
        'SMS_SENDER_ID' => 'EnvSender',
        'SMS_SERVICE' => 'otp',
        'SMS_TIMEOUT' => '1',
        'SMS_CONNECT_TIMEOUT' => '1',
    ];

    protected function setUp(): void
    {
        foreach (self::ENVIRONMENT as $name => $value) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $_SERVER[$name] = $value;
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (array_keys(self::ENVIRONMENT) as $name) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }

    /** @test */
    public function it_ignores_sms_environment_variables()
    {
        $this->assertSame('null', config('sms.default'));
        $this->assertSame('Your verification code is: {code}', config('sms.message'));
        $this->assertSame(self::TEST_USERNAME, config('sms.connections.smseg.username'));
        $this->assertSame('normal', config('sms.connections.smseg.service'));
        $this->assertSame(30, config('sms.timeout'));
        $this->assertSame(10, config('sms.connect_timeout'));
    }

    /** @test */
    public function it_sends_with_the_pinned_test_configuration()
    {
        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => Http::response(['type' => 'success'], 200),
        ]);

        $this->assertSame(1234, Sms::driver('smseg')->sendOtp('0123456789'));

        Http::assertSent(function (Request $request) {
            return $request['username'] === self::TEST_USERNAME
                && $request['message'] === 'Your verification code is: 1234';
        });
    }
}
