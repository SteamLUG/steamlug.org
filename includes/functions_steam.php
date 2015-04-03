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
