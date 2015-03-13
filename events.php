<?php
$pageTitle = "Events";
include_once( 'includes/paths.php' );
include_once( 'includes/functions_events.php' );

$event = getNextEvent( );
$data = getRecentEvents( );
if ($event != null) {
	$eTime = $event['utctime'];
	if (isset($eTime))
		$extraJS = "\t\t\tvar target = new Date(" . $eTime . ");";
}

$externalJS = array('/scripts/events.js');
include_once( 'includes/header.php' );
?>
		<h1 class="text-center">SteamLUG Events</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Next Event</h3>
			</header>
			<div class="panel-body">
			<div class="col-md-5 clearfix">
<?php
$eventButton = "";
$eventImage = "";
$eventTitle = "";
if ($event != null) {
	// TODO: tidy this mess, the next block, and the HEREDOC into one clean thing
	$eventTitle = '<h3 class="centred"><a href="' . $event['url'] . '">' .  str_replace( 'SteamLUG ','',$event['title'] ) . '</a></h3>';
	($event['appid'] !== 0 ?
	$eventImage = "<a href='" . $event['url'] . "'><img class=\"img-rounded eventimage\" src='" . $event['img_header'] . "' alt='" . $event['title'] . "'/></a>" :
	$eventImage = "<h1>?</h1>"
	);
	$eventButton = "<p><a class=\"btn btn-primary btn-lg pull-right\" href=\"" . $event['url'] . "\">Click for details</a></p>";
	$dt = $event['date'] . " " . $event['time'] . " " . $event['tz'];
}

if (isset($eTime)) {
	$eventDate = new DateTime(); $eventDate->setTimestamp($eTime);
	$diff = date_diff($eventDate, new DateTime("now"));
	list($ed, $eh, $em, $es) = explode( ' ', $diff->format("%D %H %I %S") );
}

echo <<<EVENTSHEAD
				{$eventTitle}
				<div id="countdown">
					<span class="label">Days</span>
					<span id="d1">{$ed[0]}</span>
					<span id="d2">{$ed[1]}</span>
					<span class="label">&nbsp;</span>
					<span id="h1">{$eh[0]}</span>
					<span id="h2">{$eh[1]}</span>
					<span class="label">:</span>
					<span id="m1">{$em[0]}</span>
					<span id="m2">{$em[1]}</span>
					<span class="label">:</span>
					<span id="s1">{$es[0]}</span>
					<span id="s2">{$es[1]}</span>
				</div>
				<p>This event is held on {$dt}</p>
				{$eventButton}
			</div>
			<div class="col-md-7">
					{$eventImage}
			</div>
			</div>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">About</h3>
			</header>
			<div class="panel-body">
			<p>Here you can find a list of upcoming group gaming events hosted by the SteamLUG community. A countdown timer is shown for the next upcoming event. We also have a <a href = '/feed/events'>RSS feed</a> of event reminders available.</p>
			<p>All times are listed in UTC, and are subject to change.</p>
			<p>Click on an event title to post comments, find more information, and retrieve server passwords (for this, you will need to become a group member by selecting the Join Group button on the event page).</p>
			<p>If you'd like to know more about our community, visit the <a href = 'about' >About page</a>, or hop into our <a href = 'irc'>IRC channel</a> and say hi. If you'd like to get involved with organising events, please contact <a href = 'http://twitter.com/steamlug' >steamlug</a>.</p>

			<h4>Mumble</h4>
			<p>We also run a <a href = 'http://mumble.sourceforge.net/' >Mumble</a> voice chat server which we use in place of in-game voice chat. You can learn more about it on our <a href = 'mumble' >Mumble page</a>.</p>
			<p>We encourage our users to use the Mumble server during the events, if there should be any important messages to be announced and to make team-based games easier to manage. You can just sit and listen.</p>
			</div>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Upcoming Events</h3>
			</header>
			<div class="panel-body panel-body-table">
			<table class="table table-striped table-hover events">
			<thead>
				<tr>
					<th class="col-sm-1">
					<th>Event Name
					<th class="col-sm-2">Comments
					<th class="col-sm-2">Timestamp
				</tr>
			</thead>
			<tbody>
EVENTSHEAD;

	foreach ($data['events'] as $event)
	{
		// skip if it's a special (non-game/non-app) event
		if ($event["appid"] === 0) {
			continue;
		}
		$comments = ($event['comments'] > "0" ? "<a href=\"{$event['url']}\">" . $event['comments'] . " " . ($event['comments'] == "1" ? "comment…" : "comments…") . "</a>	" : "");
		echo <<<EVENTSTRING
			<tr>
				<td><img class="eventLogo" src="{$event['img_capsule']}" alt="{$event['title']}" ></td>
				<td><a href="{$event['url']}">{$event['title']}</a></td>
				<td>{$comments}</td>
				<td>{$event['date']} {$event['time']} {$event['tz']}</td>
			</tr>
EVENTSTRING;
	}
?>
			</tbody>
			</table>
			</div>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Past Events</h3>
			</header>
			<div class="panel-body panel-body-table">
			<table class="table table-striped table-hover events">
			<thead>
				<tr>
					<th class="col-sm-1">
					<th>Event Name
					<th class="col-sm-2">Comments
					<th class="col-sm-2">Timestamp
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($data['pastevents'] as $event)
	{
		// skip if it's a special (non-game/non-app) event
		if ($event["appid"] === 0) {
			continue;
		}
		$comments = ($event['comments'] > "0" ? "<a href=\"{$event['url']}\">" . $event['comments'] . " " . ($event['comments'] == "1" ? "comment…" : "comments…") . "</a>" : "");
		echo <<<EVENTSTRING
			<tr>
				<td><img class="eventLogo" src="{$event['img_capsule']}" alt="{$event['title']}" ></td>
				<td><a href="{$event['url']}">{$event['title']}</a></td>
				<td>{$comments}</td>
				<td>{$event['date']} {$event['time']} {$event['tz']}</td>
			</tr>

EVENTSTRING;


	}
?>
			</tbody>
			</table>
			</div>
		</article>
<?php include_once('includes/footer.php'); ?>

