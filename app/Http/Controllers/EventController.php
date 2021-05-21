<?php

namespace App\Http\Controllers;

// use App\Event;
use Carbon\Carbon;
use App\Services\Google;
use Spatie\GoogleCalendar\Event;
use Google_Service_Calendar_Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $events = auth()->user()->events()
            ->orderBy('started_at', 'desc')
            ->get();

        return view('events', compact('events'));
    }

    public function create(Google $google)
    {
        $google_account = auth()->user()->googleAccount;
        $token = $google_account->token;
        $google_client_token = [
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'],
            'expires_in' => $token['expires_in']
        ];
        $google->setAccessToken(json_encode($google_client_token));
        if ($google->isAccessTokenExpired()) {
            $accessToken = $google->fetchAccessTokenWithRefreshToken($google->getRefreshToken());
            $google_account->update([
                'token' => $accessToken,
            ]);
        }
        $service = $google->service('Calendar');
        $calendarId = 'primary';
        
        $event = new Google_Service_Calendar_Event(array(
            'summary' => 'Google I/O 2015',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
              'dateTime' => '2021-05-23T09:00:00-07:00',
              'timeZone' => 'America/Los_Angeles',
            ),
            'end' => array(
              'dateTime' => '2021-05-23T17:00:00-07:00',
              'timeZone' => 'America/Los_Angeles',
            ),
            'recurrence' => array(
              'RRULE:FREQ=DAILY;COUNT=2'
            ),
            'attendees' => array(
              array('email' => '')
            ),
            'reminders' => array(
              'useDefault' => FALSE,
              'overrides' => array(
                array('method' => 'email', 'minutes' => 24 * 60),
                array('method' => 'popup', 'minutes' => 10),
              ),
            ),
        ));
        $event = $service->events->insert($calendarId, $event);
        printf('Event created: %s\n', $event->htmlLink);
        return view('events_create');
    }
}
