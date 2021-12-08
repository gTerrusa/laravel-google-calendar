# Laravel Google Calendar package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gterrusa/laravel-google-calendar.svg?style=flat-square)](https://packagist.org/packages/gterrusa/laravel-google-calendar)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/gterrusa/laravel-google-calendar/run-tests?label=tests)](https://github.com/gterrusa/laravel-google-calendar/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/gterrusa/laravel-google-calendar/Check%20&%20fix%20styling?label=code%20style)](https://github.com/gterrusa/laravel-google-calendar/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/gterrusa/laravel-google-calendar.svg?style=flat-square)](https://packagist.org/packages/gterrusa/laravel-google-calendar)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require gterrusa/laravel-google-calendar
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-google-calendar_without_prefix-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="laravel-google-calendar_without_prefix-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="example-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$laravel-google-calendar = new GTerrusa\LaravelGoogleCalendar();
echo $laravel-google-calendar->echoPhrase('Hello, GTerrusa!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [gTerrusa](https://github.com/gTerrusa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
