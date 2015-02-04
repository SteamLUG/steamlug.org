<?php
require_once("rbt_prs.php");
require_once("steameventparser.php");
$season  = isset($_GET["s"]) ? intval($_GET["s"]) : "0";
$season  = str_pad($season, 2, '0', STR_PAD_LEFT);
$episode = isset($_GET["e"]) ? intval($_GET["e"]) : "0";
$episode = str_pad($episode, 2, '0', STR_PAD_LEFT);
$parser = new SteamEventParser();

$month = gmstrftime("%m")-0; // Yuck, apparently the 0 breaks something?
$year = gmstrftime("%Y");
$data = $parser->genData("steamlug", $month, $year);
$data2 = $parser->genData("steamlug", ( $month >= 12 ? 1 : ( $month +1 ) ), ( $month >= 12 ? ( $year + 1 ) : $year ));
/* merge the data */
$data['events'] = array_merge($data['events'], $data2['events']);
/* cleanup */
unset($data2);

/* loopety loop through the events */
foreach ($data["events"] as $event) {
	// only use if it's a special (non-game/non-app) event and a cast
	if ($event["appid"] !== 0 || strpos($event["title"], "Cast") === false) {
		continue;
	}
	$d = explode("-", $event['date']);
	$t = explode(":", $event['time']);
	$dt = $event['date'] . " " . $event['time'] . " " . $event['tz'];
	$u = $event['url'];
	$c = preg_replace("#(.*)(S[0-9][0-9])(E[0-9][0-9])(.*)#", "\$3", $event["title"]);
	$s = preg_replace("#(.*)(S[0-9][0-9])(E[0-9][0-9])(.*)#", "\$2", $event["title"]);
	break;
}

if (isset($d)) {
	$extraJS = "\t\t\tvar target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";
}
$externalJS = array( '/scripts/events.js' );
$deferJS = array( '/scripts/castseek.js' );
$syncexternalJS = array( '/scripts/jquery.tablesorter.min.js', '/scripts/jquery.tablesorter.widgets.min.js', '/scripts/jquery.twbsPagination.min.js' );
$pageTitle = "Cast";

$rssLinks = '<link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (mp3) Feed" href="https://steamlug.org/feed/cast/mp3" /><link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (Ogg) Feed" href="https://steamlug.org/feed/cast/ogg" />';

include_once('includes/paths.php');

function slenc($u)
{
	return htmlentities($u,ENT_QUOTES, "UTF-8");
}

/* TODO: join this to our steamlug user system; TODO: make steamlug user system */
$hostAvatars = array(
	"swordfischer" =>	"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/87/87542ec881993993fe2c5268224689538e264fac_full.jpg",
	"ValiantCheese" =>	"//gravatar.com/avatar/916ffbb1cd00d10f5de27ef4f9846390",
	"johndrinkwater" =>	"//gravatar.com/avatar/751a360841982f0d0418d6d81b4beb6d",
	"MimLofBees" =>	"//pbs.twimg.com/profile_images/2458841225/cnm856lvnaz4hhkgz6yg.jpeg",
	"DerRidda" =>	"//pbs.twimg.com/profile_images/2150739768/pigava.jpeg",
	"mnarikka" =>	"//pbs.twimg.com/profile_images/523529572243869696/lb04rKRq.png",
	"Nemoder" =>	"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/0d/0d4a058f786ea71153f85262c65bb94490205b59_full.jpg",
	"beansmyname" =>	"//pbs.twimg.com/profile_images/2821579010/3f591e15adcbd026095f85b88ac8a541.png",
	"Corben78" =>	"//pbs.twimg.com/profile_images/313122973/Avatar.jpg",
	"Buckwangs" =>	"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
	"Cockfight" =>	"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
);

/* we take: ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ / ‘John Drinkwater {URL}’ and spit out HTML */
function nameplate( $string, $size = 32 ) {

	global $hostAvatars;
	$name = ""; $nickname = ""; $twitterHandle = "";
	foreach ( explode( " ", $string ) as $data ) {

		// (@johndrinkwater) or @johndrinkwater
		if ( preg_match( '/\(?@([a-z0-9_]+)\)?/i', $data, $twitterResult ) ) {
			$twitterHandle = $twitterResult[1];

		// (johndrinkwater)
		} else if ( preg_match( '/\(([a-z0-9_]+)\)/i', $data, $nicknameResult) ) {
			$nickname = $nicknameResult[1];

		// {//i.imgur.com/8YkJva1.jpg}
		} else if ( preg_match( '/{(.*)}/i', $data, $avatarURLResult) ) {
			$avatarURL = $avatarURLResult[1];

		// John Drinkwater
		} else {
			$name .= $data . " ";
		}
	}

	$name = trim( $name );
	if ( strlen( $nickname ) > 0 ) {
		$name .= " (" . $nickname . ")";
	}

	if ( strlen( $name ) == 0 && strlen( $twitterHandle ) > 0 ) {
		$name = $twitterHandle;
	}

	if ( strlen( $name ) == 0 ) {
		return $string;
	}

	if ( !isset( $avatarURL ) ) {
		if ( array_key_exists( $twitterHandle, $hostAvatars ) ) {
			$avatarURL = $hostAvatars["$twitterHandle"];
		} elseif ( array_key_exists( $name, $hostAvatars ) ) {
			$avatarURL = $hostAvatars["$name"];
		} else {
			$avatarURL = "";
		}
	}

	if ( strlen( $avatarURL ) > 0 ) {
		$avatar = <<<AVATAR
<img src="{$avatarURL}" title="{$name}" width="{$size}" height="{$size}" alt="{$name}" class="img-rounded"/>
AVATAR;
	} else {
		$avatar = $name;
	}

	if ( strlen( $twitterHandle ) > 0 ) {
		return <<<TWITLINK
<a href="https://twitter.com/{$twitterHandle}">{$avatar}</a>

TWITLINK;
	} else {
		return $avatar;
	}
}

$start = <<<STARTPAGE
		<h1 class="text-center">SteamLUG Cast</h1>
		<div class="row">
STARTPAGE;

$filename = $notesPath . "/s" . $season . "e" . $episode . "/episode.txt";

/* User wanting to see a specific cast, and shownotes file exists */
if ($season !== "00" && $episode !== "00" && file_exists($filename)) {

	$shownotes		= file($filename);

	$head = array_slice( $shownotes, 0, 14 );
	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
						'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL', 'YOUTUBE' ), '');
	foreach ( $head as $entry ) {
		list($k, $v) = explode( ':', $entry, 2 );
		$meta[$k] = trim($v); /* TODO remember to slenc() stuff! */
	}

	$epi = 's' . slenc($meta['SEASON']) . 'e' . slenc($meta['EPISODE']);
	$archiveBase = $publicURL . '/' . $epi . '/' . $meta['FILENAME'];
	$episodeBase = $filePath .'/' . $epi . '/' . $meta['FILENAME'];

	$meta['RECORDED']  = ( $meta['RECORDED'] === "" ? "N/A" : '<time datetime="' . $meta['RECORDED'] . '">' . $meta['RECORDED'] . '</time>' );
	$meta['PUBLIC'] = $meta['PUBLISHED'];
	$meta['PUBLISHED'] = ($meta['PUBLISHED'] === "" ? '<span class="warning">In Progress</span>' : '<time datetime="' . $meta['PUBLISHED'] . '">' . $meta['PUBLISHED'] . '</time>');
	$meta['TITLE'] = slenc($meta['TITLE']);
	$meta['SHORTDESCRIPTION'] = slenc(substr($meta['DESCRIPTION'],0,132));


	$noteEditor			= nameplate( $meta['NOTESCREATOR'], 22 );
	$castEditor			= nameplate( $meta['EDITOR'], 22 );
	$castHosts			= array_map('trim', explode(',', $meta['HOSTS']));
	$castGuests			= array_map('trim', explode(',', $meta['GUESTS']));
	$listHosts = ""; $listGuests = "";
	foreach ($castHosts as $Host) {
		$listHosts .= nameplate( $Host, 48 );
	}
	$listHosts = ( empty($listHosts) ? 'No Hosts' : $listHosts );
	foreach ($castGuests as $Guest) {
		$listGuests .= nameplate( $Guest, 48 );
	}
	$listGuests = ( empty($listGuests) ? 'No Guests' : $listGuests );

	$episodeOggFS	= (file_exists($episodeBase . ".ogg")  ? round(filesize($episodeBase . ".ogg") /1024/1024,2) : 0);
	$siteListen		= ($episodeOggFS > 0 ? '<audio id="castplayer" preload="none" src="' . $archiveBase . '.ogg" controls="controls">Your browser does not support the &lt;audio&gt; tag.</audio>' : '');
	$episodeOddDS	= "<span class='ogg'>" . ($episodeOggFS > 0 ? $episodeOggFS . ' MB <a download href="' . $archiveBase . '.ogg">OGG</a>' : 'N/A OGG') . "</span>";
	$episodeMp3FS	= (file_exists($episodeBase . ".mp3")  ? round(filesize($episodeBase . ".mp3") /1024/1024,2) : 0);
	$episodeMP3DS	= "<span class='mp3'>" . ($episodeMp3FS > 0 ? $episodeMp3FS . ' MB <a download href="' .$archiveBase . '.mp3">MP3</a>' : 'N/A MP3') . "</span>";

	$extraCrap = <<<TWITCARD
		<meta name="twitter:card" content="player">
		<meta name="twitter:site" content="@SteamLUG">
		<meta name="twitter:title" content="{$epi} – {$meta[ 'TITLE' ]}">
		<meta name="twitter:description" content="{$meta['SHORTDESCRIPTION']}…">
		<meta name="twitter:image:src" content="https://steamlug.org/images/steamlugcast.png">
		<meta name="twitter:image:width" content="300">
		<meta name="twitter:image:height" content="300">
		<meta name="twitter:player" content="https://www.youtube.com/embed/{$meta[ 'YOUTUBE' ]}">
		<meta name="twitter:player:width" content="480">
		<meta name="twitter:player:height" content="360">

TWITCARD;
#		<meta name="twitter:player:stream" content="https://www.youtube.com/embed/MY_URL">
#		<meta name="twitter:player:stream:content_type" content="video/mp4; codecs=&quot;avc1.42E01E1, mp4a.40.2&quot;">

	/* We start late to give us the ability to compose header info for twitter cards */
	include('includes/header.php');
	echo $start;
	$footer = <<<FOOTERBLOCK
  SteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.
  Visit our site http://steamlug.org/ and the cast homepage http://steamlug.org/cast
  Email us feedback, questions, tips and suggestions to cast@steamlug.org
  We can be followed on Twitter as @SteamLUG

FOOTERBLOCK;
	$shownotes = array_merge( $shownotes, explode( "\n", $footer ) );
echo "</div>";
echo <<<CASTENTRY
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">{$meta[ 'TITLE' ]} <span class="author">edited by {$castEditor}</span></h3>
		</header>
		<div class="panel-body">
			<div class="row">
			<div class="col-md-7">
			<h4>Season {$meta[ 'SEASON' ]}, Episode {$meta[ 'EPISODE' ]}</h4>
			<dl class="dl-horizontal">
			<dt>Recorded</dt><dd>{$meta['RECORDED']}</dd>
			<dt>Published</dt><dd>{$meta['PUBLISHED']}</dd>
			<dt>Hosts</dt><dd>$listHosts</dd>
			<dt>Special Guests</dt><dd>$listGuests</dd>
			</dl>
			</div>
			<div class="col-md-5">
			<h4>Description</h4>
			<p>{$meta['DESCRIPTION']}</p>
			</div>
			</div>
			{$siteListen}
			<div class="clearfix"></div>
			<p class="pull-left download-links">
				$episodeOddDS
				$episodeMP3DS
			</p>
			<p class="pull-right">
				<a href='http://creativecommons.org/licenses/by-sa/3.0/'>
					<img class='license' src='/images/by-sa.png' alt='Creative Commons By-Share‐Alike license logo' title='Licensed under CC-BY-SA'>
				</a>
			</p>
		</div>
	</article>
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">Shownotes <span class="author">written by {$noteEditor}</span></h3>
		</header>
		<div class="panel-body shownotes">

CASTENTRY;

	/* if published unset, skip this entry */
	if ( $meta['PUBLIC'] === '' )
	{
		/* RSS hides the episode, but the site just hides the notes */
		echo "<p>The shownotes are currently in the works, however they're not finished as of yet.</p>\n<p>You're still able to enjoy listening to the cast until we finalize the notes.</p>\n";
	} else {

		foreach ( array_slice( $shownotes, 15 ) as $note)
		{
		$note = preg_replace_callback(
			'/\d+:\d+:\d+\s+\*(.*)\*/',
			function($matches) { return "\n<h4>" . slenc($matches[1]) . "</h4>\n<dl class=\"dl-horizontal\">"; },
			$note );
		$note = preg_replace_callback(
			'/(\d+:\d+:\d+)\s+(.*)$/',
			function($matches) { return '<dt>' . slenc($matches[1]) . "</dt>\n\t<dd>" . slenc($matches[2]) . "</dd>"; },
			$note );
		$note = preg_replace_callback(
			'/(\d+:\d+:\d{2})(?!])/',
			function($matches) { return '<time id="ts-' . slenc($matches[1]) . '" datetime="' . slenc($matches[1]) . '">' . slenc($matches[1]) . '</time>'; },
			$note );
		$note = preg_replace_callback(
			'/(?i)\b((?:(https?|irc):\/\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
			function($matches) { return "[<a href='" . slenc($matches[0]) . "' class='text-info'>source</a>]"; },
			$note );
		$note = preg_replace_callback(
			'/(?i)\b((?:(steam):\/\/[^ \n<]*))/',
			function($matches) { return "<a href='" . slenc($matches[0]) . "' class=\"steam-link\">" . slenc($matches[0]) . "</a>"; },
			$note );
		$note = preg_replace_callback(
			'/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/',
			function($matches) { return "<a href='mailto:". slenc($matches[0]) . "' class=\"mail-link\">" . slenc($matches[0]) . '</a>'; },
			$note );
		$note = preg_replace_callback(
			'/((?<=^|\s|\(|>))@([A-Za-z0-9_]+)/i',
			function($matches) { return $matches[1] . '<a href="https://twitter.com/' . slenc($matches[2]) . '" class="twitter-link">' . slenc($matches[2]) . '</a>'; },
			$note );
		$note = preg_replace_callback(
			'/^\n$/',
			function($matches) { return "</dl>\n"; },
			$note );
		$note = preg_replace_callback(
			'/\t\[(\w+)\](.*)/',
			function($matches) { return "\t<dd>&lt;<span class=\"nickname\">" . $matches[1] . "</span>&gt; " . $matches[2] . "</dd>";	},
			$note );
		$note = preg_replace_callback(
			'/\t((?!<dd).*)$/',
			function($matches) { return "\t<dd>" . $matches[1] . "</dd>"; },
			$note );
		$note = preg_replace_callback(
			'/  (.*)/',
			function($matches) { return '<p>' . $matches[1] . "</p>\n";	},
			$note );
		$note = preg_replace_callback(
			'/\[(\w\d+\w\d+)#([0-9:]*)\]/',
			function($matches) { return '<a href="/cast/' . $matches[1] . '#ts-' . $matches[2] . '">' . $matches[1] . " @ " . $matches[2] . "</a>"; },
			$note );
		$note = preg_replace_callback(
			'/\[(\w\d+\w\d+)\]/',
			function($matches) { return '<a href="/cast/' . $matches[1] . '">' . $matches[1] . "</a>"; },
			$note );
		echo $note;
		}
	}

} else {

	include('includes/header.php');
	echo $start;

	/* TODO make this show as being live for the duration of the event */
	if (isset($d) && strtotime($d[0] . "-" . $d[1] . "-" .$d[2])-strtotime(date("Y-m-d")) <= 21 * 86400) {

		echo <<<NEXTCAST
<div class="col-md-6">
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title"><a href="{$u}">Upcoming Recording, {$s} {$c}</a></h3>
		</header>
		<div class="panel-body">
			<div id="countdown">
				<span class="label">Days</span>
				<span id="d1">0</span>
				<span id="d2">0</span>
				<span class="label">&nbsp;</span>
				<span id="h1">0</span>
				<span id="h2">0</span>
				<span class="label">:</span>
				<span id="m1">0</span>
				<span id="m2">0</span>
				<span class="label">:</span>
				<span id="s1">0</span>
				<span id="s2">0</span>
			</div>
			<p>This episode will be recorded on {$dt}</p>
			<p>Listen in live as our hosts and guests discuss Linux gaming on our <a href="mumble">SteamLUG Mumble server</a>.</p>
			<p><a href="{$u}" class="btn btn-primary btn-lg">Click for details</a></p>
		</div>
	</article>
</div>
NEXTCAST;
		$aboutWidth = "col-md-6";

	} else {
		// for the times when we have not organised the next cast.
		$aboutWidth = "col-md-12";
	}
	echo <<<ABOUTCAST
<div class="{$aboutWidth}">
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">About</h3>
		</header>
		<div class="panel-body">
			<p>SteamLUG Cast is a casual, fortnightly live audiocast held on the <a href="/mumble">SteamLUG Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities. SteamLUG Cast is licenced <a href = 'http://creativecommons.org/licenses/by-sa/3.0/'>CC BY-SA</a></p>
			<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>
		</div>
	</article>
</div>

<div class="col-md-12">
	<article class="panel panel-default subscribe-here">
		<header class="panel-heading">
			<h3 class="panel-title">Subscribe</h3>
		</header>
		<div class="panel-body">
			<p>Make sure to subscribe to our lovely RSS feeds</p>
			<ul>
				<li><a href="/feed/cast/ogg">OGG feed</a></li>
				<li><a href="/feed/cast/mp3">MP3 feed</a></li>
			</ul>
		</div>
	</article>
</div>
ABOUTCAST;
	echo "</div>";

	echo <<<CASTTABLE
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">Previous Casts</h3>
		</header>
		<div class="panel-body">
			<table id="casts" class="table table-striped table-hover tablesorter">
				<thead>
					<tr>
						<th class="col-sm-1">No.
						<th>Recorded
						<th class="col-sm-4">Title
						<th class="col-sm-2">Hosts
						<th>Guests
					</tr>
				</thead>
				<tbody>
CASTTABLE;

	$casts = scandir($filePath, 1);
	foreach( $casts as $castdir )
	{
		if ($castdir === '.' or $castdir === '..')
			continue;

		$filename		= $notesPath .'/'. $castdir . "/episode.txt";
		if (!file_exists($filename))
			continue;
		/* let’s grab less here, 2K ought to be enough */
		$header			= explode( "\n", file_get_contents($filename, false, NULL, 0, 1024) );

		$head = array_slice( $header, 0, 14 );
		$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
							'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
					'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL', 'YOUTUBE' ), '');
		foreach ( $head as $entry ) {
			list($k, $v) = explode( ':', $entry, 2 );
			$meta[$k] = trim($v); /* TODO remember to slenc() stuff! */
		}

		/* if published unset, skip this entry */
		if ( $meta['PUBLISHED'] === '' )
			continue;

		$castHosts = array_map('trim', explode(',', $meta['HOSTS']));
		$listHosts = ""; $listGuests = "";
		foreach ($castHosts as $Host) {
			$listHosts .= nameplate( $Host, 22 );
		}
		/* TODO: pretty the datetime= & public value up */
		$meta['RECORDED']  = '<time datetime="' . $meta['RECORDED'] . '">' . $meta['RECORDED'] . '</time>';
		$meta['PUBLISHED'] = '<time datetime="' . $meta['PUBLISHED'] . '">' . $meta['PUBLISHED'] . '</time>';

		$meta['TITLE'] = slenc($meta['TITLE']);
		/* TODO: add these in HTML, we want to show off guests! */
		$castGuests			= array_map('trim', explode(',', $meta['GUESTS']));
		foreach ($castGuests as $Guest) {
			$listGuests .= nameplate( $Guest, 22 );
		}
		echo <<<CASTENTRY
			<tr>
				<td><a href="/cast/s{$meta['SEASON']}e{$meta['EPISODE']}">S{$meta['SEASON']}E{$meta['EPISODE']}</a></td>
				<td>{$meta['RECORDED']}</td>
				<td><a href="/cast/s{$meta['SEASON']}e{$meta['EPISODE']}"><i class="fa fa-volume-up"></i>{$meta[ 'TITLE' ]}</a></td>
				<td>$listHosts</td>
				<td>$listGuests</td>
			</tr>
CASTENTRY;
	}
?>
</tbody>
</table>
<!-- FIXME
<ul class="pagination pagination-sm">
  <li class="disabled"><a href="#">«</a></li>
  <li class="active"><a href="#">Season 1</a></li>
  <li><a href="#">Season 2</a></li>
  <li><a href="#">Season 3</a></li>
  <li><a href="#">»</a></li>
</ul>
!-->
<?php
}
?>
	</div>
</article>

<script>
		$(document).ready
		(
$(function() {

  $.extend($.tablesorter.themes.bootstrap, {
	table		: '',
    caption		: 'caption',
    header		: 'bootstrap-header',	// give the header a gradient background
    sortNone	: 'fa fa-unsorted',
    sortAsc		: 'fa fa-sort-up',		// includes classes for Bootstrap v2 & v3
    sortDesc	: 'fa fa-sort-down',	// includes classes for Bootstrap v2 & v3
  });
  $("#casts").tablesorter({
    theme : "bootstrap",
    headerTemplate : '{content} {icon}',
    widgets : [ "uitheme" ],
  })
}));
</script>
<script>
  $('#pagination').twbsPagination({
	totalPages: 2,
	visiblePages: 2,
    href: '?season={{number}}'
	})
</script>
<?php
include_once("includes/footer.php");
