<?php
	header("Content-Type: application/rss+xml");
	header("Access-Control-Allow-Origin: *");
	require_once("../rbt_prs.php");
	require_once("../steameventparser.php");

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
	$month = gmstrftime("%m");
	$year = gmstrftime("%Y");
	$data = $parser->genData("steamlug", $month, $year);
	$data2 = $parser->genData("steamlug", $month >= 12 ? 1: $month +1, $month >= 12 ? $year + 1: $year);
	$data['events'] = array_merge($data['events'], $data2['events']);
	
	$d = explode("-", $data['events'][0]['date']);
	$t = explode(":", $data['events'][0]['time']);
	$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";
	
	$timezone = new DateTimeZone('UTC'); 
	$nowDate = new DateTime("now", $timezone);
	foreach($data['events'] as $event)
	{
		//TODO: We probably should be using whatever timezone the events were using to begin with
		$tempDate = new DateTime($event['date'] . " " . $event['time'], $timezone);
		$timeLeft = "1 hour";
		if ($tempDate->sub(new DateInterval("PT1H")) > $nowDate)
		{
			$timeLeft = "24 hours";
			if ($tempDate->sub(new DateInterval("PT23H")) > $nowDate)
			{
				$timeLeft = "1 week";
				if ($tempDate->sub(new DateInterval("P6D")) > $nowDate)
				{
					$timeLeft = "2 weeks";
					if ($tempDate->sub(new DateInterval("P7D")) > $nowDate)
					{
						$timeLeft = "Ages ;_;";
						continue;			
					}
				}
			}
		}
	
		echo "<item>\n";
			echo "<title>" . $event['title'] . "</title>\n";
			echo "<link>" . $event['url'] . "</link>\n";
			echo "<description>";
			echo "&lt;img width='292' height='136' src='" . $event['img_header_small'] . "' alt='" . $event['title'] . "'/&gt;";
			echo "&lt;p&gt;A reminder that " . $event['title'] . " will be on " . $event['date'] . " at " . $event['time'] . " " . $event['tz'] . " (" . $timeLeft. " away)&lt;/p&gt;</description>\n";
			echo "<author>steamlug@gmail.com (SteamLUG)</author>\n";
			echo "<pubDate>". $tempDate->format("D, d M Y H:i:s O") . "</pubDate>\n";
			echo "<guid>" . $event['url'] . "</guid>\n";
			echo "<category>Event</category>\n";
		echo "</item>\n";
	}
	
	echo "</channel>";
	echo "</rss>";
?>
