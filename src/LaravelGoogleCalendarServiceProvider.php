<?php

namespace GTerrusa\LaravelGoogleCalendar;

use GTerrusa\LaravelGoogleCalendar\Commands\LaravelGoogleCalendarQuickstartCommand;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelGoogleCalendarServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        Validator::extend('string_or_array', function ($attribute, $value, $fail) {
            return is_string($value) || is_array($value);
        });

        return parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-google-calendar')
            ->hasConfigFile()
            ->hasCommand(LaravelGoogleCalendarQuickstartCommand::class)
            ->hasRoute('api');
    }
}
