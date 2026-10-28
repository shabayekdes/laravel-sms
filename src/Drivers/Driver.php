<?php

namespace Shabayek\Sms\Drivers;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Shabayek\Sms\Enums\Service;
use Throwable;

/**
 * Driver class.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
abstract class Driver
{
    /**
     * Username.
     *
     * @var string
     */
    protected $username;
    /**
     * Password.
     *
     * @var string
     */
    protected $password;
    /**
     * Sender ID.
     *
     * @var string
     */
    protected $sender_id;
    /**
     * Language.
     *
     * @var string
     */
    protected $language;
    /**
     * Service of sms gateway.
     *
     * @var string
     */
    protected $service = Service::SMS_NORMAL_SERVICE;

    /**
     * Driver Constructor.
     *
     * @param  string  $username
     * @param  string  $password
     * @param  string  $sender_id
     * @return void
     */
    public function __construct($username, $password, $sender_id)
    {
        $this->username = $username;
        $this->password = $password;
        $this->sender_id = $sender_id;

        $this->language = config('sms.language');
    }

    /**
     * Verify phone number.
     *
     * @param  string  $phone
     * @param  int  $otp
     * @param  int|null  $actualOtp
     * @return bool
     *
     * @throws Exception
     */
    public function verify($phone, $otp, $actualOtp = null): bool
    {
        if (is_null($actualOtp)) {
            throw new \Exception('Actual otp is required for this service');
        }

        if (! $this->isValidOtp($otp) || ! $this->isValidOtp($actualOtp)) {
            return false;
        }

        return hash_equals((string) $actualOtp, (string) $otp);
    }

    /**
     * Determine if the given value is a well-formed otp.
     *
     * Only non-negative integers and digit-only strings are accepted, so
     * booleans, floats and padded or numeric-looking strings never match.
     *
     * No return type is declared, so subclasses that already define this
     * method without one remain compatible.
     *
     * @param  mixed  $otp
     * @return bool
     */
    protected function isValidOtp($otp)
    {
        if (is_int($otp)) {
            return $otp >= 0;
        }

        return is_string($otp) && preg_match('/^\d+\z/', $otp) === 1;
    }

    /**
     * Generate otp code.
     *
     * The fixed development code is limited to the local and testing
     * environments; any other environment name gets a random code.
     *
     * @return int
     */
    protected function generateCode()
    {
        if (app()->environment('local', 'testing')) {
            $code = 1234;
        } else {
            $code = random_int(100000, 999999);
        }

        return $code;
    }

    /**
     * Send sms message.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return array
     */
    protected function getMessage($code): string
    {
        $message = config("sms.message.{$this->language}", 'Your verification code is: {code}');

        return Str::replace('{code}', $code, $message);
    }

    /**
     * Run a gateway request without exposing request details on failure.
     *
     * Guzzle transport errors end with the full request URL, which can hold
     * credentials, phone numbers, message text and otp codes, and keep the
     * request itself. They are rethrown as the same exception type with a
     * message that only names the gateway host, a request without query or
     * body, and no previous exception, since that would carry the original.
     *
     * The callback receives a pending request with the configured timeouts.
     *
     * @param  callable  $request
     * @return \Illuminate\Http\Client\Response
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \GuzzleHttp\Exception\RequestException
     */
    protected function sendSafely(callable $request)
    {
        try {
            return $request($this->pendingRequest());
        } catch (ConnectionException $e) {
            throw new ConnectionException($this->transportErrorMessage($e->getPrevious()));
        } catch (RequestException $e) {
            $class = $e instanceof TooManyRedirectsException ? TooManyRedirectsException::class : RequestException::class;

            throw new $class($this->transportErrorMessage($e), $this->redactRequest($e->getRequest()));
        }
    }

    /**
     * Create a gateway request with the configured timeouts.
     *
     * A timeout of 0 waits indefinitely, as Guzzle does by default.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function pendingRequest(): PendingRequest
    {
        return Http::timeout((int) config('sms.timeout', 30))
            ->withOptions(['connect_timeout' => (int) config('sms.connect_timeout', 10)]);
    }

    /**
     * Build a transport error message from safe details only.
     *
     * @param  \Throwable|null  $e
     * @return string
     */
    private function transportErrorMessage(?Throwable $e): string
    {
        $host = null;
        $errno = null;

        if ($e !== null && method_exists($e, 'getRequest')) {
            $host = $e->getRequest()->getUri()->getHost();
        }

        if ($e !== null && method_exists($e, 'getHandlerContext')) {
            $errno = $e->getHandlerContext()['errno'] ?? null;
        }

        $message = $host ? "SMS gateway request to {$host} failed" : 'SMS gateway request failed';

        if (is_int($errno)) {
            $message .= " (cURL error {$errno})";
        }

        return $message.'. Request details were removed from this message.';
    }

    /**
     * Copy a request without user info, query string, headers or body.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @return \Psr\Http\Message\RequestInterface
     */
    private function redactRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri()->withUserInfo('')->withQuery('')->withFragment('');

        return new Request($request->getMethod(), $uri);
    }

    /**
     * Language.
     *
     * @param  string  $language  Language
     * @return self
     */
    public function setLanguage($language): self
    {
        $this->language = $language;

        return $this;
    }
}
