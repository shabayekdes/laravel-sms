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

    /**
     * A config published by an older version can lack keys added later.
     *
     * @test
     *
     * @dataProvider connections
     */
    public function it_creates_drivers_from_a_connection_without_optional_keys($connection)
    {
        config()->set("sms.connections.{$connection}", ['driver' => $connection]);

        $this->assertNotNull(Sms::driver($connection));
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
        $this->expectExceptionMessage('SMS connection [test] is not defined.');

        Sms::driver();
    }

    /** @test */
    public function it_throws_for_a_connection_with_an_unsupported_driver()
    {
        config()->set('sms.connections.broken', ['driver' => 'unknown']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [broken] not supported.');

        Sms::driver('broken');
    }

    /** @test */
    public function it_passes_the_application_and_connection_config_to_custom_creators()
    {
        $config = ['driver' => 'custom', 'username' => 'custom-user'];
        config()->set('sms.connections.custom', $config);

        Sms::extend('custom', function ($app, $connection) {
            return (object) ['app' => $app, 'config' => $connection];
        });

        $driver = Sms::driver('custom');

        $this->assertSame($this->app, $driver->app);
        $this->assertSame($config, $driver->config);
    }

    /** @test */
    public function it_still_calls_custom_creators_without_parameters()
    {
        config()->set('sms.connections.custom', ['driver' => 'custom']);

        Sms::extend('custom', function () {
            return new SmsNull([]);
        });

        $this->assertInstanceOf(SmsNull::class, Sms::driver('custom'));
    }

    /** @test */
    public function it_finds_custom_creators_by_driver_name()
    {
        config()->set('sms.connections.primary', ['driver' => 'custom', 'sender_id' => 'Primary']);
        config()->set('sms.connections.backup', ['driver' => 'custom', 'sender_id' => 'Backup']);

        Sms::extend('custom', function ($app, $connection) {
            return (object) ['sender' => $connection['sender_id']];
        });

        $this->assertSame('Primary', Sms::driver('primary')->sender);
        $this->assertSame('Backup', Sms::driver('backup')->sender);
    }

    /** @test */
    public function it_prefers_the_creator_registered_for_the_connection_name()
    {
        config()->set('sms.connections.primary', ['driver' => 'custom']);

        Sms::extend('custom', function () {
            return (object) ['creator' => 'driver'];
        });
        Sms::extend('primary', function () {
            return (object) ['creator' => 'connection'];
        });

        $this->assertSame('connection', Sms::driver('primary')->creator);
    }

    /** @test */
    public function it_lets_a_custom_creator_replace_a_built_in_driver()
    {
        Sms::extend('smseg', function () {
            return new SmsNull([]);
        });

        $this->assertInstanceOf(SmsNull::class, Sms::driver('smseg'));
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
