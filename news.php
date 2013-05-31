<?php

$pageTitle = "News";
$externalJS = array('http://twitterjs.googlecode.com/svn/trunk/src/twitter.min.js');
$extraJS = "			getTwitters('tweet', { 
			id: 'steamlug', 
			count: 3, 
			enableLinks: true, 
			ignoreReplies: true, 
			clearContents: true,
			template: '<p>\"<em>%text%</em>\" <a href=\"http://twitter.com/%user_screen_name%/statuses/%id_str%/\">%time%</a></p>'
		});"
?>
<?php
	include_once("includes/header.php");
	include_once("includes/lastRSS.php");
?>
		<header>
			<hgroup>
				<h1>SteamLUG News</h1>
			</hgroup>
		</header>
		<section>
			<article>
				<div class = 'shadow'>
				<h1>Recent Tweets</h1>
					<div id="tweet">
						<p>Please wait while our tweets load</p>
						<p><a href="http://twitter.com/SteamLUG">If you can't wait - check out what we've been tweeting</a></p>
					</div>
					<p>Read more and follow <a href="http://twitter.com/SteamLUG">@SteamLUG</a> on Twitter.</p>
				</div>
			</article>
<?
	$rss = new lastRSS;
	$rss->cache_dir = './temp';
	$rss->cache_time = 1200;
	$rss->CDATA = 'content';
	$rss->items_limit = 6;
	$rssString = "";
	if ($rs = $rss->get('http://cenobite.swordfischer.com/steamlug/rss'))
	{
		foreach($rs['items'] as $item)
		{
			if (!preg_match("/steamlug\/events\//", $item['link']))
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
				
				if (!isset($item['author']))
				{
					$item['author'] = "Author";
				}				
								$rssString .= "\t\t\t<article>\n";
				$rssString .= "\t\t\t\t<div class = 'shadow'>\n";
				$rssString .= "\t\t\t\t\t<h1><a href = '" . $item['link'] . "'>" . htmlspecialchars($item['title']) . "</a></h1>\n";
				$rssString .= "\t\t\t\t\t<p class = 'attribution'>By " . $item['author'] . " on " . str_replace("+0000", "UTC", $item['pubDate']) . "</p>\n";
				$rssString .= "\t\t\t\t\t<p>" . $item['description'] . "</p>\n";
				$rssString .= "\t\t\t\t\t<p class = 'serverlink'><a href = '" . $item['link'] . "'>View and comment on our Steam group</a></p>\n";
				$rssString .= "\t\t\t\t</div>\n";
				$rssString .= "\t\t\t</article>\n";
			}
		}
    	}
	else
	{
		$rssString .= "\t\t\t<article>\n";
		$rssString .= "\t\t\t\t<div class = 'shadow'>\n";
		$rssString .= "\t\t\t\t\t<h1><a href = 'http://steamcommunity.com/groups/steamlug/announcements/'>Error</a></h1>\n";
		$rssString .= "\t\t\t\t\t\<p>RSS news source not found...</p>\n";
		$rssString .= "\t\t\t\t\t\<p>You can try viewing news on the Steam Group <a href = 'http://steamcommunity.com/groups/steamlug/announcements/'>Announcements page</a>.</p>\n";
		$rssString .= "\t\t\t\t</div>\n";
		$rssString .= "\t\t\t</article>\n";
	} 
	echo $rssString;
?>
		</section>
<?php include_once("includes/footer.php"); ?>
