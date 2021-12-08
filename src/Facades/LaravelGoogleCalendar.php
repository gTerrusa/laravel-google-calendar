<?php

namespace GTerrusa\LaravelGoogleCalendar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendar
 */
class LaravelGoogleCalendar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-google-calendar';
    }
}
