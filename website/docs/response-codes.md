---
title: Response codes
description: The structured result returned by send() and how to customize its messages.
---

# Response codes

`send()` returns an array with a `code` you can check (a `Shabayek\Sms\Enums\ResponseCode` case), a `message` you can customize, and the gateway's own code and message when it returned them:

```php
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

```php
use Shabayek\Sms\Enums\ResponseCode;
use Shabayek\Sms\Facade\Sms;

$result = Sms::send('0120000000', 'Hello world');

if ($result['code'] === ResponseCode::Rejected) {
    logger()->warning('SMS rejected', $result['provider']);
}
```

Check `code` rather than `message` in your application. The case serializes to its string value in JSON, and `ResponseCode::from('rejected')` turns a stored value back into a case.

## Customize the messages

To change the texts, publish the config and edit the `messages` array:

```php
'messages' => [
    'sent' => 'Message sent successfully',
    'rejected' => 'Message not sent successfully',
    'http_error' => 'Message not sent successfully',
    'invalid_response' => 'Message not sent successfully',
    'disabled' => 'SMS driver is disabled',
],
```
