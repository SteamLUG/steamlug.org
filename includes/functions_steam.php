<?php

	include_once('functions_geturl.php');
	include_once('steam.php');
	include_once('creds.php');

/* { "steamid": int, "communityvisibilitystate": int, "profilestate": int,
	"personaname": str, "lastlogoff": timestamp, "commentpermission": int,
	"profileurl": url, "avatar": url, "avatarmedium": url, "avatarfull": url,
	"personastate": int, "realname": str, "primaryclanid": int, "timecreated":
	timestamp, "personastateflags": int, "loccountrycode": ISO country code,
	"locstatecode": nfc } */
	function getPlayerSummary( $id ) {
		$params = array('key' => getSteamAPIKey(),
						'steamids' => $id,
						'format' => 'json' );
		$reply = geturl( 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/', $params );
		$users = json_decode($reply, true);
		if ( count( $users["response"]["players"] ) == 0 )
			return false;
		if ( strpos( $id, ',' ) !== false ) {
			$users = $users["response"]["players"];
		} else {
			$users = $users["response"]["players"][0];
		}
		return $users;
	}

	/* lazy helper function, just calls getPlayerSummary( ) and returns a sorted array :D */
	function getAdminNames() {

		$users = getPlayerSummary( implode( ',', getAdmins() ) );
		asort( $users );
		return $users;
	}

	/*
	* Small utility function to return JSON details about a workshop item
	* http://steamcommunity.com/sharedfiles/filedetails/?id=[0-9]*
	* we are going to assume the call has parsed URL and stripped the id off for us
	*/
	function getWorkshopDetails( $id ) {

		// TODO allow this to take array of ids in future, return array of details
		$params = array('key' => getSteamAPIKey(),
						'publishedfileids[0]' => $id,
						'format' => 'json' );
		$reply = geturl( 'http://api.steampowered.com/service/PublishedFile/GetDetails/v1/', $params );
		$details = json_decode($reply, true);
		return $details['response']['publishedfiledetails'];
	}

	/*
	* Small utility function to return JSON details about all games on Steam
	* Only call this RARELY as it is 17000 items
	*/
	function getSteamGames() {

		$params = array('key' => getSteamAPIKey(),
						'format' => 'json' );
		$reply = geturl( 'http://api.steampowered.com/ISteamApps/GetAppList/v0001/', $params );
		$details = json_decode($reply, true);
		return $details['applist']['apps']['app'];
	}

	/*
	* Small utility function to return JSON details about users game collection on Steam
	*/
	function getMemberGames( $id ) {

		$params = array('key' => getSteamAPIKey(),
						'steamid' => $id,
						'include_played_free_games' => '1',
						'format' => 'json' );
		$reply = geturl( 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/', $params );
		$details = json_decode($reply, true);
		return $details['response'];
	}

	/*
	* Small utility function to return our active user count
	* TODO make this share information with getMembers if that is needed to be called too
	* TODO consider this returning membersInChat membersInGame membersOnline as an assoc array.
	*/
	function getGroupCount() {

		$params = array( 'xml' => '1', 'p' => '1' );
		$reply = geturl( 'http://steamcommunity.com/groups/steamlug/memberslistxml', $params );
		$details = (array)simplexml_load_string( $reply );
		return $details['memberCount'];
	}


	/*
	* Small utility function to return JSON details about all our members
	* This is different from the others, as it is paginated data that only this function knows about
	*/
	function getGroupMembers() {

		$pages = 1;
		$everyone = array();

		for ( $page = 1; $page <= $pages; $page++ ) {
			$params = array( 'xml' => '1', 'p' => $page );
			$reply = geturl( 'http://steamcommunity.com/groups/steamlug/memberslistxml', $params );
			$details = simplexml_load_string( $reply );
			$pages = $details->totalPages;
			foreach ( (array)$details->members->steamID64 as $member) {
				array_push( $everyone, $member );
			}
		}
		return $everyone;
	}
