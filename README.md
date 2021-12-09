# Laravel Google Calendar package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gterrusa/laravel-google-calendar.svg?style=flat-square)](https://packagist.org/packages/gterrusa/laravel-google-calendar)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/gterrusa/laravel-google-calendar/run-tests?label=tests)](https://github.com/gterrusa/laravel-google-calendar/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/gterrusa/laravel-google-calendar/Check%20&%20fix%20styling?label=code%20style)](https://github.com/gterrusa/laravel-google-calendar/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/gterrusa/laravel-google-calendar.svg?style=flat-square)](https://packagist.org/packages/gterrusa/laravel-google-calendar)

A wrapper for [Spatie Laravel Google Calendar](https://github.com/spatie/laravel-google-calendar), that extends its usage.

## Installation

You can install the package via composer:

```bash
composer require gterrusa/laravel-google-calendar
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendarServiceProvider"
```

This is the contents of the published config file:

```php
return [

    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'oauth'),

    'auth_profiles' => [

        /*
         * Authenticate using a service account.
         */
        'service_account' => [
            /*
             * Path to the json file containing the credentials.
             */
            'credentials_json' => env(
                'GOOGLE_SERVICE_ACCOUNT_CREDENTIALS_PATH',
                storage_path('google-calendar/service-account-credentials.json')
            ),
        ],

        /*
         * Authenticate with actual google user account.
         */
        'oauth' => [
            /*
             * Path to the json file containing the oauth2 credentials.
             */
            'credentials_json' => env(
                'GOOGLE_OAUTH_CREDENTIALS_PATH',
                storage_path('google-calendar/oauth-credentials.json')
            ),

            /*
             * Path to the json file containing the oauth2 token.
             */
            'token_json' => env(
                'GOOGLE_OAUTH_TOKEN_PATH',
                storage_path('google-calendar/oauth-token.json')
            ),
        ]
    ],

    /*
     *  The id of the Google Calendar that will be used by default.
     */
    'calendar_id' => env('GOOGLE_CALENDAR_ID'),
];
```

## Google Calendar Integration

1. Login or create an account in the [Google Cloud Console](https://console.developers.google.com/apis)
2. Enable Google Calendar Api
3. Click: 
   1. Apis & Services 
   2. Credentials
   3. Create Credentials 
   4. Oauth client Id 
   5. Web Application
4. Include authorized endpoints and create.
5. Download credentials and paste contents into ```storage/google-calendar/oauth-credentials.json```
6. add ```GOOGLE_CALENDAR_ID={{ google-cloud-email-address-here }}``` to your .env file
7. run ```php artisan laravel-google-calendar:quickstart``` and follow the prompts

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
