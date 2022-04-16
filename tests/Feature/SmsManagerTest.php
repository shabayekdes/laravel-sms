<?php

namespace Shabayek\Sms\Tests\Feature;

use InvalidArgumentException;
use Shabayek\Sms\Drivers\SmsEg;
use Shabayek\Sms\Drivers\SmsMisr;
use Shabayek\Sms\Drivers\SmsNull;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;

/**
 * SmsManagerTest class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsManager extends TestCase
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
