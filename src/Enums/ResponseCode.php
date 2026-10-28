<?php

namespace Shabayek\Sms\Enums;

/**
 * Response code enum.
 *
 * Every send() result has one of these cases under the "code" key.
 * The "message" key holds the text configured in sms.messages for it.
 *
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
enum ResponseCode: string
{
    /**
     * The gateway accepted the message.
     */
    case Sent = 'sent';

    /**
     * The gateway answered with a failure value, see the provider details.
     */
    case Rejected = 'rejected';

    /**
     * The gateway answered with a non-2xx HTTP status.
     */
    case HttpError = 'http_error';

    /**
     * The gateway answered with an empty, malformed or unexpected body.
     */
    case InvalidResponse = 'invalid_response';

    /**
     * The null driver is in use, so nothing was sent.
     */
    case Disabled = 'disabled';

    /**
     * Determine if the code means the message was sent.
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this === self::Sent;
    }

    /**
     * Get the message configured for the code.
     *
     * @return string
     */
    public function message(): string
    {
        return config("sms.messages.{$this->value}", $this->defaultMessage());
    }

    /**
     * Get the default message for the code.
     *
     * @return string
     */
    public function defaultMessage(): string
    {
        return match ($this) {
            self::Sent => 'Message sent successfully',
            self::Disabled => 'SMS driver is disabled',
            default => 'Message not sent successfully',
        };
    }
}
