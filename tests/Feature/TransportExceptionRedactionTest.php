<?php

namespace Shabayek\Sms\Tests\Feature;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use RuntimeException;
use Shabayek\Sms\Drivers\Ooredoo;
use Shabayek\Sms\Facade\Sms;
use Shabayek\Sms\Tests\TestCase;
use Throwable;

/**
 * Transport failures must not expose request details.
 *
 * Guzzle ends cURL error messages with the full request url, which holds
 * credentials, the phone number and the message text. The stubs below build
 * the same kind of message, plus the request body, and throw it from the
 * HTTP layer so it goes through the Laravel client's own conversion.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class TransportExceptionRedactionTest extends TestCase
{
    private const PHONE = '0109876543';
    private const MESSAGE = 'Private message text';
    private const OTP = '654321';
    private const GENERATED_OTP = '1234';

    /**
     * @test
     * @dataProvider operations
     */
    public function it_removes_request_details_from_connection_failures($connection, $service, $url, $host, $operation)
    {
        $this->stubHttp([
            $url => function (Request $request) {
                throw new ConnectException(
                    'cURL error 28: Operation timed out after 30001 milliseconds for '.$request->url().' '.$request->body(),
                    $request->toPsrRequest(),
                    null,
                    ['errno' => 28]
                );
            },
        ]);

        $e = $this->catchFailure($connection, $service, $operation);

        $this->assertInstanceOf(ConnectionException::class, $e);
        $this->assertNull($e->getPrevious());
        $this->assertRedacted($e);
        $this->assertSame(
            "SMS gateway request to {$host} failed (cURL error 28). Request details were removed from this message.",
            $e->getMessage()
        );
    }

    /**
     * @test
     * @dataProvider operations
     */
    public function it_removes_request_details_from_other_transport_failures($connection, $service, $url, $host, $operation)
    {
        $this->stubHttp([
            $url => function (Request $request) {
                throw new RequestException(
                    'cURL error 56: Recv failure: Connection reset by peer for '.$request->url().' '.$request->body(),
                    $request->toPsrRequest(),
                    null,
                    null,
                    ['errno' => 56]
                );
            },
        ]);

        $e = $this->catchFailure($connection, $service, $operation);

        $this->assertSame(RequestException::class, get_class($e));
        $this->assertNull($e->getPrevious());
        $this->assertFalse($e->hasResponse());
        $this->assertSame([], $e->getHandlerContext());
        $this->assertRedacted($e);
        $this->assertStringContainsString("request to {$host} failed (cURL error 56)", $e->getMessage());

        $request = $e->getRequest();
        $this->assertSame($host, $request->getUri()->getHost());
        $this->assertSame('', $request->getUri()->getQuery());
        $this->assertSame('', (string) $request->getBody());
        $this->assertStringNotContainsString(self::TEST_PASSWORD, (string) $request->getUri());
    }

    /** @test */
    public function it_keeps_the_too_many_redirects_exception_type()
    {
        $this->stubHttp([
            'smssmartegypt.com/sms/api/?*' => function (Request $request) {
                throw new TooManyRedirectsException('Will not follow more than 5 redirects for '.$request->url(), $request->toPsrRequest());
            },
        ]);

        $e = $this->catchFailure('smseg', 'normal', 'send');

        $this->assertSame(TooManyRedirectsException::class, get_class($e));
        $this->assertRedacted($e);
        $this->assertSame('', $e->getRequest()->getUri()->getQuery());
    }

    /** @test */
    public function it_falls_back_to_a_generic_message_without_a_request()
    {
        $this->stubHttp([
            'smsmisr.com/api/v2' => function () {
                throw new ConnectionException('cURL error 7: Failed to connect for https://smsmisr.com/api/v2?password='.self::TEST_PASSWORD);
            },
        ]);

        $e = $this->catchFailure('smsmisr', 'normal', 'send');

        $this->assertInstanceOf(ConnectionException::class, $e);
        $this->assertRedacted($e);
        $this->assertSame('SMS gateway request failed. Request details were removed from this message.', $e->getMessage());
    }

    /** @test */
    public function it_does_not_change_exceptions_that_are_not_transport_failures()
    {
        $original = new RuntimeException('Not a transport failure');

        $this->stubHttp([
            'smsmisr.com/api/v2' => function () use ($original) {
                throw $original;
            },
        ]);

        $this->assertSame($original, $this->catchFailure('smsmisr', 'normal', 'send'));
    }

    public static function operations(): array
    {
        return [
            'smseg send' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/?*', 'smssmartegypt.com', 'send'],
            'smseg normal otp' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/?*', 'smssmartegypt.com', 'sendOtp'],
            'smseg provider otp' => ['smseg', 'otp', 'smssmartegypt.com/sms/api/otp-send', 'smssmartegypt.com', 'sendOtp'],
            'smseg provider verify' => ['smseg', 'otp', 'smssmartegypt.com/sms/api/otp-check', 'smssmartegypt.com', 'verify'],
            'smseg balance' => ['smseg', 'normal', 'smssmartegypt.com/sms/api/getBalance*', 'smssmartegypt.com', 'balance'],
            'smsmisr send' => ['smsmisr', 'normal', 'smsmisr.com/api/v2', 'smsmisr.com', 'send'],
            'smsmisr normal otp' => ['smsmisr', 'normal', 'smsmisr.com/api/v2', 'smsmisr.com', 'sendOtp'],
            'smsmisr otp service' => ['smsmisr', 'otp', 'smsmisr.com/api/vSMS', 'smsmisr.com', 'sendOtp'],
            'smsmisr balance' => ['smsmisr', 'normal', 'smsmisr.com/api/Request*', 'smsmisr.com', 'balance'],
            'victorylink send' => ['victorylink', null, 'smsvas.vlserv.com/*/SendSMS*', 'smsvas.vlserv.com', 'send'],
            'victorylink otp' => ['victorylink', null, 'smsvas.vlserv.com/*/SendSMS*', 'smsvas.vlserv.com', 'sendOtp'],
            'victorylink balance' => ['victorylink', null, 'smsvas.vlserv.com/*/CheckCredit*', 'smsvas.vlserv.com', 'balance'],
            'ooredoo send' => ['ooredoo', null, 'ooredoo.test/Messenger.asmx/HTTP_SendSms*', 'ooredoo.test', 'send'],
            'ooredoo otp' => ['ooredoo', null, 'ooredoo.test/Messenger.asmx/HTTP_SendSms*', 'ooredoo.test', 'sendOtp'],
            'ooredoo balance' => ['ooredoo', null, 'ooredoo.test/Messenger.asmx/HTTP_Authenticate2*', 'ooredoo.test', 'balance'],
        ];
    }

    /**
     * Run a driver operation and return the exception it throws.
     *
     * @param  string  $connection
     * @param  string|null  $service
     * @param  string  $operation
     * @return \Throwable
     */
    private function catchFailure($connection, $service, $operation): Throwable
    {
        if ($service) {
            config()->set("sms.connections.{$connection}.service", $service);
        }

        // The manager can't resolve Ooredoo yet, so it is built directly.
        $driver = $connection === 'ooredoo'
            ? new Ooredoo(config('sms.connections.ooredoo'))
            : Sms::driver($connection);

        try {
            switch ($operation) {
                case 'send':
                    $driver->send(self::PHONE, self::MESSAGE);
                    break;
                case 'sendOtp':
                    $driver->sendOtp(self::PHONE);
                    break;
                case 'verify':
                    $driver->verify(self::PHONE, self::OTP);
                    break;
                case 'balance':
                    $driver->balance();
                    break;
            }
        } catch (Throwable $e) {
            return $e;
        }

        $this->fail("The {$operation} failure was not propagated.");
    }

    /**
     * Assert no exception in the chain exposes request details.
     *
     * @param  \Throwable  $e
     * @return void
     */
    private function assertRedacted(Throwable $e): void
    {
        $sensitive = [
            self::TEST_USERNAME,
            self::TEST_PASSWORD,
            self::TEST_SENDER_ID,
            'test-token',
            'test-msignature',
            'test-sms-id',
            'test-customer',
            self::PHONE,
            self::MESSAGE,
            urlencode(self::MESSAGE),
            self::OTP,
            self::GENERATED_OTP,
            'password=',
            '?',
        ];

        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            foreach ($sensitive as $value) {
                $this->assertStringNotContainsString($value, $current->getMessage());
            }
        }
    }
}
