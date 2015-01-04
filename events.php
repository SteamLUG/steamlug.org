<?php
$pageTitle = "Events";

require_once( "rbt_prs.php" );
require_once( "steameventparser.php" );

$parser = new SteamEventParser();

$month = gmstrftime("%m")-0;
$year  = gmstrftime("%Y");
$data  = $parser->genData("steamlug", $month, $year);
$data2 = $parser->genData("steamlug", ( $month >= 12 ? 1 : ( $month +1 ) ), ( $month >= 12 ? ( $year + 1 ) : $year ));
$data3 = $parser->genData("steamlug", ( $month <= 1 ? 12 : ( $month -1 ) ), ( $month <= 1 ? ( $year -1 ) : $year ));

$data['events'] = array_merge($data['events'], $data2['events']);
$data['pastevents'] = array_merge($data['pastevents'], $data3['pastevents']);

foreach ($data['events'] as $event)
	{
		if ($event['appid'] === 0)
		{
			continue;
		}
	$d = explode("-", $event['date']);
	$t = explode(":", $event['time']);

	break;
}

$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";
$extraJS = $dateString;
$externalJS = array('/scripts/events.js');

include_once( "includes/header.php" );
?>
		<h1 class="text-center">SteamLUG Events</h1>
		<div class="jumbotron">
			<h2>Next Event</h2>
			<div class="row">
			<div class="col-md-6">
<?php
$eventButton = "";
$eventImage = "";
$eventTitle = "";
foreach ($data['events'] as $event)
{
	$event['img_header'] = "//steamcdn-a.akamaihd.net/steam/apps/" . $event["appid"] . "/header.jpg";
	if ($event['appid'] === 0)
	{
		continue;
	}

	$eventTitle = "\t\t\t\t<h2><a href='" . $event['url'] . "'>" .  $event['title'] . "</a></h2>";
	($event['appid'] !== 0 ? 
			$eventImage = "\t\t\t\t\t<a href='" . $event['url'] . "'><img class=\"img-rounded eventimage\" src='" . $event['img_header'] . "' alt='" . $event['title'] . "'/></a>\n" :
			$eventImage = "\t\t\t\t\t<h1>?</h1>\n"
	);
			$eventButton = "\t\t\t\t<p><a class=\"btn btn-primary btn-lg\" href=\"" . $event['url'] . "\">Click for details</a></p>\n";
	break;
}
?>
			<?php echo $eventTitle; ?>
			<p>
				<div class="btn-group">
					<span class="btn btn-primary btn-sm">Days</span>
					<span id="d1" class="btn btn-default btn-sm">0</span>
					<span id="d2" class="btn btn-default btn-sm">0</span>
					<span class="btn btn-primary btn-sm">Hours</span>
					<span id="h1" class="btn btn-default btn-sm">0</span>
					<span id="h2" class="btn btn-default btn-sm">0</span>
					<span class="btn btn-primary btn-sm">Minutes</span>
					<span id="m1" class="btn btn-default btn-sm">0</span>
					<span id="m2" class="btn btn-default btn-sm">0</span>
					<span class="btn btn-primary btn-sm">Seconds</span>
					<span id="s1" class="btn btn-default btn-sm">0</span>
					<span id="s2" class="btn btn-default btn-sm">0</span>
				</div>
			</p>
			</div>
			<div class="col-md-6">
					<?php echo $eventImage; ?>
			</div>
			</div>
			<?php echo $eventButton; ?>
		</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">About</h3>
				</div>
				<div class="panel-body">
				<p>Welcome to the SteamLUG events page!</p>
				<p>Here you can find a list of upcoming group gaming events hosted by the SteamLUG community. A countdown timer is shown above for the next upcoming event. We also have a <a href = '/feed/events'>RSS feed</a> of event reminders available.</p>
				<p>All times are listed in UTC, and are subject to change.</p>
				<p>Click on an event title to post comments, find more information, and retrieve server passwords (for this, you will need to become a group member by clicking the Join Group button on the upper right of any of the event pages).</p>
				<p>If you'd like to know more about our community, visit the <a href = 'about' >About page</a>, or hop into our <a href = 'irc'>IRC channel</a> and say hi. If you'd like to get involved with organising SteamLUG events, please contact <a href = 'http://steamcommunity.com/id/swordfischer' >swordfischer</a>.</p>

				<h4>Mumble</h4>
				<p>We also run a <a href = 'http://mumble.sourceforge.net/' >Mumble</a> voice chat server which we use in place of in-game voice chat. You can learn more about it on our <a href = 'mumble' >Mumble page</a>.</p>
				<p>We encourage our users to use the Mumble server during the events, if there should be any important messages to be announced and to make team-based games easier to manage. You can just sit and listen.</p>
			</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Upcoming Events</h3>
				</div>
				<div class="panel-body">
			<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>
					<th>Event Name
					<th>Comments
					<th>Timestamp
				</tr>
			</thead>
			<tbody>

<?php
	foreach ($data['events'] as $event)
	{
		// skip if it's a special (non-game/non-app) event
		if ($event["appid"] === 0) {
			continue;
		}
		$eventString = "\t\t\t<tr>\n";
		$eventString .= "\t\t\t\t\t<td><img class = 'eventLogo' src = '" . $event['img_small'] . "' alt = '" . $event['title'] . "' >\n";
		$eventString .= "\t\t\t\t\t<td><a href = '" . $event['url'] . "' >" . $event['title'] . "</a>";
		$eventString .= "\t\t\t\t\t<td>" . ($event['comments'] > "0" ? "<a href = '" . $event['url'] . "' >" . $event['comments'] . " " . ($event['comments'] == "1" ? "comment.." : "comments..") . "</a>\t" : "");
		$eventString .= "\t\t\t\t\t<td>" . $event['date'] . " " . $event['time'] . " " . $event['tz'] . "\n";
		$eventString .= "\t\t\t</tr>\n";
		echo $eventString;
	}
?>
		</tbody>
		</table>
		</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Past Events</h3>
			</div>
			<div class="panel-body">
			<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>
					<th>Event Name
					<th>Comments
					<th>Timestamp
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

		$eventString = "\t\t\t<tr>\n";
		$eventString .= "\t\t\t\t\t<td><img class = 'eventLogo' src = '" . $event['img_small'] . "' alt = '" . $event['title'] . "' >\n";
		$eventString .= "\t\t\t\t\t<td><a href = '" . $event['url'] . "' >" . $event['title'] . "</a>";
		$eventString .= "\t\t\t\t\t<td>" . ($event['comments'] > "0" ? "<a href = '" . $event['url'] . "' >" . $event['comments'] . " " . ($event['comments'] == "1" ? "comment.." : "comments..") . "</a>\t" : "");
		$eventString .= "\t\t\t\t\t<td>" . $event['date'] . " " . $event['time'] . " " . $event['tz'] . "\n";
		$eventString .= "\t\t\t</tr>\n";
		echo $eventString;
	}
?>
		</tbody>
		</table>
		</div>
		</div>
<?php include_once("includes/footer.php"); ?>

