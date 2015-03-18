<?php
include_once('includes/paths.php');

$season  = isset($_GET["s"]) ? intval($_GET["s"]) : "0";
$season  = str_pad($season, 2, '0', STR_PAD_LEFT);
$episode = isset($_GET["e"]) ? intval($_GET["e"]) : "0";
$episode = str_pad($episode, 2, '0', STR_PAD_LEFT);

include_once('includes/functions_events.php');
include_once('includes/functions_avatars.php');
include_once('includes/functions_cast.php');

$cast = getNextEvent( true );
if ($cast != null) {
	$eTime = $cast['utctime'];
	$dt = $cast['date'] . " " . $cast['time'] . " " . $cast['tz'];
	$u = $cast['url'];
	$c = preg_replace("#(.*)(S[0-9][0-9])(E[0-9][0-9])(.*)#", "\$3", $cast["title"]);
	$s = preg_replace("#(.*)(S[0-9][0-9])(E[0-9][0-9])(.*)#", "\$2", $cast["title"]);
}

if (isset($eTime)) {
	$extraJS = "\t\t\tvar target = new Date(" . $eTime . ");";
}
$externalJS = array( '/scripts/events.js' );
$deferJS = array( '/scripts/castseek.js' );
$syncexternalJS = array( '/scripts/jquery.tablesorter.min.js', '/scripts/jquery.tablesorter.widgets.min.js', '/scripts/jquery.twbsPagination.min.js' );
$pageTitle = "Cast";

$rssLinks = '<link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (mp3) Feed" href="https://steamlug.org/feed/cast/mp3" /><link rel="alternate" type="application/rss+xml" title="SteamLUG Cast (Ogg) Feed" href="https://steamlug.org/feed/cast/ogg" />';

function slenc($u)
{
	return htmlentities($u,ENT_NOQUOTES, "UTF-8");
}

function nameplate( $string, $size = 32 ) {

	$person = parsePersonString( $string );
	$name = $person['name'];
	if ( strlen( $person['nickname'] ) > 0 ) {
		$name .= " (" . $person['nickname'] . ")";
	}
	if ( strlen( $name ) == 0 && strlen( $person['twitter'] ) > 0 ) {
		$name = $person['twitter'];
	}
	if ( strlen( $name ) == 0 )
		return $string;

	if ( strlen( $person['avatar'] ) > 0 ) {
		$avatar = <<<AVATAR
<img src="{$person['avatar']}" title="{$name}" width="{$size}" height="{$size}" alt="{$name}" class="img-rounded"/>

AVATAR;
	} else
		$avatar = $name;

	if ( strlen( $person['twitter'] ) > 0 ) {
		return <<<TWITLINK
<a href="https://twitter.com/{$person['twitter']}">{$avatar}</a>

TWITLINK;
	} else {
		return $avatar;
	}
}

$start = <<<STARTPAGE
		<h1 class="text-center">SteamLUG Cast</h1>
STARTPAGE;

$filename = $notesPath . '/s' . $season . 'e' . $episode . '/episode.txt';

/* User wanting to see a specific cast, and shownotes file exists */
if ( $season !== "00" && $episode !== "00" && file_exists( $filename ) ) {

	$shownotes			= file( $filename );
	$meta				= castHeader( array_slice( $shownotes, 0, 14 ) );

	$epi				= 's' . $meta['SEASON'] . 'e' . $meta['EPISODE'];
	$archiveBase		= $publicURL . '/' . $epi . '/' . $meta['FILENAME'];
	$episodeBase		= $filePath  . '/' . $epi . '/' . $meta['FILENAME'];

	$meta['RECORDED']	= ( $meta['RECORDED'] === "" ? 'N/A' :	'<time datetime="' . $meta['RECORDED'] . '">' . $meta['RECORDED'] . '</time>' );
	$meta['PUBLIC']		= ( $meta['PUBLISHED'] );
	$meta['PUBLISHED']	= ( $meta['PUBLISHED'] === "" ? '<span class="warning">In Progress</span>' : '<time datetime="' . $meta['PUBLISHED'] . '">' . $meta['PUBLISHED'] . '</time>');
	$episodeTitle		= $epi . ' – ' . ( ($meta['TITLE'] === "") ? 'Edit In Progress' : slenc( $meta['TITLE'] ) );
	$pageTitle		   .= ' ' . $episodeTitle;
	$meta['SHORTDESC']	= slenc( substr( $meta['DESCRIPTION'], 0, 132 ) );
	$noteEditor			= ( $meta['NOTESCREATOR'] === "" ? "" :	'<span class="author">written by ' . nameplate( $meta['NOTESCREATOR'], 22 ) . '</span>' );
	$castEditor			= ( $meta['EDITOR'] === "" ? "" :		'<span class="author">edited by ' . nameplate( $meta['EDITOR'], 22 ) . '</span>' );

	$listHosts = '';
	foreach ($meta['HOSTS'] as $Host) {
		$listHosts .= nameplate( $Host, 48 );
	}
	$listHosts			= ( empty($listHosts) ? 'No Hosts' : $listHosts );

	$listHostsTwits = array( );
	foreach ($meta['HOSTS2'] as $Host) {
		if ( strlen( $Host['twitter'] ) > 0 )
			$listHostsTwits[] = '@' . $Host['twitter'];
	}
	$twits				= ( empty($listHostsTwits) ? '' : ', or individually as ' . implode( ', ', $listHostsTwits) );

	$listGuests = '';
	foreach ($meta['GUESTS'] as $Guest) {
		$listGuests .= nameplate( $Guest, 48 );
	}
	$listGuests			= ( empty($listGuests) ? 'No Guests' : $listGuests );

	$extraCrap = <<<TWITCARD
		<meta name="twitter:card" content="player">
		<meta name="twitter:site" content="@SteamLUG">
		<meta name="twitter:title" content="{$episodeTitle}">
		<meta name="twitter:description" content="{$meta['SHORTDESC']}…">
		<meta name="twitter:image:src" content="https://steamlug.org/images/steamlugcast.png">
		<meta name="twitter:image:width" content="300">
		<meta name="twitter:image:height" content="300">
		<meta name="twitter:player" content="https://www.youtube.com/embed/{$meta[ 'YOUTUBE' ]}">
		<meta name="twitter:player:width" content="480">
		<meta name="twitter:player:height" content="360">

TWITCARD;

	/* We start late to populate our Twitter player card */
	include('includes/header.php');

	$meta['TITLE'] = ( ( ($meta['TITLE'] === "") or ( $weareadmin === false ) ) ? 'Edit In Progress' : slenc($meta['TITLE']) );

	if ( $meta['PUBLIC'] === "" and $weareadmin === false ) {
		$episodeMP3DS = $siteListen = $episodeOddDS = $episodeYoutube = "";
	} else {
		$episodeOggFS	= ( file_exists( $episodeBase . '.ogg' )  ? round( filesize( $episodeBase . '.ogg' ) /1024/1024, 2 ) : 0 );
		$siteListen		= ($episodeOggFS > 0 ? '<audio id="castplayer" preload="none" src="' . $archiveBase . '.ogg" controls="controls">Your browser does not support the &lt;audio&gt; tag.</audio>' : '');
		$episodeOddDS	= '<span class="ogg">' . ( $episodeOggFS > 0 ? $episodeOggFS . ' MB <a download href="' . $archiveBase . '.ogg">OGG</a>' : 'N/A OGG' ) . '</span>';
		$episodeMp3FS	= ( file_exists( $episodeBase . '.mp3' )  ? round( filesize( $episodeBase . '.mp3' ) /1024/1024, 2 ) : 0 );
		$episodeMP3DS	= '<span class="mp3">' . ( $episodeMp3FS > 0 ? $episodeMp3FS . ' MB <a download href="' .$archiveBase . '.mp3">MP3</a>' : 'N/A MP3' ) . '</span>';

		$episodeYoutube = ( empty( $meta['YOUTUBE'] ) ? '' : '<span class="youtube"><a href="//youtu.be/' . $meta['YOUTUBE'] . '">YOUTUBE</a></span>' );
	}

	echo $start;
	$footer = <<<FOOTERBLOCK
  SteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.
  Visit our site http://steamlug.org/ and the cast homepage http://steamlug.org/cast
  Email us feedback, questions, tips and suggestions to cast@steamlug.org
  We can be followed on Twitter as @SteamLUG{$twits}

FOOTERBLOCK;
	$shownotes = array_merge( $shownotes, explode( "\n", $footer ) );
	$adminblock = "";
	if ( $weareadmin === true ) {
		$adminblock = <<<HELPFULNESS
<div><p>Admin helper pages:<br>YouTube <a href="/youtubethumb/{$epi}">video background</a> and <a href="/youtubedescription/{$epi}">description</a>. <a target="_blank" href="/transcriberer?audio={$archiveBase}.ogg">Note creation</a>.</p></div>
HELPFULNESS;
	}

echo <<<CASTENTRY
	<article class="panel panel-default" id="cast-description">
		<header class="panel-heading">
			<h3 class="panel-title">{$meta[ 'TITLE' ]} {$castEditor}</h3>
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
			<div id="play-box">
				{$siteListen}
				<p class="download-links">
					$episodeOddDS
					$episodeMP3DS
					$episodeYoutube
				</p>
				<p class="licence">
					<a href='http://creativecommons.org/licenses/by-sa/3.0/'>
						<img class='license' src='/images/by-sa.png' alt='Creative Commons By-Share‐Alike license logo' title='Licensed under CC-BY-SA'>
					</a>
				</p>
				{$adminblock}
			</div>
		</div>
	</article>
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">Shownotes {$noteEditor}</h3>
		</header>
		<div class="panel-body shownotes">

CASTENTRY;

	/* if published unset, skip this entry. Unless OP admin */
	if ( $weareadmin === false and $meta['PUBLIC'] === '' )
	{
		/* RSS hides the episode, but the site just hides the notes */
		echo "<p>The episode recording is currently in the works.</p>\n";
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
	echo "		<div class=\"row\">";
	/* TODO make this show as being live for the duration of the event */
	if (isset($eTime) && (( $eTime - time() ) <= 14 * 86400)) {

		$eventDate = new DateTime(); $eventDate->setTimestamp($eTime);
		$diff = date_diff($eventDate, new DateTime("now"));
		list($ed, $eh, $em, $es) = explode( ' ', $diff->format("%D %H %I %S") );

		echo <<<NEXTCAST
<div class="col-md-6">
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title"><a href="{$u}">Upcoming Recording, {$s} {$c}</a></h3>
		</header>
		<div class="panel-body">
			<div id="countdown">
				<span class="label">Days</span>
				<span id="d1">{$ed[0]}</span>
				<span id="d2">{$ed[1]}</span>
				<span class="label">&nbsp;</span>
				<span id="h1">{$eh[0]}</span>
				<span id="h2">{$eh[1]}</span>
				<span class="label">:</span>
				<span id="m1">{$em[0]}</span>
				<span id="m2">{$em[1]}</span>
				<span class="label">:</span>
				<span id="s1">{$es[0]}</span>
				<span id="s2">{$es[1]}</span>
			</div>
			<p>This episode will be recorded on {$dt}</p>
			<p>Listen in live as our hosts and guests discuss Linux gaming on our <a href="mumble">Mumble server</a>.</p>
			<p><a href="{$u}" class="btn btn-primary btn-lg pull-right">Click for details</a></p>
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
			<p>SteamLUG Cast is a casual, fortnightly live audiocast held on our <a href="/mumble">Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities. The cast is licensed <a href = 'http://creativecommons.org/licenses/by-sa/3.0/'>CC BY-SA</a></p>
			<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>
			<p><a href="/cast-guests">Do you want to be a guest?</a></p>
		</div>
	</article>
</div>

<div class="col-md-12">
	<article class="panel panel-default subscribe-here">
		<header class="panel-heading">
			<h3 class="panel-title">Subscribe</h3>
		</header>
		<div class="panel-body">
			<p>Make sure to subscribe to our lovely feeds</p>
			<ul>
				<li><a href="/feed/cast/ogg">OGG feed</a></li>
				<li><a href="/feed/cast/mp3">MP3 feed</a></li>
				<li class="apple-why"><a href="https://itunes.apple.com/gb/podcast/steamlug-cast/id673962699?mt=2">iTunes</a></li>
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
		<div class="panel-body panel-body-table">
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

	$casts = scandir($notesPath, 1);
	foreach( $casts as $castdir )
	{
		if ($castdir === '.' or $castdir === '..' or $castdir === '.git' or $castdir === 'README')
			continue;

		$filename		= $notesPath .'/'. $castdir . "/episode.txt";

		if (!file_exists($filename))
			continue;

		$header			= file_get_contents($filename, false, NULL, 0, 950);
		$meta			= castHeaderFromString( $header );

		/* if published unset, skip this entry */
		$wip = "";
		if ( $meta['PUBLISHED'] === '' ) {
			$meta['TITLE'] = 'Edit In Progress';
			$wip = "class=\"in-progress\" ";
		}

		$meta['TITLE'] = slenc($meta['TITLE']);

		$listHosts = ""; $listGuests = "";
		foreach ($meta['HOSTS'] as $Host) {
			$listHosts .= nameplate( $Host, 22 );
		}
		foreach ($meta['GUESTS'] as $Guest) {
			$listGuests .= nameplate( $Guest, 22 );
		}
		echo <<<CASTENTRY
			<tr {$wip}>
				<td><a href="/cast/s{$meta['SEASON']}e{$meta['EPISODE']}">S{$meta['SEASON']}E{$meta['EPISODE']}</a></td>
				<td><time datetime="{$meta['RECORDED']}">{$meta['RECORDED']}</time></td>
				<td><a href="/cast/s{$meta['SEASON']}e{$meta['EPISODE']}">{$meta[ 'TITLE' ]}</a></td>
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
include_once('includes/footer.php');
