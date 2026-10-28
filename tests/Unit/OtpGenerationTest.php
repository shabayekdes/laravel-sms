<?php

namespace Shabayek\Sms\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * Locally generated OTP codes (Driver::generateCode via sendOtp).
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class OtpGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->stubHttp([
            'smsvas.vlserv.com/KannelSending/service.asmx/SendSMS*' => function () {
                return Http::response('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://tempuri.org/">0</int>', 200);
            },
        ]);
    }

    /**
     * @test
     * @dataProvider developmentEnvironments
     */
    public function it_uses_the_development_code_in_local_and_testing($environment)
    {
        $this->app['env'] = $environment;

        $this->assertSame(1234, Sms::driver('victorylink')->sendOtp('0123456789'));
    }

    /**
     * @test
     * @dataProvider nonDevelopmentEnvironments
     */
    public function it_generates_a_six_digit_code_outside_local_and_testing($environment)
    {
        $this->app['env'] = $environment;

        $code = Sms::driver('victorylink')->sendOtp('0123456789');

        $this->assertIsInt($code);
        $this->assertGreaterThanOrEqual(100000, $code);
        $this->assertLessThanOrEqual(999999, $code);
    }

    /**
     * rand() is mt_rand(), so seeding mt_srand() makes it repeat its output.
     * A code from random_int() does not follow the seed. Two draws per run
     * keep the chance of an accidental match around one in 10^11.
     *
     * @test
     */
    public function it_does_not_derive_codes_from_the_seedable_mt_rand_generator()
    {
        $this->app['env'] = 'production';
        $driver = Sms::driver('victorylink');

        mt_srand(42);
        $first = [$driver->sendOtp('0123456789'), $driver->sendOtp('0123456789')];

        mt_srand(42);
        $second = [$driver->sendOtp('0123456789'), $driver->sendOtp('0123456789')];

        mt_srand();

        $this->assertNotSame($first, $second);
    }

    public static function developmentEnvironments(): array
    {
        return [
            'local' => ['local'],
            'testing' => ['testing'],
        ];
    }

    public static function nonDevelopmentEnvironments(): array
    {
        return [
            'production' => ['production'],
            'prod' => ['prod'],
            'staging' => ['staging'],
            'uat' => ['uat'],
            'demo' => ['demo'],
        ];
    }
}
