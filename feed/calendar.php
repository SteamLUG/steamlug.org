<?php
header('Content-type: text/calendar; charset=utf-8');
header("Content-Disposition: inline; filename=steamlug-events.ics" );
date_default_timezone_set('UTC');

echo <<<HEADER
BEGIN:VCALENDAR
VERSION:2.0
PRODID:https://steamlug.org

HEADER;
include_once( '../includes/functions_events.php' );
$data = getRecentEvents( );
foreach ($data['events'] as $event) {

	/* Ignore SteamLUG cast? q.q */
	/* TODO: sort out steamlugcast & non-gaming events for this */
	if ( $event['appid'] === 0 ) { continue; }

	$eventTime = strtotime( $event['date'] . ' ' . $event['time'] );
	$eventStart = date( 'Ymd\THis\Z', $eventTime );
	$eventCreation = date( 'Ymd\THis\Z', $eventTime - 604800 );
	$eventEnd = date( 'Ymd\THis\Z', $eventTime + 7200 );

	echo <<<EVENTHERE
BEGIN:VEVENT
UID:{$event['id']}@steamlug.org
DTSTAMP:{$eventCreation}
DTSTART:{$eventStart}
DTEND:{$eventEnd}
SUMMARY:{$event['title']}
URL:https://steamcommunity.com/groups/steamlug#events/{$event['id']}
BEGIN:VALARM
TRIGGER:-PT60M
ACTION:DISPLAY
DESCRIPTION:Reminder to play {$event['title']}
END:VALARM
END:VEVENT

EVENTHERE;
}

echo <<<FOOTER
END:VCALENDAR
FOOTER;
