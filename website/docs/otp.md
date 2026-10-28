---
title: OTP and verification
description: What sendOtp() returns, timeouts, and how verify() compares codes.
---

# OTP and verification

## OTP results

`sendOtp()` returns the generated code when the request is treated as sent, and `null` when it is not (rejected by the gateway, unexpected response, or the `null` driver). Check for `null` before storing the code: passing `null` to `verify()` as the actual otp throws an exception.

```php
$code = Sms::sendOtp('201000000000');

if ($code === null) {
    // not sent: show an error or let the user retry
}
```

:::warning
SMS EG with service **otp** always returns `null`, because the gateway generates and checks the code itself. The return value does not tell you whether the gateway accepted the request, so don't use the check above with this service.
:::

## Timeouts and connection failures

- Connection failures such as timeouts throw `Illuminate\Http\Client\ConnectionException` instead of returning `null`.
- Requests time out after 30 seconds, or 10 seconds while connecting. Change this with `SMS_TIMEOUT` and `SMS_CONNECT_TIMEOUT` (or `timeout` and `connect_timeout` in `config/sms.php`). A timed-out request may still have been accepted by the gateway, so don't resend automatically: the user could get the SMS twice.
- A returned code means the gateway accepted the request at best. No driver confirms that the SMS was delivered.

## Verification

- `verify()` only accepts non-negative integers and strings of digits. Anything else (booleans, `null`, floats, spaces, values like `1.2e5`) returns `false`. The integer `123456` and the string `'123456'` match each other, but a string with a leading zero only matches the same string. Validate the input first, for example with `'otp' => 'required|digits:6'`.
- In the `local` and `testing` environments the code is always `1234`. Every other environment gets a random 6-digit code, so make sure `APP_ENV` is neither `local` nor `testing` on any server real users can reach.

## Your application's responsibilities

The package sends codes and compares them. Your application handles the rest:

- **Expiry:** store the code with a timestamp, and reject it after a few minutes.
- **Attempts:** limit how many times a code can be checked, then discard it.
- **Sending limits:** throttle `sendOtp()` per phone number and per IP address, for example with Laravel's `RateLimiter`. Every message costs money, and repeated sends can be abused.
- **Storage:** keep the code on the server (cache or database), never in a form field or cookie the user can read.
- **Logging:** the package removes request details from transport error messages, but Laravel's HTTP client events and tools that record requests, such as Telescope, still see the full request. Several gateways put the username, password, phone number and message text in the URL, so keep those tools off in production or filter what they record.
- **Stack traces:** with `zend.exception_ignore_args=Off`, stack traces show function arguments, such as the phone number and the code passed to `verify()`. Keep `zend.exception_ignore_args=On` (the production default in `php.ini-production`) or scrub traces before sending them to an error tracker.
- **Base URL:** a malformed `base_url` (Ooredoo) makes Guzzle throw an "Unable to parse URI" error that contains the URL. Don't put credentials in it.
