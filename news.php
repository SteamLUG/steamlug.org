<?php
	$pageTitle = "News";
	include_once("includes/header.php");
	include_once("includes/lastRSS.php");
?>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		<header>
				<h1>SteamLUG News</h1>
		</header>
		<section>
		<article>
			<div class="shadow">
				<h1>
					Recent Tweets
					<span class="follow"><a href="https://twitter.com/SteamLUG" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @SteamLUG</a></span>	
				</h1>
				<div class="timeline">
					<a class="twitter-timeline" href="https://twitter.com/SteamLUG" data-widget-id="423854063487160320" data-link-color="#ebebeb" data-chrome="nofooter noheader transparent noborders" data-tweet-limit="3" lang="EN">Tweets by @SteamLUG</a>
				</div>
			</div>
		</article>
<?php
	$rss = new lastRSS;
	$rss->cache_dir = './temp';
	$rss->cache_time = 1200;
	$rss->CDATA = 'content';
	$rss->items_limit = 6;
	$rssString = "";
	if ($rs = $rss->get('http://cenobite.swordfischer.com/steamlug/rss.xml'))
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
				$item['description'] = str_replace("<blockquote>", "</p>\n<blockquote>", $item['description']);
				$item['description'] = str_replace("</blockquote>", "</blockquote>\n<p>", $item['description']);
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
