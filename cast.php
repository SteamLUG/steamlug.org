<?php

include_once( 'includes/functions_cast.php' );
include_once( 'includes/functions_events.php' );
include_once( 'includes/functions_avatars.php' );
include_once( 'includes/functions_stats.php' );

$cast = getNextEvent( true );
if ($cast != null) {
	$eTime = $cast['utctime'];
	$dt = $cast['date'] . " " . $cast['time'] . " " . $cast['tz'];
	$u = $cast['url'];
	if ( preg_match("#.*S([0-9]+)E([0-9]+).*#", $cast["title"], $matches) == 1 ) {
		$c = 'e' . str_pad( $matches[2], 2, '0', STR_PAD_LEFT);
		$s = 's' . str_pad( $matches[1], 2, '0', STR_PAD_LEFT);
	} else {
		unset( $eTime );
	}
}

if (isset($eTime)) {
	$extraJS = "\t\t\tvar target = new Date(" . $eTime . ");";
}
$castList = true;
$tailJS = array( '/scripts/events.js', '/scripts/jquery.tablesorter.min.js'/*, '/scripts/jquery.twbsPagination.min.js'*/ );
$pageTitle = 'Cast';

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

/* User wanting to see a specific cast, and shownotes file exists */
if ( $season !== "00" && $episode !== "00" && ($meta = getCastHeader( $slug ) ) ) {

	$castList = false;
	$shownotes			= getCastBody( $slug );

	$meta['RECORDED']	= ( $meta['RECORDED'] === "" ? 'N/A' :	'<time datetime="' . $meta['RECORDED'] . '">' . $meta['RECORDED'] . '</time>' );
	$meta['PUBLIC']		= ( $meta['PUBLISHED'] );
	$meta['PUBLISHED']	= ( $meta['PUBLISHED'] === "" ? '<span class="warning">In Progress</span>' : '<time datetime="' . $meta['PUBLISHED'] . '">' . $meta['PUBLISHED'] . '</time>');
	$episodeTitle		= $meta['SLUG'] . ' – ' . ( ($meta['PUBLIC'] === "") ? 'Edit In Progress' : slenc( $meta['TITLE'] ) );
	$pageTitle			.= ' ' . $episodeTitle;
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

	$rating = ( $meta[ 'RATING' ] == 'Explicit' ? '<i class="text-danger fa-circle"> Explicit language use</i>' :
		($meta[ 'RATING' ] == 'Clean' ? '<i class="text-success fa-circle"> Clean language</i>' :
		($meta[ 'RATING' ] != '' ? '<i class="fa-circle"> Tolerable language</i>' : '' ) ) );

	$extraCrap = <<<TWITCARD
		<meta name="twitter:card" content="player">
		<meta name="twitter:site" content="@SteamLUG">
		<meta name="twitter:title" content="{$episodeTitle}">
		<meta name="twitter:description" content="{$meta['SHORTDESC']}…">
		<meta name="twitter:image:src" content="https://steamlug.org/images/steamlugcast.png">
		<meta name="twitter:image:width" content="300">
		<meta name="twitter:image:height" content="300">
		<meta name="twitter:player" content="https://www.youtube-nocookie.com/embed/{$meta[ 'YOUTUBE' ]}?rel=0">
		<meta name="twitter:player:width" content="480">
		<meta name="twitter:player:height" content="360">

TWITCARD;

	$tailJS = array( '/scripts/castseek.js' );
	/* We start late to populate our Twitter player card */
	include( 'includes/header.php' );
	echo $start;

	$meta['TITLE'] = ( ( ($meta['PUBLIC'] === "" ) and ( $weareadmin === false ) ) ? 'Edit In Progress' : slenc($meta['TITLE']) );

	if ( $meta['PUBLIC'] === "" and $weareadmin === false ) {
		$episodeMP3DS = $siteListen = $episodeOddDS = $episodeYoutube = "";
	} else {
		$episodeOggFS	= ( file_exists( $meta[ 'ABSFILENAME' ] . '.ogg' )  ? round( filesize( $meta[ 'ABSFILENAME' ] . '.ogg' ) /1024/1024, 2 ) : 0 );
		$siteListen		= ($episodeOggFS > 0 ? '<audio id="castplayer" preload="none" src="' . $meta[ 'ARCHIVE' ] . '.ogg" controls="controls">Your browser does not support the &lt;audio&gt; tag.</audio>' : '');
		$episodeOddDS	= '<span class="ogg">' . ( $episodeOggFS > 0 ? $episodeOggFS . ' MB <a download href="' . $meta[ 'ARCHIVE' ] . '.ogg">Ogg</a>' : 'N/A Ogg' ) . '</span>';
		$episodeMp3FS	= ( file_exists( $meta[ 'ABSFILENAME' ] . '.mp3' )  ? round( filesize( $meta[ 'ABSFILENAME' ] . '.mp3' ) /1024/1024, 2 ) : 0 );
		$episodeMP3DS	= '<span class="mp3">' . ( $episodeMp3FS > 0 ? $episodeMp3FS . ' MB <a download href="' . $meta[ 'ARCHIVE' ] . '.mp3">MP3</a>' : 'N/A MP3' ) . '</span>';

		$episodeYoutube = ( empty( $meta['YOUTUBE'] ) ? '' : '<span class="youtube"><a href="//youtu.be/' . $meta['YOUTUBE'] . '">YOUTUBE</a></span>' );
	}

	$footer = <<<FOOTERBLOCK
  SteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.
  Visit our site https://steamlug.org/ and the cast homepage https://steamlug.org/cast
  Email us feedback, questions, tips and suggestions to cast@steamlug.org
  We can be followed on Twitter as @SteamLUG{$twits}

FOOTERBLOCK;
	$shownotes = array_merge( $shownotes, explode( "\n", $footer ) );
	$adminblock = "";
	if ( $weareadmin === true ) {
		$views = getYouTubeStat( $meta[ 'YOUTUBE' ] );
		$adminblock = <<<HELPFULNESS
<div><p>Admin helper pages:<br>YouTube <a href="/youtubethumb/{$meta['SLUG']}">video background</a> and <a href="/youtubedescription/{$meta['SLUG']}">description</a>. <a href="/youtubegeneratevideo/{$meta['SLUG']}">YouTube make video</a>. <a target="_blank" href="/transcriberer?audio={$meta['ARCHIVE']}.ogg">Note creation</a>.<br>{$views} Views on YouTube.</p></div>
HELPFULNESS;
	}

	// TODO add download stats from webalizer + youtube API?
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
			<p>{$rating}</p>
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
					<a href="https://creativecommons.org/licenses/by-sa/3.0/">
						<img class="license" src="/images/by-sa.png" alt="Creative Commons By-Share‐Alike license logo" title="Licensed under CC-BY-SA">
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

		echo _castBody( $shownotes );
	}

} else {

	include( 'includes/header.php' );
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
				<span class="group">
				<span class="label">&nbsp;</span>
				<span id="h1">{$eh[0]}</span>
				<span id="h2">{$eh[1]}</span>
				<span class="label">:</span>
				<span id="m1">{$em[0]}</span>
				<span id="m2">{$em[1]}</span>
				<span class="label">:</span>
				<span id="s1">{$es[0]}</span>
				<span id="s2">{$es[1]}</span>
				</span>
			</div>
			<p>Episode to be recorded on {$dt}</p>
			<p>Listen live as our hosts and guests record on our <a href="mumble">Mumble server</a>.</p>
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
			<p>SteamLUG Cast is a casual, fortnightly live audiocast held on our <a href="/mumble">Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities. The cast is licensed <a href = 'https://creativecommons.org/licenses/by-sa/3.0/'>CC BY-SA</a></p>
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
				<li><a href="/feed/cast/ogg">Ogg feed</a></li>
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
						<th class="hidden-xxs">Reco​rded
						<th class="col-sm-4">Title
						<th class="col-xs-1">Rating
						<th class="col-sm-2">Hosts
						<th>Guests
					</tr>
				</thead>
				<tbody>
CASTTABLE;

	foreach( getCasts( ) as $castdir )
	{
		$meta = getCastHeader( $castdir );

		/* if published unset, skip this entry */
		$wip = "";
		if ( $meta['PUBLISHED'] === '' ) {
			$meta['TITLE'] = 'Edit In Progress';
			$wip = "class=\"in-progress\" ";
		}

		$meta['TITLE'] = slenc($meta['TITLE']);

		// we add a zero width space to allow this to wrap better on mobile
		$meta['RECORDED'] = preg_replace('/-/', '-​', $meta['RECORDED'], 1);

		$rating = ( $meta[ 'RATING' ] == 'Explicit' ? '<i class="text-danger"> <abbr title="Explicit">E</abbr></i>' :
			($meta[ 'RATING' ] == 'Clean' ? '<i class="text-success"> <abbr title="Clean">C</abbr></i>' :
			($meta[ 'RATING' ] != '' ? '<i><abbr title="Tolerable">T</abbr></i>' : '' ) ) );

		$listHosts = ""; $listGuests = "";
		foreach ($meta['HOSTS'] as $Host) {
			$listHosts .= nameplate( $Host, 22 );
		}
		foreach ($meta['GUESTS'] as $Guest) {
			$listGuests .= nameplate( $Guest, 22 );
		}
		echo <<<CASTENTRY
			<tr {$wip}>
				<td><a href="/cast/{$meta['SLUG']}">{$meta['SLUG']}</a></td>
				<td class="hidden-xxs"><time datetime="{$meta['RECORDED']}">{$meta['RECORDED']}</time></td>
				<td><a href="/cast/{$meta['SLUG']}">{$meta[ 'TITLE' ]}</a></td>
				<td>$rating</td>
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
<?php

$onload = <<<CALLTHESEPLS
$(document).ready(
	$(function() {
		$("#casts").tablesorter({
			theme : "bootstrap",
			headerTemplate : '{content} {icon}',
			sortList: [[0,1]],
			cssIconAsc: 'fa-sort-up',
			cssIconDesc: 'fa-sort-down',
			cssIconNone: 'fa-unsorted',
		});
	})
);
CALLTHESEPLS;
if ($castList)
	$tailScripts = array( $onload );
include_once( 'includes/footer.php' );
