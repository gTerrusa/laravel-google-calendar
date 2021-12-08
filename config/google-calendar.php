<?php
// config for GTerrusa/LaravelGoogleCalendar
return [
    'google-oauth-token-path' => env(
        'GOOGLE_OAUTH_TOKEN_PATH',
        storage_path('google-calendar/oauth-token.json')
    ),

    'google-oauth-credentials-path' => env(
        'GOOGLE_OAUTH_CREDENTIALS_PATH',
        storage_path('google-calendar/oauth-credentials.json')
    )
];
