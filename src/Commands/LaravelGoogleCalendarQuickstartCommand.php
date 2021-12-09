<?php

namespace GTerrusa\LaravelGoogleCalendar\Commands;

use Exception;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Console\Command;

class LaravelGoogleCalendarQuickstartCommand extends Command
{
    public $signature = 'laravel-google-calendar:quickstart';

    public $description = 'My command';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new Google_Service_Calendar($client);

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = [
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        ];
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        if (empty($events)) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($events as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     * @throws Exception
     */
    private function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig(config('google-calendar.auth_profiles.oauth.credentials_json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        if (file_exists(config('google-calendar.auth_profiles.oauth.token_json'))) {
            $accessToken = json_decode(file_get_contents(config('google-calendar.auth_profiles.oauth.token_json')), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (! file_exists(dirname(config('google-calendar.auth_profiles.oauth.token_json')))) {
                mkdir(dirname(config('google-calendar.auth_profiles.oauth.token_json')), 0700, true);
            }
            file_put_contents(config('google-calendar.auth_profiles.oauth.token_json'), json_encode($client->getAccessToken()));
        }

        return $client;
    }
}
