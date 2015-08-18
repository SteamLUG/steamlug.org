<?php
date_default_timezone_set('UTC');
include_once( 'paths.php' );
require_once( 'rbt_prs.php' );
require_once( 'steameventparser.php' );
/* XXX eventid ~= 204128765604998122 or eventid == '' */
$eventID = isset($_GET["eventid"]) ? $_GET["eventid"] : "0";

$parser = new SteamEventParser();

$month = gmstrftime("%m")-0;
$year  = gmstrftime("%Y");

$present	= $parser->genData($eventXMLPath, "steamlug", $month, $year);
$future		= $parser->genData($eventXMLPath, "steamlug", ( $month >= 12 ? 1 : ( $month +1 ) ), ( $month >= 12 ? ( $year + 1 ) : $year ));

// cast.php needs 1 upcoming‐event (if appid == 0)
// stream.php needs 1 upcoming-event (any appid)
// for now, let us do appid == 0 | appid != 0
/*
	This function will load the current and next month (in case we near the end) XML
	event files, parse and merge the fields, and then return the first match
	if castOnly is set true, it will only return an event of appid 0
	if gracePeriod is not null, return the last event if it is newer than $gracePeriod seconds
*/
function getNextEvent( $castOnly = false, $gracePeriod = null ) {

	global $present, $future;
	$events = array();
	$events = array_merge($events, $present['events'], $future['events']);
	$filter = function ($event) use ($castOnly) {
		if ( $castOnly and ($event["appid"] !== 0 || strpos($event["title"], "Cast") === false) ) {
			return null;
		}
		if ( !$castOnly and ($event["appid"] === 0) ) {
			return null;
		}
		$d = explode("-", $event['date']);
		$t = explode(":", $event['time']);
		$event['utctime'] = strtotime($d[0] . "-" . $d[1] . "-" . $d[2] . 'T' . $t[0] . ':' . $t[1] . 'Z');
		return $event;
	};
	if ( $gracePeriod !== null) {
		$now = time();
		foreach ($present['pastevents'] as $event) {
			$event = $filter($event);
			if($event === null) {
				continue;
			}
			if($now - $event['utctime'] > $gracePeriod) {
				break;
			}
			return $event;
		}
	}
	foreach ($events as $event) {
		$event = $filter($event);
		if($event !== null) {
			return $event;
		}
	}
	return null;
}

// events needs many upcoming, many past (if appid !== 0)
/*
	This function will load the current, next, and prev month XML event files, parse
	and merge the field, and then return a hash with events and pastevents keys
*/
function getRecentEvents( ) {

	global $present, $future;
	global $month, $year;
	global $parser, $eventXMLPath;
	$past		= $parser->genData($eventXMLPath, "steamlug", ( $month <= 1 ? 12 : ( $month -1 ) ), ( $month <= 1 ? ( $year -1 ) : $year ));

	$present['events'] = array_merge($present['events'], $future['events']);
	$present['pastevents'] = array_merge($present['pastevents'], $past['pastevents']);
	return $present;
}

/* json.php wants… just present month XML, so give it  */
function getMonthsEvents() {

	global $present;
	return $present;
}

/* Pass in a Steam event ID, get the event data. Helper function, so that in future we can swap the backend for a db? :^) */
function findEvent( $id ) {

	global $present, $future;
	$events = array_merge($present['events'], $future['events']);
	foreach ($events as $event) {

		if ( $event["id"] == $id ) {
			$d = explode("-", $event['date']);
			$t = explode(":", $event['time']);
			$event['utctime'] = strtotime($d[0] . "-" . $d[1] . "-" . $d[2] . 'T' . $t[0] . ':' . $t[1] . 'Z');
			return $event;
		}
	}
	/* TODO if we get here, we are likely a legacy event and need to dig into the older files… */
}
