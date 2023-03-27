<?php

use GTerrusa\LaravelGoogleCalendar\Http\Controllers\GoogleCalendarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([
    'prefix' => 'api/google-calendar',
    'middleware' => ['api']
], function() {
    // get requests
    Route::get('/calendars', [GoogleCalendarController::class, 'calendars']);

    // post requests
    Route::post('/calendars/create', [GoogleCalendarController::class, 'createCalendar']);
    Route::post('/calendars/update', [GoogleCalendarController::class, 'updateCalendar']);
    Route::post('/calendars/delete', [GoogleCalendarController::class, 'deleteCalendar']);
    Route::post('/calendars/events/create', [GoogleCalendarController::class, 'createEvent']);
    Route::post('/calendars/events/update', [GoogleCalendarController::class, 'updateEvent']);
    Route::post('/calendars/events/delete', [GoogleCalendarController::class, 'deleteEvent']);
    Route::post('/calendars/events/attendees/add', [GoogleCalendarController::class, 'addAttendeeToEvent']);
    Route::post('/calendars/events/attendees/remove', [GoogleCalendarController::class, 'removeAttendee']);
    Route::post('/calendars/events/attendees/update', [GoogleCalendarController::class, 'updateAttendee']);
    Route::post('/calendars/events/attendees/download', [GoogleCalendarController::class, 'downloadAttendees']);
});
