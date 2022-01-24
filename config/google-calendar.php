<?php
// config for GTerrusa/LaravelGoogleCalendar
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

    /**
     *  Values can be 'all', 'externalOnly', and 'none'.
     */
    'send_updates' => 'all',

    /**
     * Default new calendar notifications.
     * Set to false for no notifications.
     */
    'notification_settings' => [
        'notifications' => [
            [ 'method' => 'email', 'type' => 'eventCreation' ],
            [ 'method' => 'email', 'type' => 'eventChange' ],
            [ 'method' => 'email', 'type' => 'eventCancellation' ],
            [ 'method' => 'email', 'type' => 'eventResponse' ],
            [ 'method' => 'email', 'type' => 'agenda' ],
        ]
    ],

    /**
     *  Default event reminders.
     *
     *  Set false to use default reminders.
     *
     *  example:
     *  'override_default_reminders' => [
     *    [ 'method' => 'email', 'minutes' => 24 * 60 ], // send email 1 day before.
     *    [ 'method' => 'email', 'minutes' => 2 * 60 ], // send email 2 hrs before.
     *    [ 'method' => 'popup', 'minutes' => 24 * 60 ], // send popup 1 day before.
     *    [ 'method' => 'popup', 'minutes' => 2 * 60 ]  // send email 2 hrs before.
     *  ]
     */
    'override_default_reminders' => false,

    'guests_can_invite_others' => false,

    'guests_can_modify' => false,

    'guests_can_see_other_guests' => false
];
