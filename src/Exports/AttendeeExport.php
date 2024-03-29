<?php

namespace GTerrusa\LaravelGoogleCalendar\Exports;

use Carbon\Carbon;
use GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendar as GoogleCalendarService;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;

class AttendeeExport implements FromCollection, WithHeadings, Responsable
{
    use Exportable;

    private ?string $calendarId;
    private ?string $eventId;
    private ?Carbon $start;
    private ?Carbon $end;
    private \Google_Service_Calendar_CalendarListEntry $calendar;
    private string $fileName;
    private string $writerType = Excel::CSV;
    private array $headers = [
        'Content-Type' => 'text/csv',
    ];

    /**
     * AttendeeExport constructor
     *
     * @param string|null $calendarId
     * @param string|null $eventId
     */
    public function __construct(string $calendarId = null, string $eventId = null, Carbon $start = null, Carbon $end = null)
    {
        $this->calendarId = $calendarId ?? config('google-calendar.calendar_id');
        $this->eventId = $eventId;
        $this->start = $start;
        $this->end = $end;
        $this->calendar = GoogleCalendarService::getGoogleCalendarService()->calendarList->get($this->calendarId);
        $event = $this->eventId
            ? GoogleCalendarService::listEvents($this->calendarId)->first(function ($e) {
                return $e->id === $this->eventId;
            })
            : null;
        $this->fileName = $this->calendar->summary . '_' . ($event->summary ?? 'events') . '_attendees.csv';
    }

    /**
     * Collection to export
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        $events = GoogleCalendarService::listEvents($this->calendarId, $this->start ?? null, $this->end ?? null);

        if ($this->eventId) {
            $events = $events->filter(function ($event) {
                return $event->id === $this->eventId;
            });
        }

        $attendees = $events->flatMap(function ($event) {
            $attendees = collect($event->getAttendees());

            return $attendees->map(function ($attendee) use ($event) {
                return [
                    'calendar' => $this->calendar->primary ? 'Primary' : $this->calendar->summary,
                    'event' => $event->summary,
                    'event_date' => Carbon::create($event->start->date ?? $event->start->dateTime)->toDayDateTimeString(),
                    'name' => $attendee->displayName,
                    'email' => $attendee->email,
                    'response_status' => $attendee->responseStatus,
                ];
            });
        });

        return collect($attendees);
    }

    /**
     * Heading row for the export
     *
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'calendar',
            'event',
            'event_date',
            'attendee_name',
            'attendee_email',
            'attendee_response_status',
        ];
    }
}
