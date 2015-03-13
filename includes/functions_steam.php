<?php

	include_once('steam.php');
	include_once('creds.php');

	function getAdminNames() {
		$admins = implode(",", getAdmins());
		$reply = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . getSteamAPIKey() . '&steamids=' . $admins);
		$users = json_decode($reply, true);
		$users = $users["response"]["players"];
		asort( $users );
		return $users;
	}
