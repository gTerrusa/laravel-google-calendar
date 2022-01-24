<?php

namespace GTerrusa\LaravelGoogleCalendar\Http\Controllers;

use Carbon\Carbon;
use GTerrusa\LaravelGoogleCalendar\Exports\AttendeeExport;
use GTerrusa\LaravelGoogleCalendar\Http\Requests\GoogleCalendarEventRequest;
use GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendar;
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
        return collect(LaravelGoogleCalendar::listAllCalendars()->getItems())
            ->map(function ($calendar) use ($request) {
                $calendar->events = LaravelGoogleCalendar::listEvents(
                    $calendar->id,
                    $request->event_list_start ?? null,
                    $request->event_list_end ?? null
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
        $validated = $request->validate([
            'description' => 'string_or_array|nullable',
            'location' => 'string|nullable',
            'summary' => 'string|nullable',
            'timeZone' => 'string|nullable',
            'conferenceProperties' => 'array|nullable',
        ]);

        if (isset($validated['description']) && is_array($validated['description'])) {
            $validated['description'] = json_encode($validated['description']);
        }

        $newCalendar = LaravelGoogleCalendar::createCalendar($validated);

        if ($notificationSettings = config('google-calendar.notification_settings', false)) {
            $service = LaravelGoogleCalendar::getGoogleCalendarService();
            $cList = $service->calendarList->get($newCalendar->getId());
            $cList->notificationSettings = $notificationSettings;
            $service->calendarList->update($cList->getId(), $cList);
        }

        return response()->json([
            'calendar' => $newCalendar,
            'calendars' => $this->calendars($request),
        ]);
    }

    /**
     * updates a google calendar.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCalendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'string|required',
            'description' => 'string_or_array|nullable',
            'location' => 'string|nullable',
            'summary' => 'string|nullable',
            'timeZone' => 'string|nullable',
            'conferenceProperties' => 'array|nullable',
        ]);

        if (isset($validated['description']) && is_array($validated['description'])) {
            $validated['description'] = json_encode($validated['description']);
        }

        $updatedCalendar = LaravelGoogleCalendar::updateCalendar($validated['id'], $validated);

        return response()->json([
            'calendar' => $updatedCalendar,
            'calendars' => $this->calendars($request),
        ]);
    }

    /**
     * deletes a google calendar.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCalendar(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'string|required',
        ]);

        LaravelGoogleCalendar::deleteCalendar($request->id);

        return response()->json([
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
        $event = LaravelGoogleCalendar::createEventFromRequest($request);

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
        $event = LaravelGoogleCalendar::updateEventFromRequest($request);

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
            'response' => LaravelGoogleCalendar::deleteEventFromRequest($request),
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
        $event = LaravelGoogleCalendar::addAttendeeFromRequest($request);

        if (! $event) {
            return response()->json([
                'message' => 'Event is full',
            ], 400);
        }

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
        $event = LaravelGoogleCalendar::updateAttendeeFromRequest($request);

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
