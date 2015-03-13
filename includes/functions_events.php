<?php
include_once( 'paths.php' );
require_once( 'rbt_prs.php' );
require_once( 'steameventparser.php' );

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
*/
function getNextEvent( $castOnly = false ) {

	global $present, $future;
	$events = array_merge($present['events'], $future['events']);
	foreach ($events as $event) {
		if ( $castOnly and ($event["appid"] !== 0 || strpos($event["title"], "Cast") === false) ) {
			continue;
		}
		if ( !$castOnly and ($event["appid"] === 0) ) {
			continue;
		}
		$d = explode("-", $event['date']);
		$t = explode(":", $event['time']);
		$event['utctime'] = strtotime($d[0] . "-" . $d[1] . "-" . $d[2] . 'T' . $t[0] . ':' . $t[1] . 'Z');
		return $event;
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
