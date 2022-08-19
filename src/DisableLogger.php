<?php

namespace Shabayek\Sms;

use Psr\Log\LoggerInterface;

class DisableLogger implements LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        // Code here
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function alert($message, array $context = [])
    {
        // Code here
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function critical($message, array $context = [])
    {
        // Code here
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        // Code here
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function warning($message, array $context = [])
    {
        // Code here
    }

    /**
     * Normal but significant events.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function notice($message, array $context = [])
    {
        // Code here
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        // Code here
    }

    /**
     * Detailed debug information.
     *
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     */
    public function debug($message, array $context = [])
    {
        // Code here
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  mixed[]  $context
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        // Code here
    }
}
