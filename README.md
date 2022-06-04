# Laravel SMS

[![Github Status](https://github.com/shabayekdes/laravel-sms/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/shabayekdes/laravel-sms/actions) [![Styleci Status](https://github.styleci.io/repos/481996033/shield?style=flat&branch=develop)](https://github.styleci.io/repos/421966331) [![Packagist version](https://img.shields.io/packagist/v/shabayek/laravel-sms)](https://packagist.org/packages/shabayek/laravel-sms) [![mit](https://img.shields.io/apm/l/laravel)](https://packagist.org/packages/shabayek/laravel-sms) ![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/shabayek/laravel-sms) ![Packagist Downloads](https://img.shields.io/packagist/dt/shabayek/laravel-sms)

This is a Laravel Package for SMS Gateway Integration. Now Sending SMS is easy.

### List of supported gateways:
- [x] [SMS EG](https://www.smseg.com/en)
- [x] [SMS Misr](https://www.sms.com.eg/website)
- [x] [Victory Link](http://www.victorylink.com/)
- [x] [Ooredoo](https://www.ooredoo.com/en/)
- [ ] ...


## Install
Via Composer

``` bash
composer require shabayek/laravel-sms
```

## Usage

- Publish the config file

``` bash
php artisan vendor:publish --provider="Shabayek\Sms\SmsServiceProvider"
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
use Shabayek\Sms\Facades\Sms;
    
$sms = Sms::send('0120000000', 'Hello world');
```

- Send otp sms message

> if you set sms service **normal** it's will send via sms message
> if you set sms service **otp** it's will send via sms message

``` php
use Shabayek\Sms\Facades\Sms;

$sms = Sms::sendOtp('0120000000');
```

- Verify phone number

``` php
use Shabayek\Sms\Facades\Sms;

$phone = '09121234567'; // phone number
$otp = '123456'; // otp that you sent to phone
$actualOtp = '123456'; // this is the actual otp that you sent to the user

$verify = Sms::verify($phone, $otp, $actualOtp); // third params is optional with service otp
```

### Adding Custom Cache Drivers

To create our custom sms driver, we first need to implement the Shabayek\Sms\Contracts\SmsGatewayContract contract. So, a new SMS gateway implementation might look something like this:

```php
namespace Shabayek\Sms\Contracts;

class CustomSms implements SmsGatewayContract
{
    public function send($phone, $message): array;

    public function sendOtp($phone, $message = null);

    public function verify(string $phone, int $otp, $actualOtp = null): bool;

    public function balance() { }
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

we can finish our custom driver registration by calling the Sms facade's extend method:

```php
Sms::extend('custom', function ($app) {
    return new CustomSms(config());
});
```


### Testing

``` bash
composer test
```


## Security Vulnerabilities

If you've found a bug regarding security please mail [esmail.shabayek@gmail.com](mailto:esmail.shabayek@gmail.com) instead of using the issue tracker.


## License

The Laravel SMS Gateway package is open-sourced software licensed under the [MIT license](https://github.com/shabayekdes/laravel-sms/blob/main/LICENSE).
