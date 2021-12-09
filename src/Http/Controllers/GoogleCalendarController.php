<?php

namespace GTerrusa\LaravelGoogleCalendar\Http\Controllers;

use Carbon\Carbon;
use GTerrusa\LaravelGoogleCalendar\Exports\AttendeeExport;
use GTerrusa\LaravelGoogleCalendar\Http\Requests\GoogleCalendarEventRequest;
use GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendar as GoogleCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GoogleCalendarController extends Controller
{
    /**
     * returns all google calendars with their events attached
     * response is cached for 1 day
     *
     * @param Request|null $request
     * @return Collection
     */
    public function calendars(Request $request = null): Collection
    {
        return Cache::remember('google_calendars', 86400, function () {
            return collect(GoogleCalendarService::listAllCalendars()->getItems())
                ->map(function ($calendar) {
                    $calendar->events = GoogleCalendarService::listEvents($calendar->id);

                    return $calendar;
                })
                ->values();
        });
    }

    /**
     * returns all google calendars with their events attached
     * response is cached for 1 day
     *
     * @param Request|null $request
     * @return Collection
     */
    public function refreshCache(Request $request = null): Collection
    {
        Cache::forget('google_calendars');

        return $this->calendars();
    }

    /**
     * creates a google calendar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCalendar(Request $request): JsonResponse
    {
        Cache::forget('google_calendars');

        $calendar = GoogleCalendarService::createCalendarFromRequest($request);

        return response()->json([
            'calendar' => (array) $calendar,
            'calendars' => $this->calendars(),
        ]);
    }

    /**
     * creates a google calendar event
     *
     * @param GoogleCalendarEventRequest $request
     * @return JsonResponse
     */
    public function createEvent(GoogleCalendarEventRequest $request): JsonResponse
    {
        Cache::forget('google_calendars');

        $event = GoogleCalendarService::createEventFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars(),
        ]);
    }

    /**
     * updates a google calendar event
     *
     * @param GoogleCalendarEventRequest $request
     * @return JsonResponse
     */
    public function updateEvent(GoogleCalendarEventRequest $request): JsonResponse
    {
        Cache::forget('google_calendars');

        $event = GoogleCalendarService::updateEventFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars(),
        ]);
    }

    /**
     * deletes a google calendar event
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteEvent(Request $request): JsonResponse
    {
        Cache::forget('google_calendars');

        return response()->json([
            'response' => GoogleCalendarService::deleteEventFromRequest($request),
            'calendars' => $this->calendars(),
        ]);
    }

    /**
     * adds and attendee to a google calendar event
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addAttendeeToEvent(Request $request): JsonResponse
    {
        Cache::forget('google_calendars');

        $event = GoogleCalendarService::addAttendeeFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars(),
        ]);
    }

    /**
     * updates a google calendar event attendee
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAttendee(Request $request): JsonResponse
    {
        Cache::forget('google_calendars');

        $event = GoogleCalendarService::updateAttendeeFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars(),
        ]);
    }

    /**
     * download google calendar attendees in an excel spreadsheet
     *
     * @param Request $request
     * @return AttendeeExport
     */
    public function downloadAttendees(Request $request): AttendeeExport
    {
        $request->validate([
            'calendar_id' => 'nullable|string',
            'event_id' => 'nullable|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $start = $request->start ? Carbon::create($request->start) : null;
        $end = $request->end ? Carbon::create($request->end) : null;

        return new AttendeeExport($request->calendar_id, $request->event_id, $start, $end);
    }
}
