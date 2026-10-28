<p align="center">
  <a href="https://github.com/shabayekdes/laravel-sms">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="assets/branding/logo-dark.svg">
      <img src="assets/branding/logo.svg" alt="Laravel SMS" height="72">
    </picture>
  </a>
</p>

<p align="center">
  SMS and OTP gateway drivers for Laravel, behind one facade.
</p>

<p align="center">
  <a href="https://laravel-sms.pages.dev"><strong>Documentation</strong></a> ·
  <a href="https://laravel-sms.pages.dev/docs/getting-started">Getting started</a> ·
  <a href="CHANGELOG.md">Changelog</a>
</p>

<p align="center">
  <a href="https://github.com/shabayekdes/laravel-sms/actions/workflows/tests.yml"><img src="https://github.com/shabayekdes/laravel-sms/actions/workflows/tests.yml/badge.svg?branch=main" alt="Tests"></a>
  <a href="https://github.styleci.io/repos/481996033"><img src="https://github.styleci.io/repos/481996033/shield?style=flat&branch=main" alt="StyleCI"></a>
  <a href="https://packagist.org/packages/shabayek/laravel-sms"><img src="https://img.shields.io/packagist/v/shabayek/laravel-sms" alt="Latest version on Packagist"></a>
  <a href="https://packagist.org/packages/shabayek/laravel-sms"><img src="https://img.shields.io/packagist/php-v/shabayek/laravel-sms" alt="PHP version"></a>
  <a href="https://packagist.org/packages/shabayek/laravel-sms"><img src="https://img.shields.io/packagist/dt/shabayek/laravel-sms" alt="Total downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/packagist/l/shabayek/laravel-sms" alt="License"></a>
</p>

---

Laravel SMS sends text messages and one-time passwords through several SMS gateways from one `Sms` facade. You switch gateways in config, get structured results from every send, and can register your own drivers.

## Contents

- [Features](#features)
- [Supported gateways](#supported-gateways)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Usage](#usage)
- [Response codes](#response-codes)
- [OTP results and verification](#otp-results-and-verification)
- [Gateway response notes](#gateway-response-notes)
- [Your application's responsibilities](#your-applications-responsibilities)
- [Balance](#balance)
- [Adding custom drivers](#adding-custom-drivers)
- [Testing](#testing)
- [Contributing](#contributing)
- [Security vulnerabilities](#security-vulnerabilities)
- [License](#license)

## Features

- **One API for every gateway:** `send()`, `sendOtp()`, `verify()` and `balance()` work the same way on every driver.
- **Several connections:** set a default in config and pick another one at runtime with `Sms::driver('…')`.
- **OTP built in:** codes are generated with `random_int()` and checked with `hash_equals()`. SMS EG can also generate and check the code on the gateway.
- **Structured results:** `send()` returns a `ResponseCode` enum, a message you can change in config, and the gateway's own code and message.
- **Encoding detection:** the package picks English, Arabic or Unicode encoding from the message text.
- **Timeouts and safe errors:** request and connect timeouts are configurable. Transport errors never include credentials, phone numbers or codes.
- **`null` driver:** turns sending off in local and test environments.
- **Custom drivers:** add your own gateway with `Sms::extend()`.

## Supported gateways

| Gateway | Connection | `send` | `sendOtp` | `balance` | OTP service |
| --- | --- | :---: | :---: | :---: | --- |
| [SMS EG](https://www.smseg.com/en) | `smseg` | ✓ | ✓ | ✓ | `normal` or `otp` (the gateway generates and checks the code) |
| [SMS Misr](https://smsmisr.com/) | `smsmisr` | ✓ | ✓ | ✓ | `normal` or `otp` (the package's code is sent through the OTP endpoint) |
| [Victory Link](https://victorylink.com/) | `victorylink` | ✓ | ✓ | ✓ | `normal` only |
| [Ooredoo Qatar](https://www.ooredoo.qa/) | `ooredoo` | ✓ | ✓ | ✓ | `normal` only |
| Null (sending turned off) | `null` | — | — | — | — |

Phone numbers are sent to the gateway unchanged, so pass them in the format your gateway expects. SMS EG and SMS Misr document numbers in international format without `+` or leading zeros, for example `201000000000` for an Egyptian mobile number. For Victory Link and Ooredoo, check the format with your account manager.

Need another gateway? See [Adding custom drivers](#adding-custom-drivers).

## Installation

Requires PHP 8.1 or later.

Install with Composer:

``` bash
composer require shabayek/laravel-sms
```

The service provider and the `Sms` facade alias are registered automatically through Laravel package discovery.

## Quick start

Set the connection and credentials in `.env`:

``` php
SMS_CONNECTION=smseg
SMS_USERNAME=username
SMS_PASSWORD=password
SMS_SENDER_ID=sender
SMS_SERVICE=normal
```

Send a message, or send a code and check it:

``` php
use Shabayek\Sms\Facade\Sms;

$result = Sms::send('201000000000', 'Hello world');

if (! $result['success']) {
    logger()->warning('SMS not sent', $result['provider']);
}

$code = Sms::sendOtp('201000000000'); // the code that was sent, or null if it wasn't sent

// Later, compare the code the user typed with the one you stored:
$valid = Sms::verify('201000000000', $request->input('otp'), $storedCode);
```

The sections below cover each step in detail.

## Usage

- Publish the config file

``` bash
php artisan vendor:publish --provider="Shabayek\Sms\Providers\SmsServiceProvider"
```

or by tag:

``` bash
php artisan vendor:publish --tag=laravel-sms
```

- Choose what gateway you would like to use for your application. Then make that as default driver so that you don't have to specify that everywhere. But, you can also use multiple gateways in a project.

```php
'default' => 'smseg',
```

- Then fill the credentials for that gateway in the drivers array.

``` php
SMS_CONNECTION=smseg
SMS_USERNAME=username
SMS_PASSWORD=password
SMS_SENDER_ID=sender
SMS_SERVICE=normal
```

- Send normal sms message

``` php
use Shabayek\Sms\Facade\Sms;

$sms = Sms::send('201000000000', 'Hello world');
```

- Send otp sms message

> With service **normal**, the package generates the code and sends it in a regular message built from the `message` config.
> With service **otp**, SMS EG generates and checks the code itself, while SMS Misr sends a code generated by the package through its OTP endpoint. Victory Link and Ooredoo always behave like **normal**.

``` php
use Shabayek\Sms\Facade\Sms;

$sms = Sms::sendOtp('201000000000');
```

- Verify phone number

``` php
use Shabayek\Sms\Facade\Sms;

$phone = '201000000000'; // phone number
$otp = '123456'; // otp that you sent to phone
$actualOtp = '123456'; // this is the actual otp that you sent to the user

$verify = Sms::verify($phone, $otp, $actualOtp); // third param is optional only for SMS EG with service otp
```

- Change the OTP message with `SMS_MESSAGE` (or `message` in `config/sms.php`). Use `{code}` where the code goes. A message without `{code}` makes `sendOtp()` throw an `InvalidArgumentException` before anything is sent. The message can be in any language: the encoding sent to the gateway (English, Arabic or Unicode) is detected from the text.

``` php
SMS_MESSAGE="Your verification code is: {code}"
```

## Response codes

`send()` returns an array with a `code` you can check (a `Shabayek\Sms\Enums\ResponseCode` case), a `message` you can customize, and the gateway's own code and message when it returned them:

``` php
[
    'success' => false,
    'code' => ResponseCode::Rejected,
    'message' => 'Message not sent successfully',
    'provider' => ['code' => '1902', 'message' => null],
]
```

| Value | Case | Meaning |
| --- | --- | --- |
| `sent` | `ResponseCode::Sent` | The gateway accepted the message. |
| `rejected` | `ResponseCode::Rejected` | The gateway answered with a failure value. See `provider`. |
| `http_error` | `ResponseCode::HttpError` | The gateway answered with a non-2xx HTTP status. |
| `invalid_response` | `ResponseCode::InvalidResponse` | The body was empty, malformed or missing the expected value. |
| `disabled` | `ResponseCode::Disabled` | The `null` driver is in use, so nothing was sent. |

``` php
use Shabayek\Sms\Enums\ResponseCode;
use Shabayek\Sms\Facade\Sms;

$result = Sms::send('201000000000', 'Hello world');

if ($result['code'] === ResponseCode::Rejected) {
    logger()->warning('SMS rejected', $result['provider']);
}
```

Check `code` rather than `message` in your application. The case serializes to its string value in JSON, and `ResponseCode::from('rejected')` turns a stored value back into a case. To change the texts, publish the config and edit the `messages` array:

``` php
'messages' => [
    'sent' => 'Message sent successfully',
    'rejected' => 'Message not sent successfully',
    'http_error' => 'Message not sent successfully',
    'invalid_response' => 'Message not sent successfully',
    'disabled' => 'SMS driver is disabled',
],
```

## OTP results and verification

- `sendOtp()` returns the generated code when the request is treated as sent, and `null` when it is not (rejected by the gateway, unexpected response, or the `null` driver). Check for `null` before storing the code: passing `null` to `verify()` as the actual otp throws an exception.

``` php
$code = Sms::sendOtp('201000000000');

if ($code === null) {
    // not sent: show an error or let the user retry
}
```

> SMS EG with service **otp** always returns `null`, because the gateway generates and checks the code itself. The return value does not tell you whether the gateway accepted the request, so don't use the check above with this service.

- Connection failures such as timeouts throw `Illuminate\Http\Client\ConnectionException` instead of returning `null`.
- Requests time out after 30 seconds, or 10 seconds while connecting. Change this with `SMS_TIMEOUT` and `SMS_CONNECT_TIMEOUT` (or `timeout` and `connect_timeout` in `config/sms.php`). A timed-out request may still have been accepted by the gateway, so don't resend automatically: the user could get the SMS twice.
- A returned code means the gateway accepted the request at best. No driver confirms that the SMS was delivered.
- `verify()` only accepts non-negative integers and strings of digits. Anything else (booleans, `null`, floats, spaces, values like `1.2e5`) returns `false`. The integer `123456` and the string `'123456'` match each other, but a string with a leading zero only matches the same string. Validate the input first, for example with `'otp' => 'required|digits:6'`.
- In the `local` and `testing` environments the code is always `1234`. Every other environment gets a random 6-digit code, so make sure `APP_ENV` is neither `local` nor `testing` on any server real users can reach.

## Gateway response notes

- **SMS EG:** `send()` reports success only for an HTTP 2xx response whose `type` is `success`. This format comes from the package's own code and tests and has not been confirmed against SMS EG's API documentation.
- **SMS Misr:** `send()` reports success only for an HTTP 2xx response with code `1901`. With service **otp**, the `vSMS` response body is not interpreted, because its format has not been verified. Any HTTP 2xx response counts as sent, so `sendOtp()` can return a code even when SMS Misr rejected the request inside a 200 response.

See [CHANGELOG.md](CHANGELOG.md) for upgrade notes.

## Your application's responsibilities

The package sends codes and compares them. Your application handles the rest:

- **Expiry:** store the code with a timestamp, and reject it after a few minutes.
- **Attempts:** limit how many times a code can be checked, then discard it.
- **Sending limits:** throttle `sendOtp()` per phone number and per IP address, for example with Laravel's `RateLimiter`. Every message costs money, and repeated sends can be abused.
- **Storage:** keep the code on the server (cache or database), never in a form field or cookie the user can read.
- **Logging:** the package removes request details from transport error messages, but Laravel's HTTP client events and tools that record requests, such as Telescope, still see the full request. Several gateways put the username, password, phone number and message text in the URL, so keep those tools off in production or filter what they record.
- **Stack traces:** with `zend.exception_ignore_args=Off`, stack traces show function arguments, such as the phone number and the code passed to `verify()`. Keep `zend.exception_ignore_args=On` (the production default in `php.ini-production`) or scrub traces before sending them to an error tracker.
- **Base URL:** a malformed `base_url` (Ooredoo) makes Guzzle throw an "Unable to parse URI" error that contains the URL. Don't put credentials in it.

## Balance

`balance()` returns the credit the gateway reports. Some failures can't be told apart from a real balance yet:

- **SMS EG and SMS Misr:** return `0` when the gateway answers with an error or an unexpected response, so `0` doesn't always mean an empty account.
- **Victory Link:** returns the gateway's value as-is. Victory Link documents `-1` as invalid credentials and `0` as unlimited credit.
- **Ooredoo:** throws an exception when the response has no credit information.

## Adding custom drivers

- Writing The Driver

To create our custom sms driver, we first need to implement the `Shabayek\Sms\Contracts\SmsGatewayContract` contract. So, a new SMS gateway implementation might look something like this:

```php
namespace App\Sms;

use Shabayek\Sms\Contracts\SmsGatewayContract;
use Shabayek\Sms\Enums\ResponseCode;

class CustomSms implements SmsGatewayContract
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send($phone, $message): array
    {
        // Call your gateway here.
        return ['success' => true, 'code' => ResponseCode::Sent, 'message' => ResponseCode::Sent->message(), 'provider' => ['code' => null, 'message' => null]];
    }

    public function sendOtp($phone)
    {
        // Return the code you sent, or null when nothing was sent.
        return null;
    }

    public function verify(string $phone, int $otp, $actualOtp = null): bool
    {
        return $actualOtp !== null && hash_equals((string) $actualOtp, (string) $otp);
    }

    public function balance()
    {
        return 0;
    }
}
```

Then, we need to add a new config for our custom sms driver. in sms config within **connections** key

```php
'connections' => [
    ...
    'custom' => [
        'driver' => 'custom',
        'username' => 'username',
        'password' => 'password',
        'sender_id' => 'sender',
        'service' => 'normal',
    ],
    ...
],

```

we can finish our custom driver registration by calling the Sms facade's extend method, for example in a service provider's `boot` method:

```php
use App\Sms\CustomSms;
use Shabayek\Sms\Facade\Sms;

Sms::extend('custom', function ($app, array $config) {
    return new CustomSms($config);
});
```

> The closure receives the application and the config of the connection being resolved. The first argument of `extend` can be the connection name (the key under **connections**) or the `driver` value of the connection; a creator registered for the connection name wins. Resolve the driver through the `Sms` facade, for example `Sms::driver('custom')`.

## Testing

``` bash
composer test
```

## Contributing

Issues and pull requests are welcome on [GitHub](https://github.com/shabayekdes/laravel-sms). Run `composer test` before you open a pull request. Code style follows the Laravel preset and is checked by StyleCI. See [CHANGELOG.md](CHANGELOG.md) for release history and upgrade notes.

## Security vulnerabilities

If you've found a bug regarding security please mail [esmail.shabayek@gmail.com](mailto:esmail.shabayek@gmail.com) instead of using the issue tracker.

## License

The Laravel SMS Gateway package is open-sourced software licensed under the [MIT license](LICENSE).

<sub>Laravel is a trademark of Taylor Otwell. This is a community package and is not affiliated with or endorsed by Laravel. Brand assets are in [`assets/branding`](assets/branding/BRAND.md).</sub>
