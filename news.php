<?php
$pageTitle = 'News';
include_once('includes/header.php');
include_once('includes/lastRSS.php');


include_once('includes/functions_twitter.php');
$tweetblob = '';
foreach ( getRecentTweets( 3 ) as $tweet ) {

	$time = humanTime ( $tweet[ 'created_at' ] );
	$user = $tweet[ 'user' ];
	if ( array_key_exists( 'retweeted_status', $tweet ) ) {
		$user = $tweet[ 'retweeted_status' ][ 'user' ];
		$tweet = $tweet[ 'retweeted_status' ];
	}
	$msg = populateTweet( $tweet );
	$tweetblob .= <<<TWEETBLOB
<li class="tweet"><div class="header"><a class="permalink" href="//twitter.com/{$user['screen_name']}/status/{$tweet['id']}">{$time}</a><div class="h-card p-author"><a class="profile" href="//twitter.com/{$user['screen_name']}"><img class="avatar" alt="" src="{$user['profile_image_url_https']}"><span class="full-name"><span class="p-name">{$user['name']}</span></span><span class="p-nickname">@<b>{$user['screen_name']}</b></span></a></div></div><div class="content"><p class="e-entry-title" lang="en">{$msg}</p></div><div class="footer"><ul class="tweets" role="menu"><li><a href="//twitter.com/intent/tweet?in_reply_to={$tweet['id']}" class="reply" title="Reply"><i class="fa-reply"></i><b>Reply</b></a></li><li><a href="//twitter.com/intent/retweet?tweet_id={$tweet['id']}" class="retweet" title="Retweet"><i class="fa-retweet"></i><b>Retweet</b></a></li><li><a href="//twitter.com/intent/favorite?tweet_id={$tweet['id']}" class="favourite" title="Favorite"><i class="fa-star"></i><b>Favorite</b></a></li></ul></div></li>
TWEETBLOB;
}
echo <<<TWITTERWIDGET
		<h1 class="text-center">News</h1>
		<article class="panel panel-default tweets col-md-4 col-md-push-8">
			<header class="panel-heading">
				<h3 class="panel-title">Tweets</h3>
			</header>
			<div class="panel-body" id="twitter-here">
				<span class="follow"><a href="//twitter.com/intent/follow?screen_name=SteamLUG&amp;tw_p=followbutton"><i class="fa-twitter"></i><span id="l" class="follow-label">Follow <b>@SteamLUG</b></span></a></span>
				<ol>
				{$tweetblob}
				</ol>
			</div>
		</article>

TWITTERWIDGET;

	$rss = new lastRSS;
	$rss->cache_dir = $eventXMLPath . '/steamlug/temp';
	$rss->cache_time = 1200;
	$rss->CDATA = 'content';
	$rss->date_format = 'd M o H:i:s e';
	$rss->items_limit = 6;
	$rssString = "";
	$firstItem = true;
	if ($rs = $rss->get($eventXMLPath . '/steamlug/rss.xml'))
	{

		$youtubePatterns = array(
			"/www.youtube.com\/watch\?v=([0-9A-Za-z_-]*)/",
			"/youtu.be\/([0-9A-Za-z_-]*)/"
		);
		if ( true /* false if we dislike this */ ) {

			include_once('includes/functions_youtube.php');
			$youtubeIDs = array();
			foreach($rs['items'] as $item) {
				// preview content to grab youtube data?
				// XXX this will be replaced once our db stuff is in

				if (!preg_match("/steamlug\/events\//", $item['link'])) {
					foreach($youtubePatterns as $pattern) {
						if (preg_match_all($pattern, $item['description'], $vid )) {
							$youtubeIDs = array_merge( $youtubeIDs, $vid[1] );
						}
					}
				}
			}
			$videoDetails = getVideoDetails( $youtubeIDs );
		}

		foreach($rs['items'] as $item)
		{
			if (!preg_match("/steamlug\/events\//", $item['link']))
			{
				$item['description'] = htmlspecialchars_decode($item['description'] );

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
				$item['description'] = str_replace("<br>\n<br>", "</p>\n<p>", $item['description']);
				$item['description'] = str_replace("</ul>\n\n<br>", "</ul>\n<p>", $item['description']);
				$item['description'] = str_replace("<ul>", "</p>\n<ul>", $item['description']);
				$item['description'] = str_replace("<blockquote>", "</p>\n<blockquote>", $item['description']);
				$item['description'] = str_replace("</blockquote>", "</blockquote>\n<p>", $item['description']);
				$item['description'] = str_replace("<br>", "<br />", $item['description']);
				$item['description'] = str_replace("https://steamcommunity.com/linkfilter/?url=", "", $item['description']);

				if ( true /* false if we dislike */ ) {
					foreach($youtubePatterns as $pattern) {
						preg_match_all( $pattern, $item['description'], $vids, PREG_SET_ORDER );
						foreach($vids as $vid) {
							$v = $videoDetails[ $vid[1] ];
							$t = $v['thumbnails']; $t = $t['default'];
							$d = substr( $v['description'],0,158 ) . '…';
							$embed = <<<YOUTUBE
					</p><div class="dynamiclink"><img src="{$t['url']}" alt="A thumbnail of the video for {$v['title']}"/><h4><a href="https://youtu.be/{$vid[1]}">{$v['title']}</a></h4><p>$d</p></div><p>
YOUTUBE;
							$url = preg_quote($vid[0], '/');
							$pattern = "/<a href=\"https:\/\/" . $url . "\" target=\"_blank\" rel=\"noreferrer\"  id=\"dynamiclink_[0-9]\">https:\/\/" . $url . "<\/a>/";
							$item['description'] = preg_replace( $pattern, $embed, $item['description'] );
						}
					}
				}

				if (!isset($item['author']))
				{
					$item['author'] = "Author";
				}
				$addclass = "ourclearfix";
				if ($firstItem == true) {
					$addclass="col-md-8 col-md-pull-4 fixupbootstrap";
					$firstItem = false;
				}
				// XXX messy
				$item['pubDate'] = preg_replace("/([0-9]{4}) /", "\\1 <span class=\"hidden-xxs\">", $item['pubDate']) . "</span>";
				?>
			<article class="panel panel-default steam-parsed <?=$addclass?>">
				<header class="panel-heading">
					<h3 class="panel-title"><a href="<?=$item['link'];?>"><?=htmlspecialchars_decode($item['title'])?></a></h3>
				</header>
				<div class="panel-body">
					<p><?=htmlspecialchars_decode($item['description']);?></p>
				</div>
				<footer class="panel-footer">
					<p class="pull-left">By <?=$item['author'];?> on <?=$item['pubDate'];?></p>
					<p class="pull-right"><a href ="<?=$item['link'];?>"><span class="hidden-xs">View and </span>comment<span class="hidden-sm hidden-xs"> on our Steam group</span></a></p>
					<div class="clearfix"></div>
				</footer>
			</article>
				<?php
			}
		}
    }
	else
	{
		?>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title"><a href = 'https://steamcommunity.com/groups/steamlug/announcements/'>Error</a></h3>
			</header>
			<div class="panel-body">
				<p>RSS news source not found…</p>
				<p>You can try viewing news on the Steam Group <a href = 'https://steamcommunity.com/groups/steamlug/announcements/'>Announcements page</a>.</p>
			</div>
		</article>
	<?php
	}
	echo $rssString;
?>
<?php include_once('includes/footer.php');
