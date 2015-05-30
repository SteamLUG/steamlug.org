<?php
/**
*
*/
/**
*/


include_once('paths.php');

/**
* A utility function to parse our HOST string components
* @param string $string of the form: ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ / ‘John Drinkwater {URL}’
* @return array with members twitter, nickname, avatar, name
*/
	function parsePersonString( $string ) {

		$person = array_fill_keys( array( 'twitter', 'nickname', 'name', 'avatar' ), '');
		foreach ( explode( " ", $string ) as $data ) {

			// (@johndrinkwater) or @johndrinkwater
			if ( preg_match( '/\(?@([a-z0-9_]+)\)?/i', $data, $twitterResult ) ) {
				$person['twitter'] = $twitterResult[1];

			// (johndrinkwater)
			} else if ( preg_match( '/\(([a-z0-9_]+)\)/i', $data, $nicknameResult) ) {
				$person['nickname'] = $nicknameResult[1];

			// {//i.imgur.com/8YkJva1.jpg}
			} else if ( preg_match( '/{(.*)}/i', $data, $avatarURLResult) ) {
				$person['avatar'] = $avatarURLResult[1];

			// John Drinkwater
			} else {
				$person['name'] .= $data . " ";
			}
		}
		$person['name'] = trim( $person['name'] );

		if ( strlen( $person['avatar'] ) > 0 ) {
		} else {
			$lookup = $person['name'];
			if ( strlen( $person['nickname'] ) > 0 )
				$lookup = $person['nickname'];

			if ( strlen( $person['twitter'] ) > 0 )
				$lookup = $person['twitter'];
			$person['avatar'] = '/avatars/' . $lookup . '.png';
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

	/* Fetches all the PNG file entries in avatarFilePath
	*/
	function listAvatars( ) {

		global $avatarFilePath;
		$avatars = array();
		foreach( scandir($avatarFilePath, 1) as $avatar ) {

			if ( $avatar === '.' or $avatar === '..' or is_dir( $avatar )
				or ( substr( $avatar, -3 , 3 ) !== 'png' ) ) {
				continue;
			}
			array_push( $avatars, substr( $avatar, 0 , -4 ) );
		}
		asort( $avatars );
		return $avatars;
	}

	/* read from a file of some type, write to a PNG file */
	function resizeAvatar( $incoming, $outgoing, $overwrite = false ) {

		if ( file_exists( $outgoing ) and !is_dir( $outgoing ) and ( !$overwrite ) ) {
			return false;
		}
		if ( !file_exists( $incoming ) and !is_dir( $incoming ) ) {
			return false;
		}
		
		$commandresize = "convert -resize 256x256 -type optimize -strip {$incoming} png:{$outgoing}";
		ob_start( );
		echo shell_exec( $commandresize . ' 2>&1' );
		$debugoutput = ob_get_clean();

		if ( file_exists( $outgoing ) ) {
			/* do anything here? */
			return true;
		} else {
			return false;
		}
	}

if ( extension_loaded('curl') ) {

	/* this can be called by users (with permission) or an admin directly
		We have already tested for permission.
		overwrite might not be needed, it is there atm though
		Returns if we successfully wrote the file */
	function writeURLToLocation( $url, $fileLocation, $overwrite = false ) {

		if ( file_exists( $fileLocation ) and !is_dir( $fileLocation ) and (!$overwrite) ) {
			return false;
		}
		try {

			$fileOutput = fopen( $fileLocation, "w");
			$ch = curl_init();
			// consider? set_time_limit();
			curl_setopt_array($ch, array(
				CURLOPT_FILE    => $fileOutput,
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
	function writeURLToLocation( $url, $fileLocation, $overwrite = false ) {
		return false;
	}
}
