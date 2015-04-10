<?php

	include_once('steam.php');
	include_once('creds.php');

	function getAdminNames() {
		$admins = implode(",", getAdmins());
		// TODO remember to curl this!
		$reply = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . getSteamAPIKey() . '&steamids=' . $admins);
		$users = json_decode($reply, true);
		$users = $users["response"]["players"];
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
		// TODO remember to curl this!
		$reply = file_get_contents('http://api.steampowered.com/service/PublishedFile/GetDetails/v1/?key=' . getSteamAPIKey() . '&publishedfileids[0]=' . $id );
		$details = json_decode($reply, true);
		return $details['response']['publishedfiledetails'];
	}

	/*
	* Small utility function to return JSON details about all games on Steam
	* Only call this RARELY as it is 17000 items
	*/
	function getSteamGames() {

		// TODO remember to curl this!
		$reply = file_get_contents('http://api.steampowered.com/ISteamApps/GetAppList/v0001/?format=json' );
		$details = json_decode($reply, true);
		return $details['applist']['apps']['app'];
	}

	/*
	* Small utility function to return JSON details about users game collection on Steam
	*/
	function getMemberGames( $id ) {

		// TODO remember to curl this!
		$reply = file_get_contents('http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?format=json&key=' . getSteamAPIKey() . '&steamid=' . $id . '&include_played_free_games=1' );
		$details = json_decode($reply, true);
		return $details['response'];
	}

	/*
	* Small utility function to return our active user count
	* TODO make this share information with getMembers if that is needed to be called too
	* TODO consider this returning membersInChat membersInGame membersOnline as an assoc array.
	*/
	function getGroupCount() {

		// TODO remember to curl this!
		$reply = file_get_contents('http://steamcommunity.com/groups/steamlug/memberslistxml?xml=1&p=1' );
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
		// TODO remember to curl this!
		for ( $page = 1; $page <= $pages; $page++ ) {
			$reply = file_get_contents('http://steamcommunity.com/groups/steamlug/memberslistxml?xml=1&p=' . $page );
			$details = simplexml_load_string( $reply );
			$pages = $details->totalPages;
			foreach ( (array)$details->members->steamID64 as $member) {
				array_push( $everyone, $member );
			}
		}
		return $everyone;
	}
