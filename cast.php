<?php
require_once("rbt_prs.php");
require_once("steameventparser.php");
$season  = isset($_GET["s"]) ? $_GET["s"] : "0";
$episode = isset($_GET["e"]) ? $_GET["e"] : "0";
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
if (!function_exists('glob_recursive'))
{
	function glob_recursive($pattern, $flags = 0)
	{
		$files = glob($pattern, $flags);
		foreach (array_reverse(glob(dirname($pattern).'/*', GLOB_ONLYDIR)) as $dir)
		{
			$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
		}
	return $files;
	}
}

$rssLinks = '<link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (mp3) Feed" href="https://steamlug.org/feed/cast/mp3" /><link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (Ogg) Feed" href="https://steamlug.org/feed/cast/ogg" />';

include_once('includes/header.php');
?>
	<header>
		<h1>SteamLUG Cast</h1>
	</header>
<section>
<?
if ($season == "0" || $episode == "0" || !glob($path . "/s" . basename($season) . "e" . basename($episode) . "/*"))
{
	$aboutPage  = "\t<article>\n";
	$aboutPage .= "\t\t<div class='shadow'>\n";
	$aboutPage .= "\t\t\t<h1>About</h1>\n";
	$aboutPage .= "\t\t\t<p>SteamLUG Cast is a casual, fortnightly live audiocast held on the <a href = '/mumble'>SteamLUG Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</p>\n";
	$aboutPage .= "\t\t\t<p>Our current hosts are:</p>\n";
	$aboutPage .= "\t\t\t<ul>\n";
	$aboutPage .= "\t\t\t\t<li><a href='http://steamcommunity.com/id/cheeseness'>Cheeseness</a> - SteamLUG's benevolent leadery person</li>\n";
	$aboutPage .= "\t\t\t\t<li><a href='http://steamcommunity.com/id/johndrinkwater'>johndrinkwater</a> - SteamLUG admin and volunteer Valve github maintainer</li>\n";
	$aboutPage .= "\t\t\t\t<li><a href='http://steamcommunity.com/id/swordfischer'>swordfischer</a> - SteamLUG's chief event organiserer</li>\n";
	$aboutPage .= "\t\t\t</ul>\n";
	$aboutPage .= "\t\t\t<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>\n";
	$aboutPage .= "\t\t\t<h2>Make sure to subscribe to our lovely RSS feeds</h2>\n";
	$aboutPage .= "\t\t\t<ul>\n";
	$aboutPage .= "\t\t\t\t<li><a href = '/feed/cast/ogg'>OGG feed</a></li>\n";
	$aboutPage .= "\t\t\t\t<li><a href = '/feed/cast/mp3'>MP3 feed</a></li>\n";
	$aboutPage .= "\t\t\t</ul>\n";
	$aboutPage .= "\t\t</div>\n";
	$aboutPage .= "\t</article>\n";

if (isset($d) && strtotime($d[0] . "-" . $d[1] . "-" .$d[2])-strtotime(date("Y-m-d")) <= 21 * 86400) {
	$aboutPage .= "\t<article id = 'nextevent'>\n";
	$aboutPage .= "\t\t<div>\n";
	$aboutPage .= "\t\t\t<h1>Upcoming Episode:</h1>\n";
	$aboutPage .= "\t\t\t<h2>" . $s . ", ". $c . "</h2>\n";
	$aboutPage .= "\t\t\t<p>Cheese, john and sword talk about SteamLUG Casty things!</p>\n";
	$aboutPage .= "\t\t\t<div id='countdown'>\n";
	$aboutPage .= "\t\t\t\t<div>Days<br />\n";
	$aboutPage .= "\t\t\t\t\t<span id='d1' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t\t<span id='d2' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t</div>";
	$aboutPage .= "\t\t\t\t<div>Hours<br />\n";
	$aboutPage .= "\t\t\t\t\t<span id='h1' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t\t<span id='h2' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t</div>";
	$aboutPage .= "\t\t\t\t<div>Minutes<br />\n";
	$aboutPage .= "\t\t\t\t\t<span id='m1' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t\t<span id='m2' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t</div>";
	$aboutPage .= "\t\t\t\t<div>Seconds<br />\n";
	$aboutPage .= "\t\t\t\t\t<span id='s1' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t\t<span id='s2' class = 'counterDigit'>0</span>\n";
	$aboutPage .= "\t\t\t\t</div>\n";
	$aboutPage .= "\t\t\t</div>\n";
	$aboutPage .= "\t\t\t<p>Feel free to join our <a href = 'mumble'>SteamLUG Mumble server</a> before, during and after the show!</p>\n";
	$aboutPage .= "\t\t</div>\n";
	$aboutPage .= "\t</article>\n";
}
	echo $aboutPage;
}
?>
	<article class='shownotes'>
		<div class="shadow">
<?php
if ($season > "0" && $episode > "0" && glob($path . "/s" . basename($season) . "e" . basename($episode) . "/*.txt"))
{
	$showEpisode = glob($path . "/s" . basename($season) . "e" . basename($episode) . "/*");
	$showEpisode = preg_replace("/\.(flac|mp3|ogg|txt)/", "", $showEpisode[0]);
	$file = basename($showEpisode);
	$regex = "/[sS]([0-9]+)[eE]([0-9]+)\.(\w+(-\w+)*)/";
	preg_match($regex, $showEpisode, $matches);
	$episodeBase = $path . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
	$archiveBase = $url . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
	$episodeOggFS  = (file_exists($episodeBase . ".ogg") ? round(filesize($episodeBase . ".ogg")/1024/1024,2) : "N/A");
	$episodeFlacFS  = (file_exists($episodeBase . ".flac") ? round(filesize($episodeBase . ".flac")/1024/1024,2) : "N/A");
	$episodeMp3FS  = (file_exists($episodeBase . ".mp3") ? round(filesize($episodeBase . ".mp3")/1024/1024,2) : "N/A");

	$listItem  = "\t\t\t<h1>" . slenc(ucfirst(str_replace('-', ' ', $matches[3]))) . "</h1>\n";
	$listItem .= "\t\t\t<h3>Season: $season, Episode: $episode</h3>\n";
	$listItem .= "\t\t\t<audio preload='none' src='$archiveBase.ogg' type='audio/ogg' controls>Your browser does not support the &lt;audio&gt; tag.</audio>\n";
	$listItem .= "\t\t\t<p>\n";
	$listItem .= "\t\t\t\t" . ($episodeOggFS > 0 ? $episodeOggFS . " MB <a href='$archiveBase.ogg'>OGG</a>" : "N/A OGG") . " | \n";
	$listItem .= "\t\t\t\t" . ($episodeFlacFS > 0 ? $episodeFlacFS . " MB <a href='$archiveBase.flac'>FLAC</a>" : "N/A FLAC") . " | \n";
	$listItem .= "\t\t\t\t" . ($episodeMp3FS > 0 ? $episodeMp3FS . " MB <a href='$archiveBase.mp3'>MP3</a>\n" : "N/A MP3");
	$listItem .= "\t\t\t\t<span class='right'><a href='http://creativecommons.org/licenses/by-sa/3.0/'><img class='license' src='/images/by-sa.png' alt='Licensed under CC-BY-SA'></a></span>\n";
	$listItem .= "\t\t\t</p>\n";
	$listItem .= "\t\t\t<h3>Shownotes</h3>\n";
	echo $listItem;

	$showNotes = file($showEpisode . ".txt");
	foreach ($showNotes as $note)
	{
		$note = preg_replace_callback
		(
		'/\d+:\d+:\d+\s+\*(.*)\*/',
		function($matches){ return "<ul class='castsection'><li><span class='casttopic'>" . slenc($matches[1]) . "</span></li>\n"; },
		$note
		);
		$note = preg_replace_callback(
		'/(\d+:\d+:\d+)/',
		function($matches){ return "<time id='ts-" . slenc($matches[1]) . "' datetime='" . slenc($matches[1]) . "'>" . slenc($matches[1]) . "</time>"; },
		$note
		);
		$note = preg_replace_callback(
		'/^<time.*$/',
		function($matches){ return "<li>" . $matches[0] . "</li>\n"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/(?i)\b((?:(https?|irc):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
		function($matches){ return "[<a href='" . slenc($matches[0]) . "' class='castsource'>source</a>]"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/(?<=^|\s)@([a-z0-9_]+)/i',
		function($matches){ return "<a href='https://twitter.com/" . slenc($matches[1]) . "'>" . slenc($matches[0]) . "</a>"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/',
		function($matches){ return "<a href='mailto:". slenc($matches[0]) . "'>" . slenc($matches[0]) . "</a>"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/^\n$/',
		function($matches){ return "</ul>\n"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/\t\[(\w+)\](.*)/',
		function($matches){ return "<li class='nostamp'>&lt;<span class='nickname'>" . $matches[1] . "&gt;</span> " . $matches[2] . "</li>\n"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/\t(.*)/',
		function($matches){ return "<li class='nostamp'>" . $matches[1] . "</li>\n"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/  (.*)/',
		function($matches){ return "\t\t\t<p class='castabout'>" . $matches[1] . "</p>\n"; },
		$note
		);
		$note = preg_replace_callback
		(
		'/\[(\w\d+\w\d+)\]/',
		function($matches){ return "\t\t\t<a href='/cast/" . $matches[1] . "'>" . $matches[1] . "</a>\n"; },
		$note
		);
		echo $note . "\n";
	}
}
else
	{
	$listItem = "";
?>
				<h1>Previous Casts</h1>
				<table id='servers' class='tablesorter'>
					<thead>
						<tr>
							<th>No.
							<th>Title
							<th>OGG
							<th>FLAC
							<th>MP3
							<th>License
						</tr>
					</thead>
					<tbody>

<?php
	if (!glob_recursive($path . "*.txt")) { echo "<h3>No archives found</h3>"; }
	foreach(glob_recursive($path . "*.txt") as $filename)
		{
		$file = basename($filename, ".txt");
		$regex = "/[sS]([0-9]+)[eE]([0-9]+)\.(\w+(-\w+)*)/";
		preg_match($regex, $filename, $matches);
		$archiveBase = $url . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
		$episodeBase = $path . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
		$episodeOggFS  = (file_exists($episodeBase . ".ogg") ? round(filesize($episodeBase . ".ogg")/1024/1024,2) : "N/A");
		$episodeFlacFS  = (file_exists($episodeBase . ".flac") ? round(filesize($episodeBase . ".flac")/1024/1024,2) : "N/A");
		$episodeMp3FS  = (file_exists($episodeBase . ".mp3") ? round(filesize($episodeBase . ".mp3")/1024/1024,2) : "N/A");
		$listItem .= "\t\t\t<tr>\n";
		$listItem .= "\t\t\t\t<td><a href='/cast/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "'>S" . slenc($matches[1]) . "E" .  slenc($matches[2]) . "</a></td>\n";
		$listItem .= "\t\t\t\t<td><img src='/images/sound_grey.png' alt='Listen'><a href='/cast/s" . slenc($matches[1]) . "e"
			  . slenc($matches[2]) . "'>" .  slenc(str_replace('-', ' ', ucfirst($matches[3]))) . "</a></td>\n";
		$listItem .= "\t\t\t\t<td>" . ( $episodeOggFS > 0 ? "<a href='$archiveBase.ogg'><img src='/images/disk.png' alt='Download'>" . $episodeOggFS . " MB" : "N/A" ) . "</a></td>\n";
		$listItem .= "\t\t\t\t<td>" . ( $episodeFlacFS > 0 ? "<a href='$archiveBase.flac'><img src='/images/disk.png' alt='Download'>" . $episodeFlacFS . " MB" : "N/A" ) . "</a></td>\n";
		$listItem .= "\t\t\t\t<td>" . ( $episodeMp3FS > 0 ? "<a href='$archiveBase.mp3'><img src='/images/disk.png' alt='Download'>" . $episodeMp3FS . " MB" : "N/A" ) . "</a></td>\n";
		$listItem .= "\t\t\t\t<td><a href='http://creativecommons.org/licenses/by-sa/3.0/'><img class='license' src='/images/by-sa.png' alt='Licensed under CC-BY-SA'></a></td>\n";
		$listItem .= "\t\t\t</tr>\n";
	}
	$listItem .= "\t\t</table>\n";
	echo $listItem;
}
?>
	</div>
    </article>
</section>
<?php include_once("includes/footer.php"); ?>
