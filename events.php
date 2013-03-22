<?php
require_once("rbt_prs.php");
require_once("steameventparser.php");
$parser = new SteamEventParser();
$data = $parser->genData("steamlug");
$d = explode("-", $data['events'][0]['date']);
$t = explode(":", $data['events'][0]['time']);
$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";

$month = gmstrftime("%m");
$year = gmstrftime("%Y");
$data2 = $parser->genData("steamlug", $month >= 12 ? 1: $month +1, $month >= 12 ? $year + 1: $year);
$data3 = $parser->genData("steamlug", $month <= 1 ? 12: $month -1, $month <= 1 ? $year -1: $year);

$data['events'] = array_merge($data['events'], $data2['events']);
$data['pastevents'] = array_merge($data['pastevents'], $data3['pastevents']);

$pageTitle = "Events";
$extraJS = $dateString;
$externalJS = array('scripts/events.js');
?>
<?php include_once("includes/header.php"); ?>
		<header>
			<hgroup>
				<h1>SteamLUG Events</h1>
			</hgroup>
		</header>
		<section>
			<article id='nextevent'>
				<h1>Next Event</h1>
<?php

	$eventString = "\t\t\t\t<h2><a href='" . $data["events"][0]["url"] . "'>" .  $data["events"][0]["title"] . "</a></h2>";
	$eventString .= "\t\t\t\t<a href='" . $data["events"][0]["url"] . "'>\n";
	$eventString .= "\t\t\t\t\t<img src='http://cdn.steampowered.com/v/gfx/apps/" . $data["events"][0]["appid"] . "/header.jpg' alt='SteamLUG Team Fortress 2'/>\n";
	$eventString .= "\t\t\t\t</a>\n";
	echo $eventString;
?>
				<div id="countdown">
					<div>Days<br />
						<span id="d1" class = "counterDigit">0</span>
						<span id="d2" class = "counterDigit">0</span>
					</div>
					<div>Hours<br />
						<span id="h1" class = "counterDigit">0</span>
						<span id="h2" class = "counterDigit">0</span>
					</div>
					<div>Minutes<br />
						<span id="m1" class = "counterDigit">0</span>
						<span id="m2" class = "counterDigit">0</span>
					</div>
					<div>Seconds<br />
						<span id="s1" class = "counterDigit">0</span>
						<span id="s2" class = "counterDigit">0</span>
					</div>
				</div>
			</article>
		</section>
		<section>
		<article id='about'>
			<div class="shadow">
				<h1>About</h1>
				<p>Welcome to the SteamLUG events page!</p>
				<p>Here you can find a list of upcoming group gaming events hosted by the SteamLUG community. A countdown timer is shown above for the next upcoming event.</p>
				<p>All times are listed in UTC, though we use the term "<em>Friturday</em>" to represent the overlap between Friday and Saturday around the world.</p>
				<p>Click on an event title to post comments, find more information, and retrieve server passwords (for this, you will need to become a group member by clicking the Join Group button on the upper right of any of the event pages).</p>
				<p>If you'd like to know more about our community, visit the <a href='about'>About page</a>, or hop into our <a href = 'chat'>IRC channel</a> and say hi. If you'd like to get involved with organising SteamLUG events, please see *** NEED A LINK HERE *** this discussion thread.</p>

				<h1>Mumble</h1>
<p>We also run a <a href = 'http://mumble.sourceforge.net/'>Mumble</a> voice chat server which we use in place of in-game voice chat. You can learn more about it on our <a href = 'mumble'>Mumble page</a>.
			</div>
		</article>
		<article id='main'>
		<div class="shadow">
<!--<pre>
<?php
print_r($data2);
?>
</pre>-->
		<h1>Upcoming Events</h1>
		<ul>

<?php
	foreach ($data['events'] as $event)
	{
		$eventString = "\t\t\t<li>\n";

		$eventString .= "\t\t\t\t<img class = 'eventLogo' src = '" . $event["img_small"] . "' alt = " . $event["title"] . ">\n";
		$eventString .= "\t\t\t\t<a class = 'eventName' href = '" . $event["url"] . "'>" . $event["title"] . "</a><span class = 'eventDate'>" . $event['date'] . " " . $event['time'] . " " . $event['tz'] . "</span>\n";

		$eventString .= "\t\t\t</li>\n";
		echo $eventString;
	}
?>
		</ul>
		<!--
			<li>
				<div class="eventLogo"><img src="" alt=""/></div>
				<div class="eventName"><a href="">SteamLUG FPS Friturday #</a><span class="eventDate">2013-00-00, 20:00 UTC</span></div>
			</li>
		!-->
		</div>
		</article>
		<article id='history'>
		<div class="shadow">
		<h1>Past Events</h1>
		<ul>

<?php
	foreach ($data['pastevents'] as $event)
	{
		$eventString = "\t\t\t<li>\n";

		$eventString .= "\t\t\t\t<img class = 'eventLogo' src = '" . $event["img_small"] . "' alt = " . $event["title"] . ">\n";
		$eventString .= "\t\t\t\t<a class = 'eventName' href = '" . $event["url"] . "'>" . $event["title"] . "</a><span class = 'eventDate'>" . $event['date'] . " " . $event['time'] . " " . $event['tz'] . "</span>\n";

		$eventString .= "\t\t\t</li>\n";
		echo $eventString;
	}
?>
		</ul>
		</div>
		</article>
		</section>
<?php include_once("includes/footer.php"); ?>

