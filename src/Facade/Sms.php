<?php

namespace Shabayek\Sms\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Sms Facade class.
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class Sms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sms';
    }
}
