<?php

include_once( 'functions_db.php' );

/*
	This should control the request/display of member details in our db
*/

/* { "steamid": int, "communityvisibilitystate": int, "profilestate": int,
	"personaname": str, "lastlogoff": timestamp, "commentpermission": int,
	"profileurl": url, "avatar": url, "avatarmedium": url, "avatarfull": url,
	"personastate": int, "realname": str, "primaryclanid": int } */
	function getPlayerSummaryDB( $id ) {

		// search $id, return assoc array similiar to getPlayerSummary()
	}

/* { "steamid": int, "communityvisibilitystate": int, "profilestate": int,
	"personaname": str, "lastlogoff": timestamp, "commentpermission": int,
	"profileurl": url, "avatar": url, "avatarmedium": url, "avatarfull": url,
	"personastate": int, "realname": str, "primaryclanid": int } */
	function findPlayerSummaryDB( $vanity ) {

		// search profileurl, return results from getPlayerSummaryDB?
	}

	function storePlayerSummaryDB( $details ) {

		// $database->beginTransaction( );
		// $storeuser = $database->prepare( "REPLACE INTO members (steamid, personaname, name) VALUES (?,?)" );
		// return if it happened

		// trim vanity and avatar URL, because we dont want to waste that …
		// $database->commit( );
	}
