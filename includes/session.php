<?php
	ini_set( 'session.use_only_cookies', 1 );
	ini_set( 'session.use_strict_mode', 1 );
	ini_set( 'session.name', 'steamlug' );
	ini_set( 'session.cookie_httponly', 1 );

	include_once('steam.php');
	include_once('creds.php');

	function sec_session_start() {

		$currentSesh = session_id( );
		if ( empty( $currentSesh ) ) {
			$secure = false; // Set to true if using https.
			$httponly = true; // This stops javascript being able to access the session id.
			$cookieParams = session_get_cookie_params(); // Gets current cookies params.
			session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
			session_start(); // Start the php session
			session_regenerate_id(true); // regenerated the session, delete the old one
		}
	}

	function login($uid)
	{
		$_SESSION['u'] = $uid;
		$_SESSION['g'] = group_check($uid);
		store_user_details($uid);
		$_SESSION['i'] = getenv("REMOTE_ADDR");
		$_SESSION['t'] = time() + (12 * 60 * 60);
		session_write_close();
	}

	function logout()
	{
		SteamSignIn::logout();
		session_destroy();
		header ("Location: /");
	}

	function login_check()
	{
		$checkResult = false;
		if (isset($_SESSION['i']))
		{
			if ($_SESSION['i'] == getenv("REMOTE_ADDR")) //Check and make sure that he session we've got is for the IP we expect
			{
				$t = time();
				if ($_SESSION['t'] > $t)
				{
					$checkResult = true;
					$_SESSION['t'] = $t + (12 * 60 * 60);

				}
			}
			// we should never write to session again in this process
			// but does this mean we can never update the time again?
			session_write_close();
		}
		return $checkResult;
	}

	function group_check($uid)
	{
		$groups = file_get_contents('http://api.steampowered.com/ISteamUser/GetUserGroupList/v0001/?key=' . getSteamAPIKey() . '&steamid=' . $uid);
		if ($groups === false)
		{
			//Quick fix for Steam non-responsiveness and private user accounts
			echo "Private account?";
			return false;
		}
		echo $groups;
		$groups = (array) json_decode($groups, true);
		if (is_array($groups))
		{
			if ($groups['response']['success'] == 1)
			{
				foreach ($groups['response']['groups'] as $g)
				{
					if ($g['gid'] == getGroupID32())
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/* TODO consider using this to also grab users game library so we can highlight events */
	/* These calls can take time… async them somehow? */
	function store_user_details($uid)
	{
		$details = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . getSteamAPIKey() . '&steamids=' . $uid);
		if ($details === false)
		{
			//Quick fix for Steam non-responsiveness and private user accounts
			// Cannot get user avatar
			return;
		}
		$details = (array) json_decode($details, true);
		if (is_array($details))
		{
			if ( isset( $details['response']['players'] ) )
			{
				$_SESSION['n'] = $details['response']['players'][0]['personaname'];
				$_SESSION['a'] = $details['response']['players'][0]['avatarfull'];
			}
		}
		return;
	}

	sec_session_start();
?>
