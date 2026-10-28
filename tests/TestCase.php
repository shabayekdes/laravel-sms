<?php

namespace Shabayek\Sms\Tests;

use Closure;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use RuntimeException;
use Shabayek\Sms\Providers\SmsServiceProvider;

/**
 * TestCase class.
 *
 * @author Esmail Shabayek
 */
abstract class TestCase extends Orchestra
{
    /**
     * Fake credentials used for every gateway connection in tests.
     */
    protected const TEST_USERNAME = 'test-user';
    protected const TEST_PASSWORD = 'test-secret';
    protected const TEST_SENDER_ID = 'TestSender';

    /**
     * Registered HTTP stubs keyed by url pattern.
     *
     * @var array
     */
    private $httpStubs = [];

    /**
     * Setup the test environment.
     *
     * Every outgoing HTTP request is routed through a single fake. A request
     * without a matching stub throws instead of reaching a real SMS gateway.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpStubs = [];

        Http::fake(function (Request $request, array $options) {
            return $this->resolveHttpStub($request, $options);
        });
    }

    /**
     * Get package service providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            SmsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * Every value read from an SMS_* variable is set explicitly, so tests never
     * depend on the developer's shell or the CI environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('sms.default', 'null');
        $app['config']->set('sms.language', 'ar');
        $app['config']->set('sms.message.ar', 'كود التحقق الخاص بك هو: {code}');
        $app['config']->set('sms.message.en', 'Your verification code is: {code}');
        $app['config']->set('sms.timeout', 30);
        $app['config']->set('sms.connect_timeout', 10);

        foreach (['smseg', 'smsmisr', 'victorylink', 'ooredoo'] as $connection) {
            $app['config']->set("sms.connections.{$connection}.username", self::TEST_USERNAME);
            $app['config']->set("sms.connections.{$connection}.password", self::TEST_PASSWORD);
            $app['config']->set("sms.connections.{$connection}.sender_id", self::TEST_SENDER_ID);
        }

        $app['config']->set('sms.connections.smseg.service', 'normal');
        $app['config']->set('sms.connections.smsmisr.service', 'normal');
        $app['config']->set('sms.connections.smsmisr.token', 'test-token');
        $app['config']->set('sms.connections.smsmisr.msignature', 'test-msignature');
        $app['config']->set('sms.connections.smsmisr.sms_id', 'test-sms-id');
        $app['config']->set('sms.connections.ooredoo.base_url', 'https://ooredoo.test/Messenger.asmx');
        $app['config']->set('sms.connections.ooredoo.customer_id', 'test-customer');
    }

    /**
     * Register fake responses for url patterns ("*" is a wildcard).
     *
     * A closure stub receives the request and the Guzzle request options.
     *
     * @param  array  $stubs
     * @return void
     */
    protected function stubHttp(array $stubs): void
    {
        foreach ($stubs as $url => $response) {
            $this->httpStubs[$url] = $response;
        }
    }

    /**
     * Resolve the fake response for a request or fail the test.
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @param  array  $options
     * @return mixed
     *
     * @throws \RuntimeException
     */
    private function resolveHttpStub(Request $request, array $options)
    {
        foreach ($this->httpStubs as $url => $response) {
            if (Str::is(Str::start($url, '*'), $request->url())) {
                return $response instanceof Closure ? $response($request, $options) : $response;
            }
        }

        $target = parse_url($request->url(), PHP_URL_HOST).parse_url($request->url(), PHP_URL_PATH);

        throw new RuntimeException("Unexpected HTTP request in tests: {$request->method()} {$target}");
    }
}
