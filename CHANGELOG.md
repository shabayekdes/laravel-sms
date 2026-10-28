# Changelog

All notable changes to `shabayek/laravel-sms` are documented in this file.

## Unreleased

### Fixed

- `sendOtp()` throws `InvalidArgumentException` and sends nothing when the `message` template has no `{code}` placeholder. Previously it sent an OTP message without the code.
- Drivers no longer crash with `Undefined array key` when a published config from an older version lacks a connection key, such as `service`, `token` or `customer_id`. A missing `service` defaults to `normal`, and a missing Ooredoo `base_url` defaults to the Ooredoo endpoint.
- Ooredoo `send()` sends the `blink`, `flash` and `Private` flags as `false` instead of `0`, which the gateway's boolean parameters may reject.

### Changed

- `composer.json` declares the Illuminate packages the package uses: `illuminate/http` and `illuminate/support` 8.83 or later, up to 13.x.
- Tests, CI configuration and other development files are left out of the Composer dist archive.
- CI tests every supported Laravel version, 8 through 13, with the PHP versions each one supports.
- The manager is registered as a singleton and can be injected by class: type-hint `Shabayek\Sms\SmsManager`. `app('sms')`, the `Sms` facade and the injected instance are the same object, so creators registered with `extend()` on one are visible on the others.
- Tests include response fixtures built from the SMS EG, SMS Misr and Ooredoo documentation.

### Documentation

- The phone number format each gateway expects, what `balance()` returns when a gateway can't report a balance, and what the application is responsible for with OTP codes (expiry, attempt and send limits, storage, and request logging).
- Fixed the SMS Misr and Victory Link links and the section titles in `config/sms.php`.
- Request data that can still leak outside transport error messages: function arguments in stack traces, and a malformed `base_url` in Guzzle's error.

### Upgrade notes

- If `SMS_MESSAGE` (or `message` in a published config) has no `{code}` placeholder, `sendOtp()` now throws `InvalidArgumentException` instead of sending a message without the code. Add `{code}` to the message.

## v3.0 - 2026-10-28

This is a major release: it requires PHP 8.1, adds response codes to `send()` results and removes `setLanguage()`. Read the upgrade notes before updating from 2.x.

### Removed

- Support for PHP 8.0. The package now requires PHP 8.1 or later, for the native `ResponseCode` enum.
- `setLanguage()` and the `language` config value (`SMS_LANGUAGE`). Messages use one language, and the encoding sent to the gateway is detected from the message text.

### Added

- `send()` results have a `code` key holding a `Shabayek\Sms\Enums\ResponseCode` case: `Sent`, `Rejected`, `HttpError`, `InvalidResponse` or `Disabled`. Check `code` instead of comparing `message` text. A case serializes to its string value (`sent`, `rejected`, `http_error`, `invalid_response`, `disabled`) in JSON.
- The text under `message` comes from the new `messages` config array, one entry per code, so it can be changed without touching code. The defaults are the texts returned in 2.x.
- `send()` results have a `provider` key with the gateway's own `code` and `message` when it returned them (SMS Misr code, Victory Link result, Ooredoo `Result`, SMS EG `error.msg`).
- The encoding sent to SMS Misr (`language`), Victory Link (`SMSLang`) and Ooredoo (`messageType`) is detected from the message text: ASCII text is sent as English/Latin, Arabic text as Arabic, and other text as Unicode on SMS Misr and Arabic elsewhere.

### Changed

- `message` in `config/sms.php` is one string, and the default OTP message is English: `Your verification code is: {code}`.
- SMS EG rejections return `Message not sent successfully` under `message`. The gateway's error text, which was returned there before, is now under `provider.message`.
- Victory Link and Ooredoo messages in plain ASCII text are sent as English/Latin. Before, the default `ar` language sent them as Arabic.

### Fixed

- Ooredoo `balance()` no longer throws `TypeError: Cannot access offset of type string on string` when the response contains a single credit entry.

### Upgrade notes

- PHP 8.1 or later is required. Applications on PHP 8.0 stay on v2.0 until they upgrade PHP.
- Remove calls to `setLanguage()`. If you published the config, replace the `message` array with one string and remove `language`; to keep sending Arabic, set `SMS_MESSAGE` to your Arabic text. Add the `messages` array to customize response texts.
- Code that compared `send()['message']` with `'Message not sent successfully'` or read SMS EG's error text from it should check `send()['code']` and `send()['provider']['message']` instead.
- Custom drivers extending `Shabayek\Sms\Drivers\Driver` no longer have a `$language` property, and inherit new protected `response()`, `isUnicode()` and `isArabic()` methods. A subclass that already defines a method with one of these names must keep a compatible signature.

## v2.0 - 2026-10-28

This is a major release: it requires PHP 8.0 and changes behaviour that applications may rely on. Read the upgrade notes before updating from 1.x.

### Security

- `verify()` no longer uses loose comparison. Only non-negative integers and strings of digits are accepted, and codes are compared with `hash_equals()`. Booleans, `null`, floats, padded strings and numeric-looking strings such as `1.2e5` now return `false`. Previously, `verify($phone, true, $code)` returned `true` for any non-zero code.
- Locally generated codes use `random_int()` instead of `rand()`.
- The fixed development code `1234` is only used in the `local` and `testing` environments. Previously it was used in every environment not named exactly `production`, including `staging`, `prod` and `uat`.
- Transport failures no longer expose request details. Guzzle ends cURL error messages with the full request URL, which for several drivers holds the username, password, phone number, message text and otp code, so a timeout could write them to the application log. Every driver now rethrows these errors as the same exception type (`Illuminate\Http\Client\ConnectionException`, or Guzzle's `RequestException` / `TooManyRedirectsException` for other cURL errors) with a message that names only the gateway host and cURL error number, for example `SMS gateway request to smsmisr.com failed (cURL error 28). Request details were removed from this message.`

### Removed

- Support for PHP 7.3 and 7.4. The package now requires PHP 8.0 or later.

### Added

- Gateway requests time out. The new `timeout` and `connect_timeout` config values (`SMS_TIMEOUT`, `SMS_CONNECT_TIMEOUT`) default to 30 and 10 seconds; previously requests could wait indefinitely on Laravel 8. A timeout throws `Illuminate\Http\Client\ConnectionException`.

### Fixed

- SMS EG and SMS Misr `send()` no longer report success for HTTP errors, empty or malformed bodies, or responses without the expected success value.
- Victory Link and Ooredoo `send()` return `success: false` (and `sendOtp()` returns `null`) for HTTP errors and for bodies that are not well-formed XML. Previously a plain-text error page threw an `ErrorException` from the XML parser, and an HTTP error with a success-looking body counted as sent.
- `sendOtp()` returns `null` instead of a code when the message was not sent (SMS EG normal service, SMS Misr, Victory Link and Ooredoo). SMS Misr no longer returns a code without sending anything when `service` has an unknown value.
- SMS EG `verify()` with service `otp` returns `false` when the verification request does not return HTTP 2xx, and rejects malformed codes without calling the provider.
- The `Sms` alias in `composer.json` now points to the facade class `Shabayek\Sms\Facade\Sms`.
- The Ooredoo driver can be resolved with `Sms::driver('ooredoo')` or by setting `SMS_CONNECTION=ooredoo`. Previously the manager threw `Driver [ooredoo] not supported.` and the driver had to be built by hand.
- The Ooredoo and null drivers no longer create dynamic properties, which are deprecated since PHP 8.2.
- Creators registered with `Sms::extend()` receive the application and the connection config. Previously they received `null`. A creator can now also be registered under the connection's `driver` value; one registered under the connection name still takes precedence.
- An undefined connection reports `SMS connection [name] is not defined.` instead of `Cache store [name] is not defined.`

### Documentation

- README examples now import the facade from `Shabayek\Sms\Facade\Sms` (previously the non-existent `Shabayek\Sms\Facades\Sms`), and the publish command names the actual provider `Shabayek\Sms\Providers\SmsServiceProvider`.
- The custom driver example is valid PHP and uses the config passed to the creator.
- Fixed the `setLanguage()` example and the descriptions of the **normal** and **otp** services.

### Upgrade notes

- PHP 8.0 or later is required. Applications on PHP 7.3 or 7.4 stay on the 1.x releases until they upgrade PHP.
- If you store the result of `sendOtp()` and later pass it to `verify()`, check for `null` first. `verify()` throws `Actual otp is required for this service` when the stored value is `null`.
- A code stored as a string with a leading zero (`'012345'`) no longer matches the integer `12345`. Codes generated by this package never start with zero.
- Servers using an environment other than `local` or `testing` now send random codes. Tests or QA flows that relied on `1234` there need another approach.
- Custom drivers extending `Shabayek\Sms\Drivers\Driver` inherit a new protected `isValidOtp()` method that `verify()` uses. A subclass that already defines a method with that name replaces this validation with its own.
- Transport exceptions thrown by the drivers no longer chain the original exception, so `getPrevious()` returns `null` and the Guzzle handler context is gone. Guzzle `RequestException`s carry a copy of the request without query string, headers or body, and no response. Code that parsed the old message or read these details needs updating.
- Custom drivers extending `Shabayek\Sms\Drivers\Driver` inherit a new protected `sendSafely()` method. A subclass that already defines a method with that name must keep a compatible signature.
- A creator registered with `Sms::extend()` now also applies to every connection whose `driver` matches its name. If you override a built-in driver such as `smseg` under its name and also have other connections using `'driver' => 'smseg'`, those connections now use your creator too. Register the creator under a name no connection uses as its `driver` to keep the old behaviour.
- `Ooredoo::$customer_id` and `SmsNull::$config` are now declared `protected`. They used to be created as public dynamic properties, so code that read them from outside the class needs to keep its own copy of the config instead.
- Gateway requests that take longer than 30 seconds (10 seconds to connect) now fail with a `ConnectionException`. Raise `SMS_TIMEOUT` / `SMS_CONNECT_TIMEOUT`, or set them to `0` to wait indefinitely as before. Published config files don't need the new keys; the defaults apply. A timed-out request may still have been accepted by the gateway, so don't resend automatically.

### Known limitations

- SMS EG with service `otp` returns `null` from `sendOtp()` whether or not the provider accepted the request, so the return value cannot be used to detect failures.
- SMS Misr with service `otp` treats any HTTP 2xx response from `vSMS` as sent, because the response body format is unverified. A rejection returned inside a 200 response is not detected.
- The SMS Misr driver still calls `/api/v2` and `/api/vSMS`, which are not described in SMS Misr's current public API documentation (that documents `/api/SMS/` and `/api/OTP/`). They are kept unchanged until SMS Misr confirms their status. The success values the drivers check, SMS EG's `type: "success"` and SMS Misr's `1901`, match the providers' documentation.
- No driver confirms SMS delivery. A successful result means, at most, that the provider accepted the request.
- Connection failures throw `Illuminate\Http\Client\ConnectionException`, while rejected messages return `null` or `success: false`.
