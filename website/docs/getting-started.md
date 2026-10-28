---
title: Getting started
description: Install Laravel SMS, publish the config and send your first message.
---

# Getting started

Laravel SMS sends text messages and one-time passwords through several SMS gateways from one `Sms` facade. You switch gateways in config, get structured results from every send, and can register your own drivers.

## Requirements

- PHP 8.1 or later
- A Laravel application

## Installation

Install with Composer:

```bash
composer require shabayek/laravel-sms
```

The service provider and the `Sms` facade alias are registered automatically through Laravel package discovery.

## Publish the config

```bash
php artisan vendor:publish --provider="Shabayek\Sms\Providers\SmsServiceProvider"
```

or by tag:

```bash
php artisan vendor:publish --tag=laravel-sms
```

This creates `config/sms.php`.

## Choose a gateway

Choose what gateway you would like to use for your application. Then make that as default driver so that you don't have to specify that everywhere. But, you can also use multiple gateways in a project.

```php
'default' => 'smseg',
```

Then fill the credentials for that gateway in your `.env` file:

```bash
SMS_CONNECTION=smseg
SMS_USERNAME=username
SMS_PASSWORD=password
SMS_SENDER_ID=sender
SMS_SERVICE=normal
```

Some gateways need extra settings. See [Supported gateways](./gateways.md) for the connection names and the settings each one reads.

## Quick start

```php
use Shabayek\Sms\Facade\Sms;

$result = Sms::send('201000000000', 'Hello world');

if (! $result['success']) {
    logger()->warning('SMS not sent', $result['provider']);
}

$code = Sms::sendOtp('201000000000'); // the code that was sent, or null if it wasn't sent

// Later, compare the code the user typed with the one you stored:
$valid = Sms::verify('201000000000', $request->input('otp'), $storedCode);
```

Next steps:

- [Sending messages](./sending.md): normal messages, OTP messages and the OTP text.
- [Response codes](./response-codes.md): what `send()` returns.
- [OTP and verification](./otp.md): what `sendOtp()` returns and how `verify()` compares codes.
