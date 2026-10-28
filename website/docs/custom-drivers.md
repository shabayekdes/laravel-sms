---
title: Custom drivers
description: Add your own SMS gateway by implementing the gateway contract.
---

# Custom drivers

## Writing the driver

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

## Configuring the connection

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

## Registering the driver

we can finish our custom driver registration by calling the Sms facade's extend method, for example in a service provider's `boot` method:

```php
use App\Sms\CustomSms;
use Shabayek\Sms\Facade\Sms;

Sms::extend('custom', function ($app, array $config) {
    return new CustomSms($config);
});
```

:::note
The closure receives the application and the config of the connection being resolved. The first argument of `extend` can be the connection name (the key under **connections**) or the `driver` value of the connection; a creator registered for the connection name wins. Resolve the driver through the `Sms` facade, for example `Sms::driver('custom')`.
:::
