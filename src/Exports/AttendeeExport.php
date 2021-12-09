<?php


namespace GTerrusa\LaravelGoogleCalendar\Exports;


use GTerrusa\LaravelGoogleCalendar\LaravelGoogleCalendar as GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;

class AttendeeExport implements FromCollection, WithHeadings, Responsable
{
    use Exportable;

    private ?string $calendarId, $eventId;
    private ?Carbon $start, $end;
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
            ? GoogleCalendarService::listEvents($this->calendarId)->first(function ($e) { return $e->id === $this->eventId; })
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
        $events = GoogleCalendarService::listEvents($this->calendarId);

        if ($this->eventId) {
            $events = $events->filter(function ($event) {
                return $event->id === $this->eventId;
            });
        }

        if ($this->start) {
            $events = $events->filter(function ($event) {
                $eventStart = Carbon::create($event->start->date ?? $event->start->dateTime)->startOfDay();
                return $eventStart->greaterThanOrEqualTo($this->start->startOfDay());
            });
        }

        if ($this->end) {
            $events = $events->filter(function ($event) {
                $eventEnd = Carbon::create($event->end->date ?? $event->end->dateTime)->startOfDay();
                return $eventEnd->lessThanOrEqualTo($this->end->startOfDay());
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
                    'response_status' => $attendee->responseStatus
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
            'attendee_response_status'
        ];
    }
}
