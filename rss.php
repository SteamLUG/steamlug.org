<?php

	header("Content-Type: application/rss+xml");
	header("Access-Control-Allow-Origin: *");

	include_once("includes/lastRSS.php");

	echo "<?xml version='1.0'?>\n";
	echo "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n";
	echo "<channel>\n";
	echo "\t<title>";
	echo "twolofbees.com - Pretty pictures, cute comics and love letters.";
	echo "</title>\n";
	echo "\t<description>";
	echo "Latest artwork and blog entries from Cheese and Mim of twolofbees.com.";
	echo "</description>\n";
	echo "\t<language>";
	echo "en-au";
	echo "</language>\n";
	echo "\t<copyright>";
	echo "Copyright " . date("Y") . " twolofbees.com";
	echo "</copyright>\n";
	echo "\t<link>";
	echo "http://twolofbees.com/";
	echo "</link>\n";
	echo "\t<managingEditor>";
	echo "contact@twolofbees.com (Mim)";
	echo "</managingEditor>\n";
	echo "\t<lastBuildDate>";
	echo date("r");
	echo "</lastBuildDate>\n";
	echo "\t<atom:link href='http://steamlug.org/rss.php type='application/rss+xml' />";
	echo "\t<pubDate>";
	echo date("r");
	echo "</pubDate>\n";
	$rss = new lastRSS;
	$rss->cache_dir = './temp';
	$rss->cache_time = 1200;
	$rss->CDATA = 'content';
	$rss->items_limit = 6;
	$rssString = "";
	if ($rs = $rss->get('http://steamcommunity.com/groups/steamlug/rss'))
	{
		foreach($rs['items'] as $item)
		{
			if (preg_match("/steamlug\/events\//", $item['link']))
			{
				$item['description'] = str_replace(array("\r", "\r\n"), "\n", $item['description']);
				$item['description'] = str_replace(" onclick=\"return AlertNonSteamSite( this );\"", "", $item['description']);
				$item['description'] = str_replace(" class=\"bb_link\"", "", $item['description']);
				$item['description'] = str_replace(" class=\"bb_ul\"", "", $item['description']);
				$item['description'] = str_replace("<br><", "<", $item['description']);
				$item['description'] = str_replace("<i>", "<em>", $item['description']);
				$item['description'] = str_replace("</i>", "</em>", $item['description']);
				$item['description'] = str_replace("<b>", "<strong>", $item['description']);
				$item['description'] = str_replace("</b>", "</strong>", $item['description']);
				$item['description'] = str_replace("<br>-----", "-----", $item['description']);
				$item['description'] = str_replace("<br>\n<br>", "</p><p>", $item['description']);
				$item['description'] = str_replace("</ul>\n\n<br>", "</ul>\n<p>", $item['description']);
				$item['description'] = str_replace("<ul>", "</p>\n<ul>", $item['description']);
				$item['description'] = str_replace("<br>", "<br />", $item['description']);

				echo "<item>";
					echo "<title>" . $item['title'] . "</title>";
					echo "<link>" . $item['title'] . "</link>";
					echo "<description>" . $item['description'] . "</description>";
					echo "<author>" . $item['author'] . "</author>";
					echo "<pubDate>" .  $item['pubDate'] ."</pubDate>";
					echo "<guid>" . $item['link'] . "</guid>";
					echo "<category>Event</category>";
				echo "</item>";
			
			}
		}
	}
	
	echo "</channel>";
	echo "</rss>";
?>
