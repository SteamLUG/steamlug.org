<?php

	header("Content-Type: application/rss+xml");
	header("Access-Control-Allow-Origin: *");
	require_once("rbt_prs.php");
	require_once("steameventparser.php");

	echo "<?xml version='1.0'?>\n";
	echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n";
	echo "<channel>\n";
	echo "\t<title>";
	echo "SteamLUG events!.";
	echo "</title>\n";
	echo "\t<description>";
	echo "This feed contains a list of SteamLUG game events.";
	echo "</description>\n";
	echo "\t<language>";
	echo "en-au";
	echo "</language>\n";
	echo "\t<copyright>";
	echo "Copyright " . date("Y") . " SteamLUG community";
	echo "</copyright>\n";
	echo "\t<link>";
	echo "http://steamlug.org";
	echo "</link>\n";
	echo "\t<managingEditor>";
	echo "steamlug@gmail.com (SteamLUG)";
	echo "</managingEditor>\n";
	echo "\t<lastBuildDate>";
	echo date("r");
	echo "</lastBuildDate>\n";
	echo "\t<atom:link href='http://steamlug.org/rss.php' rel = 'self' type='application/rss+xml' />\n";
	echo "\t<pubDate>";
	echo date("r");
	echo "</pubDate>\n";


	$parser = new SteamEventParser();
	$data = $parser->genData("steamlug");
	$d = explode("-", $data['events'][0]['date']);
	$t = explode(":", $data['events'][0]['time']);
	$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";

	$month = gmstrftime("%m");
	$year = gmstrftime("%Y");
	$data2 = $parser->genData("steamlug", $month >= 12 ? 1: $month +1, $month >= 12 ? $year + 1: $year);

	$data['events'] = array_merge($data['events'], $data2['events']);
	$timezone = new DateTimeZone('UTC'); 
	$nowDate = new DateTime("now", $timezone);
	foreach($data['events'] as $event)
	{

		//TODO: We probably should be using whatever timezone the events were using to begin with
		$tempDate = new DateTime($event['date'] . " " . $event['time'], $timezone);
		echo $tempDate->format("D, d M Y H:i:s O");
		$timeLeft = "Under an hour";
		if ($tempDate->sub(new DateInterval("PT1H")) > $nowDate)
		{
		echo $tempDate->format("D, d M Y H:i:s O");
			$timeLeft = "Under 24 hours";
			if ($tempDate->sub(new DateInterval("PT23H")) > $nowDate)
			{
		echo $tempDate->format("D, d M Y H:i:s O");
				$timeLeft = "Under 1 week";
				if ($tempDate->sub(new DateInterval("P6D")) > $nowDate)
				{
		echo $tempDate->format("D, d M Y H:i:s O");
					$timeLeft = "Ages ;_;";
					continue;			
				}
			}
		}
	
		echo "<item>\n";
			echo "<title>" . $event['title'] . "</title>\n";
			echo "<link>" . $event['url'] . "</link>\n";
			echo "<description>" . $event['title'] . " at " . $event['date'] . " " . $event['time'] . " " . $event['tz'] . " (" . $timeLeft. " away)</description>\n";
			echo "<author>steamlug@gmail.com (SteamLUG)</author>\n";
			echo "<pubDate>". $tempDate->format("D, d M Y H:i:s O") . "</pubDate>\n";
			echo "<guid>" . $event['url'] . "</guid>\n";
			echo "<category>Event</category>\n";
		echo "</item>\n";
		
		/*
				$eventString = "\t\t\t<li>\n";

		$eventString .= "\t\t\t\t<img class = 'eventLogo' src = '" . $event["img_small"] . "' alt = " . $event["title"] . ">\n";
		$eventString .= "\t\t\t\t<a class = 'eventName' href = '" . $event["url"] . "'>" . $event["title"] . "</a><span class = 'eventDate'>" . $event['date'] . " " . $event['time'] . " " . $event['tz'] . "</span>\n";

		$eventString .= "\t\t\t</li>\n";
		echo $eventString;*/
	}
	
	echo "</channel>";
	echo "</rss>";
?>
