<?php

	include_once('steam.php');
	include_once('creds.php');
	
	function sec_session_start() {
		$session_name = 'steamlug'; // Set a custom session name
		$secure = false; // Set to true if using https.
		$httponly = true; // This stops javascript being able to access the session id. 
	 
		ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies. 
		$cookieParams = session_get_cookie_params(); // Gets current cookies params.
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly); 
		session_name($session_name); // Sets the session name to the one set above.
		session_start(); // Start the php session
		session_regenerate_id(true); // regenerated the session, delete the old one.  
	}

	function login($uid)
	{
		$_SESSION['u'] = $uid;
		$_SESSION['g'] = group_check($uid);
		$_SESSION['i'] = getenv("REMOTE_ADDR");
		$_SESSION['t'] = time() + 1800;
	}

	function logout()
	{
		echo "Logging out";
		SteamSignIn::logout();
		$_SESSION = array();
		$cookieParams = session_get_cookie_params();
		setcookie(session_name(),  '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
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
					$_SESSION['t'] = $t + 1800;
				}
			}
		}
		//session_write_close();
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


	sec_session_start();
?>
