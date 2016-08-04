<?php
	ini_set( 'session.use_only_cookies', 1 );
	ini_set( 'session.use_strict_mode', 1 );
	ini_set( 'session.name', 'steamlug' );
	ini_set( 'session.cookie_httponly', 1 );
	// this setting breaks sessions for localhost, disable when testing locally
	// ini_set( 'session.cookie_secure', 1 );

	include_once('functions_steam.php');
	include_once('steam.php');
	include_once('creds.php');

	function sec_session_start() {

		session_start(); // Start the php session
		session_regenerate_id(true); // regenerated the session, delete the old one
	}

	function sec_session_destroy() {
		session_destroy();
		// Remove the client's session cookie as well by expiring it
		setcookie(
			ini_get("session.name"),
			"",
			time() - 3600,
			"/",
			"",
			ini_get("session.cookie_secure"),
			ini_get("session.cookie_httponly")
		);
		foreach ($_SESSION as $k => $v) {
			unset($_SESSION[$k]);
		}
	}

	function login($uid)
	{
		sec_session_start();
		$_SESSION['u'] = $uid;
		$_SESSION['g'] = group_check($uid);
		store_user_details($uid);
		$_SESSION['i'] = getenv("REMOTE_ADDR");
		$_SESSION['t'] = time() + (2 * 24 * 60 * 60);
		session_write_close();
	}

	function logout()
	{
		sec_session_destroy();
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
		$groups = getMemberGroups( $uid );
		if ( $groups === false ) {
			//Quick fix for Steam non-responsiveness and private user accounts
			return;
		}
		if ( is_array( $groups ) and $groups['success'] == 1 ) {

			foreach ( $groups['groups'] as $g ) {

				if ( $g['gid'] == getGroupID32() ) {

					return true;
				}
			}
		}
		return false;
	}

	/* TODO consider using this to also grab users game library so we can highlight events */
	/* These calls can take time… async them somehow? */
	function store_user_details($uid)
	{
		$details = getPlayerSummary( $uid );
		if ( $details === false ) {

			// Quick fix for Steam non-responsiveness and private user accounts
			// Cannot get user avatar
			return;
		}
		if ( is_array( $details ) ) {

			$_SESSION['n'] = $details[ 'personaname' ];
			$_SESSION['a'] = $details[ 'avatarfull' ];
		}
		return;
	}

	// Only start/resume a session if we potentially have one
	if (isset($_COOKIE[ini_get("session.name")])) {
		sec_session_start();
	}

