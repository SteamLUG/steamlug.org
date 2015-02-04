<?php
	$pageTitle = "News";
	include_once("includes/header.php");
	include_once("includes/lastRSS.php");
?>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		<h1 class="text-center">SteamLUG News</h1>
		<article class="panel panel-primary">
			<header class="panel-heading">
				<h3 class="panel-title">Recent Tweets</h3>
			</header>
			<div class="panel-body" id="twitter-here">
					<span class="follow"><a href="https://twitter.com/SteamLUG" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @SteamLUG</a></span>
					<a class="twitter-timeline" href="https://twitter.com/SteamLUG" data-widget-id="558698447109636097" data-link-color="#ebebeb" data-chrome="nofooter noheader transparent noborders" data-tweet-limit="3" lang="EN">Tweets by @SteamLUG</a>
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
				$item['description'] = str_replace("https://steamcommunity.com/linkfilter/?url=", "", $item['description']);

				if (!isset($item['author']))
				{
					$item['author'] = "Author";
				}
				?>
			<article class="panel panel-primary">
				<header class="panel-heading">
					<h3 class="panel-title"><a href="<?=$item['link'];?>"><?=htmlspecialchars($item['title']);?></a></h3>
				</header>
				<div class="panel-body">
					<p><?=htmlspecialchars_decode($item['description']);?></p>
				</div>
				<div class="panel-footer">
					<p class="pull-left">By <?=$item['author'];?> on <?=str_replace("+0000", "UTC", $item['pubDate']);?></p>
					<p class="pull-right"><a href ="<?=$item['link'];?>">View and comment on our Steam group</a></p>
					<div class="clearfix"></div>
				</div>
			</article>
				<?php
			}
		}
    }
	else
	{
		?>
		<article class="panel panel-primary">
			<header class="panel-heading">
				<h3 class="panel-title"><a href = 'http://steamcommunity.com/groups/steamlug/announcements/'>Error</a></h3>
			</header>
			<div class="panel-body">
				<p>RSS news source not found…</p>
				<p>You can try viewing news on the Steam Group <a href = 'http://steamcommunity.com/groups/steamlug/announcements/'>Announcements page</a>.</p>
			</div>
		</article>
	<?php
	} 
	echo $rssString;
?>
<?php include_once("includes/footer.php"); ?>
