---
title: Supported gateways
description: The SMS gateways Laravel SMS supports, their connection names and settings.
---

# Supported gateways

| Gateway | Connection | `send` | `sendOtp` | `balance` | OTP service |
| --- | --- | :---: | :---: | :---: | --- |
| [SMS EG](https://www.smseg.com/en) | `smseg` | ✓ | ✓ | ✓ | `normal` or `otp` (the gateway generates and checks the code) |
| [SMS Misr](https://smsmisr.com/) | `smsmisr` | ✓ | ✓ | ✓ | `normal` or `otp` (the package's code is sent through the OTP endpoint) |
| [Victory Link](https://victorylink.com/) | `victorylink` | ✓ | ✓ | ✓ | `normal` only |
| [Ooredoo Qatar](https://www.ooredoo.qa/) | `ooredoo` | ✓ | ✓ | ✓ | `normal` only |
| Null (sending turned off) | `null` | — | — | — | — |

Phone numbers are sent to the gateway unchanged, so pass them in the format your gateway expects. SMS EG and SMS Misr document numbers in international format without `+` or leading zeros, for example `201000000000` for an Egyptian mobile number. For Victory Link and Ooredoo, check the format with your account manager.

Set the connection with `SMS_CONNECTION`. The default is `null`, which turns sending off. Need another gateway? See [Custom drivers](./custom-drivers.md).

## Settings

Every connection reads `SMS_USERNAME`, `SMS_PASSWORD` and `SMS_SENDER_ID`. The table lists the extra settings each one reads in `config/sms.php`.

| Connection | Extra settings |
| --- | --- |
| `smseg` | `SMS_SERVICE` (`normal` or `otp`, default `normal`) |
| `smsmisr` | `SMS_SERVICE` (`normal` or `otp`, default `normal`), `SMS_TOKEN`, `SMS_MSIGNATURE`, `SMS_SMS_ID` |
| `victorylink` | None |
| `ooredoo` | `SMS_CUSTOMER_ID`, `SMS_BASE_URL` (default `https://messaging.ooredoo.qa/bms/soap/Messenger.asmx`) |

All connections share these settings:

| Setting | Default | Meaning |
| --- | --- | --- |
| `SMS_MESSAGE` | `Your verification code is: {code}` | The OTP message. See [Sending messages](./sending.md#change-the-otp-message). |
| `SMS_TIMEOUT` | `30` | Seconds to wait for a gateway response. |
| `SMS_CONNECT_TIMEOUT` | `10` | Seconds to wait while connecting. |

## Gateway response notes

- **SMS EG:** `send()` reports success only for an HTTP 2xx response whose `type` is `success`. This format comes from the package's own code and tests and has not been confirmed against SMS EG's API documentation.
- **SMS Misr:** `send()` reports success only for an HTTP 2xx response with code `1901`. With service **otp**, the `vSMS` response body is not interpreted, because its format has not been verified. Any HTTP 2xx response counts as sent, so `sendOtp()` can return a code even when SMS Misr rejected the request inside a 200 response.

## Balance

`balance()` returns the credit the gateway reports. Some failures can't be told apart from a real balance yet:

- **SMS EG and SMS Misr:** return `0` when the gateway answers with an error or an unexpected response, so `0` doesn't always mean an empty account.
- **Victory Link:** returns the gateway's value as-is. Victory Link documents `-1` as invalid credentials and `0` as unlimited credit.
- **Ooredoo:** throws an exception when the response has no credit information.
