<?php
	include_once('includes/paths.php');

	$hostAvatars = array(
		"swordfischer" =>	"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/87/87542ec881993993fe2c5268224689538e264fac_full.jpg",
		"ValiantCheese" =>	"//gravatar.com/avatar/916ffbb1cd00d10f5de27ef4f9846390",
		"MimLofBees" =>		"//pbs.twimg.com/profile_images/2458841225/cnm856lvnaz4hhkgz6yg.jpeg",
		"DerRidda" =>		"//pbs.twimg.com/profile_images/2150739768/pigava.jpeg",
		"mnarikka" =>		"//pbs.twimg.com/profile_images/523529572243869696/lb04rKRq.png",
		"Nemoder" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/0d/0d4a058f786ea71153f85262c65bb94490205b59_full.jpg",
		"beansmyname" =>	"//pbs.twimg.com/profile_images/2821579010/3f591e15adcbd026095f85b88ac8a541.png",
		"Corben78" =>		"//pbs.twimg.com/profile_images/313122973/Avatar.jpg",
		"Buckwangs" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
		"Cockfight" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
	);
	/* temporary work-around function while we migrate to new system */
	function gimmeAvatar( $nickname ) {
		global $hostAvatars;

		if ( array_key_exists( $nickname, $hostAvatars ) ) {
			return $hostAvatars[ $nickname ];
		} else
			return '/avatars/' . $nickname . '.png';
	}

	/* we take: ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ / ‘John Drinkwater {URL}’ and spit out Person{} */
	function parsePersonString( $string ) {

		$person = array(); $name = ""; $nickname = ""; $twitter = ""; $avatar = "";
		foreach ( explode( " ", $string ) as $data ) {

			// (@johndrinkwater) or @johndrinkwater
			if ( preg_match( '/\(?@([a-z0-9_]+)\)?/i', $data, $twitterResult ) ) {
				$twitter = $twitterResult[1];

			// (johndrinkwater)
			} else if ( preg_match( '/\(([a-z0-9_]+)\)/i', $data, $nicknameResult) ) {
				$nickname = $nicknameResult[1];

			// {//i.imgur.com/8YkJva1.jpg}
			} else if ( preg_match( '/{(.*)}/i', $data, $avatarURLResult) ) {
				$avatar = $avatarURLResult[1];

			// John Drinkwater
			} else {
				$name .= $data . " ";
			}
		}
		$person['name'] = trim( $name );
		$person['nickname'] = $nickname;
		$person['twitter']	= $twitter;

		if ( strlen( $avatar ) > 0 ) {
			$person['avatar'] = $avatar;
		} else {
			$lookup = trim( $name );
			if ( strlen( $nickname ) > 0 )
				$lookup = $nickname;

			if ( strlen( $twitter ) > 0 )
				$lookup = $twitter;
			$person['avatar'] = gimmeAvatar( $lookup );
		}
		return $person;
	}

	/* user that did it, admin that allowed it, name given, type of event */
	function writeAvatarLog( $userid, $adminid, $assignedname, $event ) {
		global $avatarKeyPath;
		// userid should be 0 for file uploads and gravatar emails
		$steamID = (is_numeric($userid) ? $userid : 0);
		// adminid should be 0 for ILLEGAL events
		$adminAuth = (is_numeric($adminid) ? $adminid : 0);
		// assigned name should report the string given to the script (to log dodgy attempts)
		$eventName = sanitiseName($assignedname);
		// event should be a verb like: add/delete/gravtar etc
		$actionVerbs = array( 'add', 'delete', 'granting', 'gravatar', 'revoke', 'upload', 'error' );
		$event = in_array($event, $actionVerbs ) ? $event : "error";

		$logMsg = $steamID . ':' . $adminAuth  . ':' . $eventName . ':' . $event . ':' . time() . "\n";
		$logFile = $avatarKeyPath . '/logfile';
		$value = file_put_contents( $logFile, $logMsg, FILE_APPEND | LOCK_EX );
	}

	function readAvatarLog( ) {
		global $avatarKeyPath;
		$logFile = $avatarKeyPath . '/logfile';
		return file_get_contents( $logFile );
	}

	/* This is a user facing variable, so we want to make damn sure they don’t try to
		abuse it to discover info about our server. */
	function sanitiseName( $nameIn ) {
		/* if in the future we want to be more lax… */
		$nameOut = preg_replace('/[^\w]+/', '', $nameIn );
		if ( $nameOut == "" )
			return "it-was-blank";
		if ( $nameOut == "logfile" )
			return "it-was-blank";
		return $nameOut;
	}

	/* This is a user facing variable, so we want to make damn sure they don’t try to
		abuse it */
	function sanitiseKey( $keyIn ) {
		// this should be a short string of length 32
		return ( (strlen($keyIn) == 32 && ctype_xdigit($keyIn)) ? $keyIn : "LITTLEHACKER" );
	}

if ( extension_loaded('curl') ) {

	/* this can be called by users (with permission) or an admin directly
		We have already tested for permission.
		overwrite might not be needed, it is there atm though
		Returns if we successfully wrote the file */
	function storeURL( $url, $fileLocation, $overwrite = false ) {

		if ( file_exists( $fileLocation ) and !is_dir( $fileLocation ) and (!$overwrite) ) {
			return false;
		}
		try {

			$fileOutput = fopen( $fileLocation, "w");
			$ch = curl_init();
			// consider? set_time_limit();
			curl_setopt_array($ch, array(
				CURLOPT_FILE    => $fileOutput,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_URL     => $url,
			) );

			$result = curl_exec($ch);
			curl_close($ch);
			fclose( $fileOutput );
			return $result;

		} catch (Exception $e) {
			return false;
		}
	}
} else {
	function storeURL( $url, $fileLocation, $overwrite = false ) {
		return false;
	}
}
?>
