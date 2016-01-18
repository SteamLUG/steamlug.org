<?php
$pageTitle = "Member";
$memberID = isset( $_GET[ 'uid' ] ) ? ($_GET[ 'uid' ] != '' ? $_GET[ 'uid' ]: 'me' ) : 'me';

include_once('includes/session.php');
include_once('includes/functions_steam.php');
include_once('includes/functions_members.php');
include_once('includes/functions_eventattendance.php');

if ( login_check() ) {
	$me = $_SESSION['u'];
	if ( $me == $memberID )
		$memberID = 'me';
}

/* check here for POST, for create/delete member account */
// are we supplying data via POST? → write to log, create DB duplicate, Location: /member?
if ( isset( $_POST['store'] ) ) {

	$profile = getPlayerSummary( $me );
	$profile[ 'isgroupmember' ] = $_SESSION[ 'g' ];
	logDB( "User requested profile storage: {$me}" );
	storePlayerSummaryDB( $profile );
	/* if this ever fails, ignore it :] */

	/* XXX messy line to get memberurl populated */
	$profile = inflatePlayerSummary( deflatePlayerSummary( $profile ) );

	if ( true ) {
		header( "Location: " . str_replace( '//steamlug.org', '', $profile[ 'memberurl' ] ) );
		exit();
	}
}

// are we supplying data via POST? → write to log, remove DB duplicate, Location: /member?
if ( isset( $_POST['unstore'] ) ) {

	logDB( "User requested profile removal: {$me}" );
	$removed = removePlayerSummaryDB( $me );

	if ( $removed ) {
		header( "Location: /member/" );
		exit();
	}
}

$accountUpdate = "";

if ( $memberID == "me" and isset( $me ) ) {

	// XXX this currently fails to show the remove account if current user visits their public link. change?
	$profile = getPlayerSummaryDB( $me );
	if ( $profile == false ) {
		$accountUpdate = <<<UPGRADENOW
			<article class="panel panel-default panel-success">
				<header class="panel-heading">
					<h3 class="panel-title">Site membership</h3>
				</header>
				<div class="panel-body">
					<p class="col-xs-9">Hey! You do not currently have a public account on the SteamLUG site, instead just a session account which means we retain no details about you. We’d like to encourage you to go public, %explain motivation%. Below is roughly what it would look like</p>
					<p class="col-xs-9">Further information can be found in our <a href="/privacy-policy/">Privacy Policy</a>.</p>
					<form method="POST" class="form-horizontal" enctype="multipart/form-data" action="/member/">
						<fieldset>
						<input type="hidden" name="store" value="STEAMID64HERENOLOLNOTREALLYSESSIONONLYNOHACKSTHANKS">
						<input type="submit" class="col-xs-offset-2 btn btn-primary" value="Create">
						</fieldset>
					</form>
				</div>
			</article>
UPGRADENOW;
		$notDBaccount = true;
		// do we want to trigger another Steam query here? or show minimal data from session?
		// it is about controlling expectation: show what we have, or show what we will show if they promote account?
		$profile = getPlayerSummary( $me );
		$profile[ 'isgroupmember' ] = $_SESSION[ 'g' ];
	} else {
		// Always show the door hanger to delete account…
		$accountUpdate = <<<GAMEOVERMAN
			<article class="panel panel-default panel-info">
				<header class="panel-heading">
					<h3 class="panel-title">Site membership</h3>
				</header>
				<div class="panel-body">
					<p class="col-xs-9">We value our members privacy, and will always offer you the ability to control your data.<br />This will remove your personal details from our database, while still leaving intact our event history (which does still contain your SteamID). User information will only rely on session storage until you promote your account again.<br />Further information can be found in our <a href="/privacy-policy/">Privacy Policy</a>.</p>
					<form method="POST" class="form-horizontal" enctype="multipart/form-data" action="/member/">
						<fieldset>
						<input type="hidden" name="unstore" value="STEAMID64HERENOLOLNOTREALLYSESSIONONLYNOHACKSTHANKS">
						<input type="submit" class="btn btn-primary" value="Delete">
						</fieldset>
					</form>
				</div>
			</article>
GAMEOVERMAN;
	}
	// show session info and offer to create account
	// if user in DB exists, show full profile, offer to delete account
	// if not in db
	// "This is your member profile with us, all the data here is currently just stored in our server’s SESSION. To give you a public link, and start collecting badges with us, we ask that you [create a full account]"
	// if in db
	// "This is your member profile with us, all the data here is stored in our database. If you have had a change of heart, want to remove your data and retire their URL, [delete your account]."

} elseif ( is_numeric( $memberID ) ) {

	// assume $memberID is SteamID64, fetch user, and …
	$profile = getPlayerSummaryDB( $memberID );

} elseif ( $memberID != "me" ) {

	// assume $memberID is vanity url, search user, and …
	$profile = findPlayerSummaryDB( $memberID );
}

if ( !isset( $profile ) or $profile == false ) {

	// oh right, we should not 404 because logging in on a page like this will break. fml.
	// header("HTTP/1.0 404 Not Found");
	header( "Location: /" );
	exit;
}

$pageTitle = " – {$profile[ 'personaname' ] } –  Member Page";
include_once('includes/header.php');

$memberClans = getPlayerClansDB( $profile[ 'steamid' ] );
$clans = "";

if ( $memberClans != false ) {
	$clans = <<<STARTCLANS
			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">Clan membership</h3>
				</header>
				<div class="panel-body">
					<ol>
STARTCLANS;

	foreach ( $memberClans as $clan ) {
		// TODO helper function to make common elements up
		$slug = ( $clan[ 'slug' ] != '' ) ? $clan[ 'slug' ] : $clan[ 'clanid' ];
		$clans .=  '<a href="/clan/' . $slug . '">' . $clan[ 'clanrole' ] . ' of ' . $clan[ 'name' ]. '</a><br>';
	}
	$clans .= <<<ENDCLANS
					</ol>
				</div>
			</article>
ENDCLANS;
}

$recentEvents = getRecentAttendance( $profile[ 'steamid' ] );
$events = "";

if ( $recentEvents != false ) {
	$events = <<<STARTEVENTS
			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">Recent events attended…</h3>
				</header>
				<div class="panel-body">
					<ol>
STARTEVENTS;

	foreach ( $recentEvents as $event ) {
		$app = getAppImages( $event[ 'appid' ] );
		$events .=  '<li><a href="/event/' . $event[ 'eventid' ] . '"><img src="' . $app[ 'capsule' ] . '"/> ' . $event[ 'title' ] . "</a></li>\n";
	}
	$events .= <<<ENDEVENTS
					</ol>
				</div>
			</article>
ENDEVENTS;
}

$lovehatesteamlug = '<a href="//steamcommunity.com/groups/steamlug">' . ( $profile[ 'isgroupmember' ] ? "Loves SteamLUG!" : "Hasn’t joined SteamLUG proper" ) . '</a>';
$share = ( ( !array_key_exists( 'memberurl', $profile ) || ( $profile[ 'memberurl' ] == '' ) ) ? '' : "<a href=\"{$profile[ 'memberurl' ]}\">Share your profile</a><br>");

/*
This is WIP, no idea how we want to present it atm…
*/
echo <<<DOCUMENT
			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">{$profile[ 'personaname' ]}</h3>
				</header>
				<div class="panel-body">
					<img src="{$profile[ 'avatarmedium' ]}" style="float:left" /><br>
					{$share} <!-- this line should hide when notDBaccount -->
					<a href="steam://friends/add/{$profile[ 'steamid' ]}">Add as friend</a><br>
					{$lovehatesteamlug}
				</div>
			</article>
			{$accountUpdate}
			{$clans}
			{$events}
DOCUMENT;

include_once('includes/footer.php');

