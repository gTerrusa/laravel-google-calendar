<?php

namespace GTerrusa\LaravelGoogleCalendar;

use Carbon\Carbon;
use Google_Service_Calendar;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_CalendarList;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_EventExtendedProperties;
use GTerrusa\LaravelGoogleCalendar\Http\Requests\GoogleCalendarEventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RRule\RRule;
use Spatie\GoogleCalendar\Event;

class LaravelGoogleCalendar extends Event
{
    /**
     * converts carbon object to an array with 'dateTime' and 'timeZone' properties
     *
     * @param Carbon $carbon
     * @return array
     */
    public static function carbonToDateTimeArray(Carbon $carbon): array
    {
        return [
            'dateTime' => $carbon->toDateTimeLocalString(),
            'timeZone' => $carbon->getTimezone()->getName(),
        ];
    }

    /**
     * converts carbon object to an array with 'date' and 'timeZone' properties
     *
     * @param Carbon $carbon
     * @return array
     */
    public static function carbonToDateArray(Carbon $carbon): array
    {
        return [
            'date' => $carbon->toDateString(),
            'timeZone' => $carbon->getTimezone()->getName(),
        ];
    }

    /**
     * returns a Google_Service_Calendar for direct api usage
     *
     * @param string|null $calendarId
     * @return Google_Service_Calendar
     */
    public static function getGoogleCalendarService(string $calendarId = null): Google_Service_Calendar
    {
        return static::getGoogleCalendar($calendarId)->getService();
    }

    /**
     * list all google calendars
     *
     * @return Google_Service_Calendar_CalendarList
     */
    public static function listAllCalendars(): Google_Service_Calendar_CalendarList
    {
        return static::getGoogleCalendarService()->calendarList->listCalendarList();
    }

    /**
     * list all events from a google calendar
     *
     * @param null $calendarId
     * @param null $start
     * @param null $end
     * @return Collection
     */
    public static function listEvents($calendarId = null, $start = null,  $end = null): Collection
    {
        $calendarId = $calendarId ?? config('google-calendar.calendar_id');
        $start = $start ? new Carbon($start) : Carbon::now()->subYear();
        $end = $end ? new Carbon($end) : Carbon::now()->addYear();

        return collect(static::get($start, $end, [], $calendarId))
            ->map(function ($event) use ($calendarId) {
                $event = $event->googleEvent;
                $event->calendar_id = $calendarId;

                return $event;
            });
    }

    /**
     * creates a recurring google calendar event
     *
     * @param array $properties
     * @param string|null $calendarId
     * @param $optParams
     * @return LaravelGoogleCalendar
     */
    public static function createRecurringEvent(array $properties, string $calendarId = null, $optParams = [])
    {
        $event = new static;

        $event->calendarId = static::getGoogleCalendar($calendarId)->getCalendarId();

        foreach ($properties as $name => $value) {
            $event->$name = $value;
        }

        $event->googleEvent->setRecurrence($properties['recurrence'] ?? null);

        return $event->save('insertEvent', $optParams);
    }

    /**
     * creates a new google calendar from a Request
     *
     * @param Request $request
     * @return Google_Service_Calendar_Calendar
     */
    public static function createCalendarFromRequest(Request $request): Google_Service_Calendar_Calendar
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($request->title);
        $calendar->setDescription($request->description);
        $calendar->setTimeZone('America/Los_Angeles');

        return static::getGoogleCalendarService()->calendars->insert($calendar);
    }

    /**
     * create a new event from a GoogleCalendarEventRequest
     *
     * @param GoogleCalendarEventRequest $request
     * @return mixed
     */
    public static function createEventFromRequest(GoogleCalendarEventRequest $request)
    {
        $calendarId = $request->calendar_id ?? config('google-calendar.calendar_id');

        $eventProps = [
            'summary' => $request->title,
            'start' => $request->allDay
                ? static::carbonToDateArray(Carbon::create($request->start))
                : static::carbonToDateTimeArray(Carbon::create($request->start)),
            'end' => $request->allDay
                ? static::carbonToDateArray(Carbon::create($request->start)->addDay())
                : static::carbonToDateTimeArray(Carbon::create($request->end)),
            'extendedProperties' => [
                'shared' => [
                    'max_attendees' => $request->max_attendees,
                ],
                'private' => [
                    'event_type' => $request->event_type,
                ],
            ],
            'description' => $request->description ?? '',
            'location' => $request->location ?? '',
            'recurrence' => $request->recurrence ?? null,
        ];

        return $request->recurrence
            ? static::createRecurringEvent($eventProps, $calendarId)
            : static::create($eventProps, $calendarId);
    }

    /**
     * update an event from a GoogleCalendarEventRequest
     *
     * @param GoogleCalendarEventRequest $request
     * @return mixed
     */
    public static function updateEventFromRequest(GoogleCalendarEventRequest $request)
    {
        // validate that request includes id
        $request->validate([
            'id' => 'required|string',
        ]);

        // set calendar id
        $calendarId = $request->calendar_id ?? config('google-calendar.calendar_id');

        // get event
        $event = static::find($request->id, $calendarId)->googleEvent;

        // check recurring options
        if ($request->include_following && $event->recurringEventId) {
            // get original recurring event
            $recurringEvent = static::find($event->recurringEventId, $calendarId)->googleEvent;

            // save original rrule
            $request->recurrence = $recurringEvent->recurrence;

            // change rrule to end before the new event's start
            $rrule = RRule::createFromRfcString($recurringEvent->recurrence[0]);
            $rruleArr = $rrule->getRule();
            $rruleArr['UNTIL'] = ($until = Carbon::create($request->start)->subDay())->toDateTimeString();
            $newRrule = new RRule($rruleArr);
            $recurringEvent->setRecurrence(['RRULE:' . $newRrule->rfcString()]);

            if ($until->lessThanOrEqualTo(Carbon::create($recurringEvent->start['date'] ?? $recurringEvent->start['dateTime']))) {
                static::deleteEvent($recurringEvent->getId(), $calendarId);
            } else {
                // update original recurring event to end before the instance being edited
                static::getGoogleCalendarService()->events->update(
                    $calendarId,
                    $recurringEvent->getId(),
                    $recurringEvent
                );
            }

            // create a new event with the original recurring event's recurrence rules
            return static::createEventFromRequest($request);
        }

        // set start and end
        $start = new Google_Service_Calendar_EventDateTime();
        $end = new Google_Service_Calendar_EventDateTime();
        if ($request->allDay) {
            $startDateArray = static::carbonToDateArray($carbonStart = Carbon::create($request->start));
            $endDateArray = static::carbonToDateArray(
                ($carbonEnd = Carbon::create($request->end))->isSameDay($carbonStart)
                    ? $carbonEnd->addDay()
                    : $carbonEnd
            );
            $start->setDate($startDateArray['date']);
            $start->setTimeZone($startDateArray['timeZone']);
            $end->setDate($endDateArray['date']);
            $end->setTimeZone($endDateArray['timeZone']);
        } else {
            $startDateTimeArray = static::carbonToDateTimeArray(Carbon::create($request->start));
            $endDateTimeArray = static::carbonToDateTimeArray(Carbon::create($request->end));
            $start->setDateTime($startDateTimeArray['dateTime']);
            $start->setTimeZone($startDateTimeArray['timeZone']);
            $end->setDateTime($endDateTimeArray['dateTime']);
            $end->setTimeZone($endDateTimeArray['timeZone']);
        }

        // set extended properties
        $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
        $extendedProperties->setShared(['max_attendees' => $request->max_attendees]);
        $extendedProperties->setPrivate(['event_type' => $request->event_type]);

        // edit data
        $event->setSummary($request->title);
        $event->setStart($start);
        $event->setEnd($end);
        $event->setExtendedProperties($extendedProperties);
        $event->setDescription($request->description ?? '');
        $event->setLocation($request->location ?? '');

        // perform update
        return static::getGoogleCalendarService()->events->update(
            $calendarId,
            $event->getId(),
            $event
        );
    }

    /**
     * add an attendee to an event from a request
     *
     * @param Request $request
     * @return Google_Service_Calendar_Event
     */
    public static function addAttendeeFromRequest(Request $request): Google_Service_Calendar_Event
    {
        $request->validate([
            'id' => 'required|string',
            'attendee' => 'required|array',
        ]);

        $calendarId = $request->calendar_id ?? config('google-calendar.calendar_id');

        $event = static::find($request->id, $calendarId)->googleEvent;

        // check if event is full
        $notDeclinedAttendees = collect($event->getAttendees())->filter(function ($a) {
            return $a->responseStatus !== 'declined';
        });
        $eventFull = isset($event->extendedProperties->shared['max_attendees'])
            && ($notDeclinedAttendees->count() >= (int) $event->extendedProperties->shared['max_attendees']);
        if ($eventFull) {
            return $event;
        }

        $attendees = $event->getAttendees();
        $attendees[] = new Google_Service_Calendar_EventAttendee([
            'email' => $request->attendee['email'],
            'comment' => $request->attendee['comment'] ?? null,
            'displayName' => $request->attendee['displayName'] ?? null,
            'additionalGuests' => $request->attendee['additionalGuests'] ?? 0,
            'responseStatus' => $request->attendee['responseStatus'] ?? 'needsAction',
        ]);
        $event->setAttendees($attendees);

        return static::getGoogleCalendarService()->events->update(
            $calendarId,
            $event->getId(),
            $event
        );
    }

    /**
     * update a google event attendee from a request
     *
     * @param Request $request
     * @return Google_Service_Calendar_Event
     */
    public static function updateAttendeeFromRequest(Request $request): Google_Service_Calendar_Event
    {
        $request->validate([
            'id' => 'required|string',
            'attendee' => 'required|array',
        ]);

        $calendarId = $request->calendar_id ?? config('google-calendar.calendar_id');

        $event = static::find($request->id, $calendarId)->googleEvent;
        $attendees = $event->getAttendees();

        foreach ($attendees as $attendee) {
            if ($attendee['email'] === $request->attendee['email']) {
                foreach ($request->attendee as $key => $value) {
                    $attendee[$key] = $value;
                }
            }
        }

        $event->setAttendees($attendees);

        return static::getGoogleCalendarService()->events->update(
            $calendarId,
            $event->getId(),
            $event
        );
    }

    /**
     * deletes a google calendar event from a request
     *
     * @param Request $request
     * @return Google_Service_Calendar_Event|\GuzzleHttp\Psr7\PumpStream|\GuzzleHttp\Psr7\Stream|\Psr\Http\Message\StreamInterface|null
     */
    public static function deleteEventFromRequest(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'calendar_id' => 'required|string',
        ]);

        // check recurring options
        if ($request->include_following) {
            // get event
            $event = static::find($request->id, $request->calendar_id)->googleEvent;

            // get original recurring event
            $recurringEvent = static::find($event->recurringEventId, $request->calendar_id)->googleEvent;

            // change rrule to end before the event's start
            $rrule = RRule::createFromRfcString($recurringEvent->recurrence[0]);
            $rruleArr = $rrule->getRule();
            $rruleArr['UNTIL'] = ($until = Carbon::create($event->start->dateTime ?? $event->start->date)->subDay())->toDateTimeString();
            $newRrule = new RRule($rruleArr);
            $recurringEvent->setRecurrence(['RRULE:' . $newRrule->rfcString()]);

            if ($until->lessThanOrEqualTo(Carbon::create($recurringEvent->start['date'] ?? $recurringEvent->start['dateTime']))) {
                return static::deleteEvent($recurringEvent->getId(), $request->calendar_id);
            } else {
                // update original recurring event
                return static::getGoogleCalendarService()->events->update(
                    $request->calendar_id,
                    $recurringEvent->getId(),
                    $recurringEvent
                );
            }
        }

        return static::deleteEvent($request->id, $request->calendar_id);
    }

    /**
     * deletes a google calendar event
     *
     * @param string $eventId
     * @param string|null $calendarId
     * @return \GuzzleHttp\Psr7\PumpStream|\GuzzleHttp\Psr7\Stream|\Psr\Http\Message\StreamInterface|null
     */
    public static function deleteEvent(string $eventId, string $calendarId = null)
    {
        $calendarId = $calendarId ?? config('google-calendar.calendar_id');

        return static::getGoogleCalendarService()->events->delete($calendarId, $eventId)->getBody();
    }
}
