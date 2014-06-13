<?php
require_once("rbt_prs.php");
require_once("steameventparser.php");
$season  = isset($_GET["s"]) ? $_GET["s"] : "0";
$season  = str_pad($season, 2, '0', STR_PAD_LEFT);
$episode = isset($_GET["e"]) ? $_GET["e"] : "0";
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
	$c = preg_replace("#(.*)(S[0-9][0-9])(E[0-9][0-9])(.*)#", "\$3", $event["title"]);
	$s = preg_replace("#(.*)(S[0-9][0-9])(E[0-9][0-9])(.*)#", "\$2", $event["title"]);
	break;
}

$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";
$extraJS = $dateString;
$externalJS = array('/scripts/events.js');
$tailJS = array('/scripts/castseek.js');
$pageTitle = "Cast";

$path = "/var/www/archive.steamlug.org/steamlugcast";
$url  = "http://archive.steamlug.org/steamlugcast";

function slenc($u)
{
	return htmlentities($u,ENT_QUOTES, "UTF-8");
}

/* TODO: join this to our steamlug user system; TODO: make steamlug user system */
$hostAvatars = array(
		"swordfischer" =>	"//pbs.twimg.com/profile_images/3091650213/abd95819b5fa2ac94d26866446404b65.png",
		"ValiantCheese" =>	"//gravatar.com/avatar/916ffbb1cd00d10f5de27ef4f9846390",
		"johndrinkwater" =>	"//gravatar.com/avatar/751a360841982f0d0418d6d81b4beb6d",
		"MimLofBees" =>		"//pbs.twimg.com/profile_images/2458841225/cnm856lvnaz4hhkgz6yg.jpeg",
		"DerRidda" =>		"//pbs.twimg.com/profile_images/2150739768/pigava.jpeg",
		"mnarikka" =>		"//pbs.twimg.com/profile_images/430414134018977792/gnI7LKDc.png",
		"Nemoder" =>		"http://cdn.akamai.steamstatic.com/steamcommunity/public/images/avatars/0d/0d4a058f786ea71153f85262c65bb94490205b59_full.jpg",
);

/* we take a ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ and spit out HTML */
/* TODO: optional the avatars */
function nameplate( $string, $size ) {

	global $hostAvatars;

	/* first case, johndrinkwater */
	if ( array_key_exists( $string, $hostAvatars ) ) {
		return '<img src="' . $hostAvatars["$string"] . "\" width=\"$size\" height=\"$size\" title=\"$string\" class=\"avatar\"/>\n";
	}

	/* third case, John Drinkwater (@twitter) */
	if ( preg_match( '/([[:alnum:] ]+)\s+\(@([a-z0-9_]+)\)/i', $string, $matches) ) {
		$avatar = $matches[2];
		if ( array_key_exists( $avatar, $hostAvatars ) )
			$avatar = '<img src="' . $hostAvatars["$avatar"] . "\" width=\"$size\" height=\"$size\" title=\"$avatar\" class=\"avatar\"/>";
		return "<a href=\"https://twitter.com/" . $matches[2] . "\">" . $avatar . "</a>\n";
	}

	/* second case, @johndrinkwater */
	if (preg_match( '/@([a-z0-9_]+)/i', $string, $matches)) {
		$avatar = $matches[1];
		if ( array_key_exists( $avatar, $hostAvatars ) )
			$avatar = '<img src="' . $hostAvatars["$avatar"] . "\" width=\"$size\" height=\"$size\" title=\"$avatar\" class=\"avatar\"/>";
		return "<a href=\"https://twitter.com/" . $matches[1] . "\">" . $avatar . "</a>\n";
	}
	/* unmatched, why? blank or Nemoder :^) */
	return $string;
}

$rssLinks = '<link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (mp3) Feed" href="https://steamlug.org/feed/cast/mp3" /><link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (Ogg) Feed" href="https://steamlug.org/feed/cast/ogg" />';

include_once('includes/header.php');
?>
	<header>
		<h1>SteamLUG Cast</h1>
	</header>
<section>
<?php
/* User hitting main /cast/ page */
if ( $season == "00" || $episode == "00" )
{
	echo <<<ABOUTCAST
	<article>
		<div class="shadow">
			<h1>About</h1>
			<p>SteamLUG Cast is a casual, fortnightly live audiocast held on the <a href="/mumble">SteamLUG Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</p>
			<p>Our current hosts are:</p>
			<ul>
				<li><a href="http://steamcommunity.com/id/cheeseness">Cheeseness</a> - SteamLUG’s benevolent leadery person</li>
				<li><a href="http://steamcommunity.com/id/johndrinkwater">johndrinkwater</a> - SteamLUG admin and volunteer Valve github maintainer</li>
				<li><a href="http://steamcommunity.com/id/swordfischer">swordfischer</a> - SteamLUG’s chief event organiserer</li>
			</ul>
			<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>
			<h2>Make sure to subscribe to our lovely RSS feeds</h2>
			<ul>
				<li><a href="/feed/cast/ogg">OGG feed</a></li>
				<li><a href="/feed/cast/mp3">MP3 feed</a></li>
			</ul>
		</div>
	</article>

ABOUTCAST;

if (isset($d) && strtotime($d[0] . "-" . $d[1] . "-" .$d[2])-strtotime(date("Y-m-d")) <= 21 * 86400) {

	echo <<<NEXTCAST
	<article id="nextevent">
		<div>
			<h1>Upcoming Episode:</h1>
			<h2>$s, $c</h2>
			<p>Cheese, john and sword talk about SteamLUG Casty things!</p>
			<div id="countdown">
				<div>Days<br />
					<span id="d1" class="counterDigit">0</span>
					<span id="d2" class="counterDigit">0</span>
				</div>
				<div>Hours<br />
					<span id="h1" class="counterDigit">0</span>
					<span id="h2" class="counterDigit">0</span>
				</div>
				<div>Minutes<br />
					<span id="m1" class="counterDigit">0</span>
					<span id="m2" class="counterDigit">0</span>
				</div>
				<div>Seconds<br />
					<span id="s1" class="counterDigit">0</span>
					<span id="s2" class="counterDigit">0</span>
				</div>
			</div>
			<p>Feel free to join our <a href="mumble">SteamLUG Mumble server</a> before, during and after the show!</p>
		</div>
	</article>

NEXTCAST;
}
}
?>
	<article class='shownotes'>
		<div class="shadow">
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
	$siteListen		= ($episodeOggFS > 0 ? '<audio preload="none" src="' . $archiveBase . '.ogg" type="audio/ogg" controls>Your browser does not support the &lt;audio&gt; tag.</audio>' : '');
	$episodeOddDS	= ($episodeOggFS > 0 ? $episodeOggFS . ' MB <a download href="' . $archiveBase . '.ogg">OGG</a>' : 'N/A OGG');
	$episodeFlacFS	= (file_exists($episodeBase . ".flac") ? round(filesize($episodeBase . ".flac")/1024/1024,2) : 0);
	$episodeFlacDS	= ($episodeFlacFS > 0 ? $episodeFlacFS . ' MB <a download href="' . $archiveBase . '.flac">FLAC</a>' : 'N/A FLAC');
	$episodeMp3FS	= (file_exists($episodeBase . ".mp3")  ? round(filesize($episodeBase . ".mp3") /1024/1024,2) : 0);
	$episodeMP3DS	= ($episodeMp3FS > 0 ? $episodeMp3FS . ' MB <a download href="' .$archiveBase . '.mp3">MP3</a>' : 'N/A MP3');

echo <<<CASTENTRY
			<h1>{$meta[ 'TITLE' ]}</h1>
			<h3>Season: {$meta[ 'SEASON' ]}, Episode: {$meta[ 'EPISODE' ]}</h3>
			{$siteListen}
			<p>
				$episodeOddDS
				$episodeFlacDS
				$episodeMP3DS
				<span class='right'><a href='http://creativecommons.org/licenses/by-sa/3.0/'><img class='license' src='/images/by-sa.png' alt='Licensed under CC-BY-SA'></a></span>
			</p>
			<dl>
			<dt>Recorded</dt><dd>{$meta['RECORDED']}</dd>
			<dt>Published</dt><dd>{$meta['PUBLISHED']}</dd>
			<dt>Hosts</dt><dd>$listHosts</dd>
			<dt>Special Guests</dt><dd>$listGuests</dd>
			</dl>
			<h3>Description</h3>
			<p>{$meta['DESCRIPTION']}</p>
			<h3>Shownotes</h3>

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
			function($matches) { return '<ul class="castsection"><li><span class="casttopic">' . slenc($matches[1]) . "</span></li>\n"; },
			$note );
		$note = preg_replace_callback(
			'/(\d+:\d+:\d+)/',
			function($matches) { return '<time id="ts-' . slenc($matches[1]) . '" datetime="' . slenc($matches[1]) . '">' . slenc($matches[1]) . '</time>'; },
			$note );
		$note = preg_replace_callback(
			'/^<time.*$/',
			function($matches) { return "<li>" . $matches[0] . "</li>"; },
			$note );
		$note = preg_replace_callback(
			'/(?i)\b((?:(https?|irc):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
			function($matches) { return "[<a href='" . slenc($matches[0]) . "' class='castsource'>source</a>]"; },
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
			function($matches) { return '<p class="castabout">' . $matches[1] . "</p>";	},
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
			<h1>Previous Casts</h1>
			<table id='servers' class='tablesorter'>
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
				<td><img src="/images/sound_grey.png" alt="Listen"><a href="/cast/s{$meta['SEASON']}e{$meta['EPISODE']}">{$meta[ 'TITLE' ]}</a></td>
				<td>$listHosts</td>
				<td>$listGuests</td>
			</tr>

CASTENTRY;
	}
	echo "\t\t\t</table>\n";
}
?>
		</div>
    </article>
</section>
<?php
include_once("includes/footer.php");
