<?php

namespace GTerrusa\LaravelGoogleCalendar;

use GTerrusa\LaravelGoogleCalendar\Commands\LaravelGoogleCalendarQuickstartCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelGoogleCalendarServiceProvider extends PackageServiceProvider
{
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
            ->hasViews()
            ->hasMigration('create_laravel-google-calendar_table')
            ->hasCommand(LaravelGoogleCalendarQuickstartCommand::class)
            ->hasRoute('api');
    }
}
