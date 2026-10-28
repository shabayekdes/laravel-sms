<?php

namespace Shabayek\Sms\Tests\Feature;

use InvalidArgumentException;
use Shabayek\Sms\Drivers\Ooredoo;
use Shabayek\Sms\Drivers\SmsEg;
use Shabayek\Sms\Drivers\SmsMisr;
use Shabayek\Sms\Drivers\SmsNull;
use Shabayek\Sms\Drivers\VictoryLink;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * SmsManagerFeatureTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsManagerFeatureTest extends TestCase
{
    /** @test */
    public function it_can_sms_manager_instance_of_smseg()
    {
        $this->setConfig('smseg');

        $sms = Sms::driver();

        $this->assertInstanceOf(SmsEg::class, $sms);
    }

    /** @test */
    public function it_can_sms_manager_instance_of_smsmisr()
    {
        $this->setConfig('smsmisr');

        $sms = Sms::driver();

        $this->assertInstanceOf(SmsMisr::class, $sms);
    }

    /** @test */
    public function it_can_sms_manager_instance_of_victorylink()
    {
        $this->setConfig('victorylink');

        $this->assertInstanceOf(VictoryLink::class, Sms::driver());
    }

    /** @test */
    public function it_can_sms_manager_instance_of_ooredoo()
    {
        $this->setConfig('ooredoo');

        $this->assertInstanceOf(Ooredoo::class, Sms::driver());
    }

    /**
     * @test
     *
     * @dataProvider connections
     */
    public function it_creates_drivers_without_dynamic_properties($connection)
    {
        $deprecations = [];

        set_error_handler(function ($level, $message) use (&$deprecations) {
            $deprecations[] = $message;

            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            Sms::driver($connection);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);
    }

    public static function connections(): array
    {
        return [
            'smseg' => ['smseg'],
            'smsmisr' => ['smsmisr'],
            'victorylink' => ['victorylink'],
            'ooredoo' => ['ooredoo'],
            'null' => ['null'],
        ];
    }

    /** @test */
    public function it_can_sms_manager_instance_of_sms_null()
    {
        $sms = Sms::driver();

        $this->assertInstanceOf(SmsNull::class, $sms);
    }

    /** @test */
    public function it_can_sms_manager_throw_exception_with_undefined_driver()
    {
        $this->setConfig('test');

        $this->expectException(InvalidArgumentException::class);

        Sms::driver();
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
