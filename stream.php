<?php
$pageTitle = 'Live Stream';

// because we hate cache
header("Cache-Control: no-cache, must-revalidate");
// HTTP 1.0ld header("Pragma: no-cache");

include_once('includes/header.php');

include_once('includes/functions_events.php');
include_once('includes/functions_geturl.php');
include_once('includes/functions_mumble.php');

$murmur = getMumble( );
$maxUsers = $murmur->get_status( )['original']['x_gtmurmur_max_users'];
$mumbleHeader = ": Offline";
if( $murmur->is_online( ) ) {
	$mumbleHeader = ': Online, ' . count( $murmur->get_users( ) ) . ' ⁄ ' . $maxUsers;
}

$data = getNextEvent( false );

$someoneStreaming = false;
$twitchOnline = false;
$hitboxOnline = false;
$streamers = "";

$streamers = '<div class="row">';
/* Twitch */
$maybeOnline = geturl( 'https://api.twitch.tv/kraken/streams/steamlug', array(), array( 'Accept: application/vnd.twitchtv.v3.json' ) );
$twitchStream = json_decode( $maybeOnline, true );
if ( $twitchStream['stream'] != null ) {
	$someoneStreaming = true;
	$twitchOnline = true;
} else {
	// if Twitch is offline, maybe we can pull the channel following
	$twitchUsers = geturl( 'https://api.twitch.tv/kraken/users/steamlug/follows/channels', array(), array( 'Accept: application/vnd.twitchtv.v3.json' ) );
	$twitchStreamers = @json_decode( $twitchUsers, true );
	$twitchPeeps = "";
	if ( $twitchStreamers != null ) {
		foreach ( $twitchStreamers['follows'] as $streamer ) {
			$person = $streamer['channel'];
			$twitchPeeps .= '<li>';
			$twitchPeeps .= '<a href="' . $person['url'] . '">';
			$twitchPeeps .= '<img src="' . ( $person['logo'] != '' ? str_replace( "http:", "", $person['logo']) : '//static-cdn.jtvnw.net/jtv_user_pictures/xarth/404_user_150x150.png' ) . '" alt="A lovely picture of ' . $person['display_name'] . '" />';
			$twitchPeeps .= $person['display_name'] . '</a>';
			$twitchPeeps .= '</li>';
		}
	$streamers .= <<<TWITCHBOX
							<div class="col-sm-6">
								<article class="panel panel-default">
									<header class="panel-heading">
										<h3 class="panel-title">Our Twitch streamers</h3>
									</header>
									<div class="panel-body">
										<ul class="streamers-list" id="twitch">
											{$twitchPeeps}
										</ul>
									</div>
								</article>
							</div>
TWITCHBOX;
	}
}

/* HitBox */
$hitboxUsers = geturl( 'http://api.hitbox.tv/team/steamlug' );
$hitboxStreamers = @json_decode( $hitboxUsers, true );
$hitboxPeeps = "";
if ( $hitboxStreamers != null ) {
	foreach ( $hitboxStreamers['members'] as $streamer ) {
		$hitboxPeeps .= '<li>';
		$hitboxPeeps .= '<a href="http://hitbox.tv/' . $streamer['user_name'] . '">';
		$hitboxPeeps .= '<img src="//edge.sf.hitbox.tv/' . $streamer['user_logo_small'] . '" alt="A lovely picture of ' . $streamer['user_name'] . '" />';
		$hitboxPeeps .= $streamer['user_name'] . '</a>';
		$hitboxPeeps .= '</li>';
		/*
		if ( $streamer['user_name'] == 'steamlug' and $streamer['is_live'] == 1) {
			$someoneStreaming = true;
			$hitboxOnline = true; // we cannot embed the hitbox viewer because it is http only true;
		}
		*/
	}
	$streamers .= <<<HITBOXBOX
						<div class="col-sm-6">
							<article class="panel panel-default">
								<header class="panel-heading">
									<h3 class="panel-title">Our Hitbox streamers</h3>
								</header>
								<div class="panel-body">
									<ul class="streamers-list" id="hitbox">
										{$hitboxPeeps}
									</ul>
								</div>
							</article>
						</div>
HITBOXBOX;
}
$streamers .= '</div>';

/* If Streaming, we should: hide <h1> title, remove Next Event: */
/* If not-streaming, show current hitbox team roster? */
if (!$someoneStreaming) {
	print '<h1 class="text-center">Live Stream</h1>';
}
?>
					<div class="row no-content">
						<div class="col-sm-6">
							<article class="panel panel-default">
								<header class="panel-heading">
									<h3 class="panel-title"><a href="/mumble">Mumble<?=$mumbleHeader; ?></a></h3>
								</header>
							</article>
						</div>
						<div class="col-sm-6">
							<article class="panel panel-default">
								<header class="panel-heading">
									<h3 class="panel-title"><a href="<?=$data["url"];?>"><?=str_replace( 'SteamLUG ','',$data["title"])?></a></h3>
								</header>
<!--							<div class="panel-body">
									<p class="text-center"><img src="<?=$data["img_header"];?>" alt="<?=$data["title"];?>" /></p>
									<p><a href="<?=$data["url"];?>" class="btn btn-primary">Click for details</a></p>
								</div>-->
							</article>
						</div>
					</div>
<?php

if ($hitboxOnline) {

echo <<<HITBOX
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title"><a href="http://hitbox.tv/steamlug">SteamLUG on HitBox</a></h3>
				</header>
				<div class="panel-body">
					<!-- <div id="hitbox-viewer"><iframe width="640" height="360" src="http://hitbox.tv/#!/embed/steamlug?autoplay=true" frameborder="0" allowfullscreen></iframe></div> -->
					<p>We can’t embed Hitbox here for you to watch, because their embed dislikes https, follow the link to watch!</p>
					<p><a href="http://hitbox.tv/steamlug" class="btn btn-primary">Click for hitbox stream</a></p>
				</div>
			</article>
HITBOX;
} else if ($twitchOnline) {

echo <<<TWITCH
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title"><a href="https://twitch.tv/steamlug">SteamLUG on Twitch</a></h3>
				</header>
				<div class="panel-body">
					<div id="twitch-viewer"><object type="application/x-shockwave-flash" width="100%" height="550px" id="live_embed_player_flash" data="https://www-cdn.jtvnw.net/swflibs/TwitchPlayer.swf?channel=steamlug">
						<param name="allowFullScreen" value="true" />
						<param name="allowScriptAccess" value="always" />
						<param name="allowNetworking" value="all" />
						<param name="movie" value="https://www-cdn.jtvnw.net/swflibs/TwitchPlayer.swf" />
						<param name="flashvars" value="hostname=www.twitch.tv&channel=steamlug&auto_play=true&start_volume=25" />
					</object></div>
					<p><a href="https://www.twitch.tv/steamlug" class="btn btn-primary">Click for larger stream</a></p>
				</div>
			</article>
TWITCH;
}
if ( $someoneStreaming == false ) {
	print <<<WHELP
			<div class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Stream Offline</h3>
				</header>
				<div class="panel-body">
					<p>It looks like no one is streaming on SteamLUG right now, how about checking out our regular streamers:</p>
					<!-- put some links here to main channels? -->
				</div>
			</div>
WHELP;

	print $streamers;
}

include_once('includes/footer.php');
