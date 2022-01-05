<?php

namespace GTerrusa\LaravelGoogleCalendar\Http\Controllers;

use Carbon\Carbon;
use GTerrusa\LaravelGoogleCalendar\Exports\AttendeeExport;
use GTerrusa\LaravelGoogleCalendar\Http\Requests\GoogleCalendarEventRequest;
use GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendar as GoogleCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GoogleCalendarController extends Controller
{
    /**
     * returns all google calendars with their events attached
     * response is cached for 1 day
     *
     * @param Request|null $request
     * @return Collection
     */
    public function calendars(Request $request): Collection
    {
        return collect(GoogleCalendarService::listAllCalendars()->getItems())
            ->map(function ($calendar) use ($request) {
                $calendar->events = GoogleCalendarService::listEvents(
                    $calendar->id,
                    $request->start ?? null,
                    $request->end ?? null
                );

                return $calendar;
            })
            ->values();
    }

    /**
     * creates a google calendar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCalendar(Request $request): JsonResponse
    {
        $calendar = GoogleCalendarService::createCalendarFromRequest($request);

        return response()->json([
            'calendar' => (array) $calendar,
            'calendars' => $this->calendars($request),
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
        $event = GoogleCalendarService::createEventFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars($request),
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
        $event = GoogleCalendarService::updateEventFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars($request),
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
        return response()->json([
            'response' => GoogleCalendarService::deleteEventFromRequest($request),
            'calendars' => $this->calendars($request),
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
        $event = GoogleCalendarService::addAttendeeFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars($request),
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
        $event = GoogleCalendarService::updateAttendeeFromRequest($request);

        return response()->json([
            'event' => (array) $event,
            'calendars' => $this->calendars($request),
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
