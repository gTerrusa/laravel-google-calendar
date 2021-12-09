<?php

namespace GTerrusa\LaravelGoogleCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoogleCalendarEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'nullable|string',
            'calendar_id' => 'nullable|string',
            'title' => 'required|string',
            'allDay' => 'required|boolean',
            'start' => 'required|string',
            'end' => 'required|string',
            'max_attendees' => 'required|numeric',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'recurrence' => 'nullable|array',
            'include_following' => 'nullable|boolean',
            'event_type' => Rule::in(['tour', 'event'])
        ];
    }
}
