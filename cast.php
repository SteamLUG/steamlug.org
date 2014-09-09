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

$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";
$extraJS = $dateString;
$syncexternalJS = array('/scripts/jquery.js','/scripts/jquery.tablesorter.js','/scripts/events.js','/scripts/jquery.tablesorter.widgets.js','/scripts/jquery.twbsPagination.js');
$tailJS = array('/scripts/castseek.js');
$pageTitle = "Cast";

$path = "/var/www/archive.steamlug.org/steamlugcast";
$url  = "//archive.steamlug.org/steamlugcast";

function slenc($u)
{
	return htmlentities($u,ENT_QUOTES, "UTF-8");
}

/* TODO: join this to our steamlug user system; TODO: make steamlug user system */
$hostAvatars = array(
		"swordfischer" =>	"//gravatar.com/avatar/12da0ce50a5376a78188583f963cb3ee",
		"ValiantCheese" =>	"//gravatar.com/avatar/916ffbb1cd00d10f5de27ef4f9846390",
		"johndrinkwater" =>	"//gravatar.com/avatar/751a360841982f0d0418d6d81b4beb6d",
		"MimLofBees" =>		"//pbs.twimg.com/profile_images/2458841225/cnm856lvnaz4hhkgz6yg.jpeg",
		"DerRidda" =>		"//pbs.twimg.com/profile_images/2150739768/pigava.jpeg",
		"mnarikka" =>		"//pbs.twimg.com/profile_images/523529572243869696/lb04rKRq.png",
		"Nemoder" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/0d/0d4a058f786ea71153f85262c65bb94490205b59_full.jpg",
		"beansmyname" =>	"//pbs.twimg.com/profile_images/2821579010/3f591e15adcbd026095f85b88ac8a541.png",
		"Corben78" =>		"//pbs.twimg.com/profile_images/313122973/Avatar.jpg",
		"Buckwangs" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
		"Cockfight" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
);

/* we take a ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ and spit out HTML */
/* TODO: optional the avatars */
function nameplate( $string, $size ) {

	global $hostAvatars;

	/* first case, johndrinkwater */
	if ( array_key_exists( $string, $hostAvatars ) ) {
		return '<img src="' . $hostAvatars["$string"] . "\" title=\"$string\" alt=\"$string\" class=\"img-rounded\"/>\n";
	}

	/* third case, John Drinkwater (@twitter) */
	if ( preg_match( '/([[:alnum:] ]+)\s+\(@([a-z0-9_]+)\)/i', $string, $matches) ) {
		$avatar = $matches[2];
		if ( array_key_exists( $avatar, $hostAvatars ) )
			$avatar = '<img src="' . $hostAvatars["$avatar"] . "\"  title=\"$string\" alt=\"$avatar\" class=\"img-rounded\"/>";
		return "<a href=\"https://twitter.com/" . $matches[2] . "\">" . $avatar . "</a>\n";
	}

	/* second case, @johndrinkwater */
	if (preg_match( '/@([a-z0-9_]+)/i', $string, $matches)) {
		$avatar = $matches[1];
		if ( array_key_exists( $avatar, $hostAvatars ) )
			$avatar = '<img src="' . $hostAvatars["$avatar"] . "\" title=\"$string\" alt=\"$avatar\" class=\"img-rounded\"/>";
		return "<a href=\"https://twitter.com/" . $matches[1] . "\">" . $avatar . "</a>\n";
	}
	/* unmatched, why? blank or Nemoder :^) */
	return $string;
}

$rssLinks = '<link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (mp3) Feed" href="https://steamlug.org/feed/cast/mp3" /><link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (Ogg) Feed" href="https://steamlug.org/feed/cast/ogg" />';

include_once('includes/header.php');
?>
		<h1 class="text-center">SteamLUG Cast</h1>
		<div class="row">
<?php
/* User hitting main /cast/ page */
if ( $season == "00" || $episode == "00" )
{
if (isset($d) && strtotime($d[0] . "-" . $d[1] . "-" .$d[2])-strtotime(date("Y-m-d")) <= 21 * 86400) {

	echo <<<NEXTCAST
<div class="col-md-7">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Upcoming Episode</h3>
		</div>
		<div class="panel-body">
			<h4><a href = '{$u}'>{$s} {$c}</a></h4>
			<p>Listen in live as our hosts and guests discuss Linux gaming!</p>
			<p>This episode will be recorded on {$dt}</p>
			<p>Feel free to join our <a href="mumble">SteamLUG Mumble server</a> before, during and after the show!</p>
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
			<p><button class="btn btn-info"><a href ="{$u}">Click for details</a></button></p>
		</div>
	</div>
</div>
NEXTCAST;
}
	echo <<<ABOUTCAST
<div class="col-md-5">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">About</h3>
		</div>
		<div class="panel-body">
			<p>SteamLUG Cast is a casual, fortnightly live audiocast held on the <a href="/mumble">SteamLUG Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities. SteamLUG Cast is licenced <a href = 'http://creativecommons.org/licenses/by-sa/3.0/'>CC BY-SA</a></p>
			<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>
			<h4>Make sure to subscribe to our lovely RSS feeds</h4>
			<ul>
				<li><a href="/feed/cast/ogg">OGG feed</a></li>
				<li><a href="/feed/cast/mp3">MP3 feed</a></li>
			</ul>
		</div>
	</div>
</div>
ABOUTCAST;

}
?>
	</div>

<?php
$filename = $path . "/s" . $season . "e" . $episode . "/episode.txt";
/* User wanting to see a specific cast, and shownotes file exists */
if ($season !== "00" && $episode !== "00" && file_exists($filename))
{
	$shownotes		= file($filename);

	$head = array_slice( $shownotes, 0, 10 );
	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
						'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL' ), '');
	foreach ( $head as $entry ) {
		list($k, $v) = explode( ':', $entry, 2 );
		$meta[$k] = trim($v); /* TODO remember to slenc() stuff! */
	}

	$epi = 's' . slenc($meta['SEASON']) . 'e' . slenc($meta['EPISODE']);
	$archiveBase = $url . '/' . $epi . '/' . $meta['FILENAME'];
	$episodeBase = $path .'/' . $epi . '/' . $meta['FILENAME'];

	$meta['RECORDED']  = ( $meta['RECORDED'] === "" ? "N/A" : '<time datetime="' . $meta['RECORDED'] . '">' . $meta['RECORDED'] . '</time>' );
	$meta['PUBLIC'] = $meta['PUBLISHED'];
	$meta['PUBLISHED'] = ($meta['PUBLISHED'] === "" ? '<span class="warning">In Progress</span>' : '<time datetime="' . $meta['PUBLISHED'] . '">' . $meta['PUBLISHED'] . '</time>');
	$meta['TITLE'] = slenc($meta['TITLE']);

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
	$siteListen		= ($episodeOggFS > 0 ? '<audio id="castplayer" preload="none" src="' . $archiveBase . '.ogg" type="audio/ogg" controls>Your browser does not support the &lt;audio&gt; tag.</audio>' : '');
	$episodeOddDS	= ($episodeOggFS > 0 ? $episodeOggFS . ' MB <a download href="' . $archiveBase . '.ogg">OGG</a>' : 'N/A OGG');
	$episodeFlacFS	= (file_exists($episodeBase . ".flac") ? round(filesize($episodeBase . ".flac")/1024/1024,2) : 0);
	$episodeFlacDS	= ($episodeFlacFS > 0 ? $episodeFlacFS . ' MB <a download href="' . $archiveBase . '.flac">FLAC</a>' : 'N/A FLAC');
	$episodeMp3FS	= (file_exists($episodeBase . ".mp3")  ? round(filesize($episodeBase . ".mp3") /1024/1024,2) : 0);
	$episodeMP3DS	= ($episodeMp3FS > 0 ? $episodeMp3FS . ' MB <a download href="' .$archiveBase . '.mp3">MP3</a>' : 'N/A MP3');

echo <<<CASTENTRY
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">{$meta[ 'TITLE' ]}</h3>
		</div>
		<div class="panel-body">
			<div class="row">
			<div class="col-md-7">
			<h4>Season: {$meta[ 'SEASON' ]}, Episode: {$meta[ 'EPISODE' ]}</h4>
			<dl class="dl-horizontal">
			<dt>Recorded</dt><dd>{$meta['RECORDED']}</dd>
			<dt>Published</dt><dd>{$meta['PUBLISHED']}</dd>
			<dt>Hosts</dt><dd>$listHosts</dd>
			<dt>Special Guests</dt><dd>$listGuests</dd>
			</dl>
			</div>
			<div class="col-md-4">
			<h4>Description</h4>
			<p>{$meta['DESCRIPTION']}</p>
			</div>
			</div>
			{$siteListen}
			<div class="clearfix"></div>
			<p class="pull-left">
				$episodeOddDS
				$episodeFlacDS
				$episodeMP3DS
			</p>
			<p class="pull-right">
				<a href='http://creativecommons.org/licenses/by-sa/3.0/'>
					<img class='license' src='/images/by-sa.png' alt='Licensed under CC-BY-SA'>
				</a>
			</p>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title" id="shownotes">Shownotes</h3>
		</div>
		<div class="panel-body shownotes">

CASTENTRY;

	/* if published unset, skip this entry */
	if ( $meta['PUBLIC'] === '' )
	{
		/* RSS hides the episode, but the site just hides the notes */
		echo "<p>The shownotes are currently in the works, however they're not finished as of yet.</p>\n<p>You're still able to enjoy listening to the cast until we finalize the notes.</p>\n";
	} else {

		foreach ( array_slice( $shownotes, 12 ) as $note)
		{
		$note = preg_replace_callback(
			'/\d+:\d+:\d+\s+\*(.*)\*/',
			function($matches) { return '<ul class="list-unstyled castsection"><li><span class="casttopic">' . slenc($matches[1]) . "</span></li>\n"; },
			$note );
		$note = preg_replace_callback(
			'/(\d+:\d+:\d+)/',
			function($matches) { return '<time class="casttimestamp" id="ts-' . slenc($matches[1]) . '" datetime="' . slenc($matches[1]) . '">' . slenc($matches[1]) . '</time>'; },
			$note );
		$note = preg_replace_callback(
			'/^<time.*$/',
			function($matches) { return "<li>" . $matches[0] . "</li>"; },
			$note );
		$note = preg_replace_callback(
			'/(?i)\b((?:(https?|irc):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
			function($matches) { return "[<a href='" . slenc($matches[0]) . "' class='text-info'>source</a>]"; },
			$note );
		$note = preg_replace_callback(
			'/(?<=^|\s)@([a-z0-9_]+)/i',
			function($matches) { return '<a href="https://twitter.com/' . slenc($matches[1]) . '">' . slenc($matches[0]) . '</a>'; },
			$note );
		$note = preg_replace_callback(
			'/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/',
			function($matches) { return "<a href='mailto:". slenc($matches[0]) . "'>" . slenc($matches[0]) . '</a>'; },
			$note );
		$note = preg_replace_callback(
			'/^\n$/',
			function($matches) { return "</ul>\n"; },
			$note );
		$note = preg_replace_callback(
			'/\t\[(\w+)\](.*)/',
			function($matches) { return '<li class="nostamp">&lt;<span class="nickname">' . $matches[1] . "&gt;</span> " . $matches[2] . "</li>";	},
			$note );
		$note = preg_replace_callback(
			'/\t(.*)/',
			function($matches) { return '<li class="nostamp">' . $matches[1] . "</li>"; },
			$note );
		$note = preg_replace_callback(
			'/  (.*)/',
			function($matches) { return '<p>' . $matches[1] . "</p>";	},
			$note );
		$note = preg_replace_callback(
			'/\[(\w\d+\w\d+)\]/',
			function($matches) { return '<a href="/cast/' . $matches[1] . '">' . $matches[1] . "</a>\n"; },
			$note );
		echo $note;
		}
	}
} else {
/* Show cast list */
?>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Previous Casts</h3>
		</div>
		<div class="panel-body">
			<table id="casts" class="table table-striped table-hover tablesorter">
				<thead>
					<tr>
						<th>No.
						<th>Recorded
						<th>Title
						<th>Hosts
						<th>Guests
					</tr>
				</thead>
				<tbody>
<?php
	$casts = scandir($path, 1);
	foreach( $casts as $castdir )
	{
		if ($castdir === '.' or $castdir === '..')
			continue;

		$filename		= $path .'/'. $castdir . "/episode.txt";
		if (!file_exists($filename))
			continue;
		/* let’s grab less here, 2K ought to be enough */
		$header			= explode( "\n", file_get_contents($filename, false, NULL, 0, 1024) );

		$head = array_slice( $header, 0, 10 );
		$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
							'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
					'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL' ), '');
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
			$listHosts .= nameplate( $Host, 16 );
		}
		/* TODO: pretty the datetime= & public value up */
		$meta['RECORDED']  = '<time datetime="' . $meta['RECORDED'] . '">' . $meta['RECORDED'] . '</time>';
		$meta['PUBLISHED'] = '<time datetime="' . $meta['PUBLISHED'] . '">' . $meta['PUBLISHED'] . '</time>';

		$meta['TITLE'] = slenc($meta['TITLE']);
		/* TODO: add these in HTML, we want to show off guests! */
		$castGuests			= array_map('trim', explode(',', $meta['GUESTS']));
		foreach ($castGuests as $Guest) {
			$listGuests .= nameplate( $Guest, 16 );
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
</div>

<script>
		$(document).ready
		(
$(function() {

  $.extend($.tablesorter.themes.bootstrap, {
	table	   : '',
    caption    : 'caption',
    header     : 'bootstrap-header', // give the header a gradient background
    sortNone   : 'fa fa-unsorted',
    sortAsc    : 'fa fa-sort-up',     // includes classes for Bootstrap v2 & v3
    sortDesc   : 'fa fa-sort-down', // includes classes for Bootstrap v2 & v3
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
