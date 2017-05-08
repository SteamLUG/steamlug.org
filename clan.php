<?php
$pageTitle = 'Clan';
$clanID = ( isset( $_GET[ 'clanid' ] ) && ( $_GET[ 'clanid' ] != '' ) ) ? $_GET[ 'clanid' ]: false;

include_once( 'includes/session.php' );
include_once( 'includes/functions_steam.php' );
include_once( 'includes/functions_clans.php' );
include_once( 'includes/functions_members.php' );

if ( login_check() ) {
	$me = $_SESSION['u'];
}

/* check here for POST, for create/delete clan, adjust members, etc */

if ( $clanID == false and isset( $me ) ) {

	// clans = getClanList
	$yourClans = getPlayerClansDB( $me );
	foreach ( $yourClans as $clan ) {
		print $clan[ 'name' ];
	}
	// if clans
	//		list

} elseif ( is_numeric( $clanID ) ) {

	// fetch clan, and …
	$clan = getClanSummaryDB( $clanID );
	if ( $clan != false )
		$players = getClanPlayersDB( $clan[ 'clanid' ] );

} elseif ( $clanID != false ) {

	// assume $clabID is slub url, search clan, and …
	$clan = findClanSummaryDB( $clanID );
	if ( $clan != false )
		$players = getClanPlayersDB( $clan[ 'clanid' ] );

} else {

	// assume we’re not logged in + no requested clan
	header( 'HTTP/1.0 404 Not Found' );
	exit;
}

if ( $clanID == false and isset( $me ) ) {

	// render page for current user
} elseif ( isset( $clan ) ) {

	// render page for this clan
	$profile = getPlayerSummary( $clan[ 'creator' ] );
	if ( !$profile )
		$profile = inflatePlayerSummary( $profile );

} else {

	// render nothing?
    header( 'Location: /' );
    exit( );
}

$pageTitle = " – {$clan[ 'name' ]} –  Clan Page";
include_once( 'includes/header.php' );

$listMembers = '';
if ( isset( $players ) ) {
	foreach ( $players as $member ) {
		$listMembers .= $member[ 'clanrole' ] . ':  '.  $member[ 'steamid' ] . "<br>\n";
	}
}
// header( 'Location: ' . str_replace( '//steamlug.org', '', $profile[ 'memberurl' ] ) );
/*
This is WIP, no idea how we want to present it atm…
*/
echo <<<DOCUMENT
		<h1 class="text-center">SteamLUG group: </h1>
			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">{$clan[ 'name' ]}</h3>
				</header>
				<div class="panel-body">
					Our Clan creator: {$clan[ 'creator' ]}
					<img src="{$profile[ 'avatarmedium' ]}" /><br>
				{$listMembers}
				</div>
			</article>
DOCUMENT;

include_once( 'includes/footer.php' );

