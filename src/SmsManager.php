<?php

namespace Shabayek\Sms;

use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Shabayek\Sms\Drivers\SmsEg;
use Shabayek\Sms\Drivers\SmsMisr;
use Shabayek\Sms\Drivers\SmsNull;
use Shabayek\Sms\Drivers\VictoryLink;

/**
 * SMS Manager class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsManager extends Manager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new sms manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Create a new driver instance.
     *
     * @param  string  $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        $config = $this->getConfig($driver);

        if (is_null($config)) {
            throw new InvalidArgumentException("Cache store [{$driver}] is not defined.");
        }

        $method = 'create'.Str::studly($config['driver']).'Driver';

        if (method_exists($this, $method)) {
            return $this->$method($config);
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * Get the cache connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app['config']["sms.connections.{$name}"];
        }

        return ['driver' => 'null'];
    }

    /**
     * Create an instance of the sms eg driver.
     *
     * @param  array  $config
     * @return \Shabayek\Sms\Drivers\SmsEg
     */
    protected function createSmsegDriver(array $config): SmsEg
    {
        return new SmsEg($config);
    }

    /**
     * Create an instance of the sms misr driver.
     *
     * @param  array  $config
     * @return \Shabayek\Sms\Drivers\SmsMisr
     */
    protected function createSmsmisrDriver(array $config): SmsMisr
    {
        return new SmsMisr($config);
    }

    /**
     * Create an instance of the sms victory link driver.
     *
     * @param  array  $config
     * @return \Shabayek\Sms\Drivers\VictoryLink
     */
    protected function createVictorylinkDriver(array $config): VictoryLink
    {
        return new VictoryLink($config);
    }

    /**
     * Create an instance of the Null sms driver.
     *
     * @param  array  $config
     * @return \Shabayek\Sms\Drivers\SmsNull
     */
    protected function createNullDriver(array $config): SmsNull
    {
        return new SmsNull($config);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['sms.default'];
    }
}
