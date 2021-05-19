<?php
/**
* Function collection for fetching and storing details for Event attendance in our db.
*/


/**
*/


include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );


	/**
	* Returns a list of most recently attended events for the SteamID given, along with some details for the event
	* @param string $steamid SteamID of the user we want the details for
	* @param integer $limit How many events to return, defaulting to 6
	* @return array hash of attended events, with details, ordered by time the events were
	*/
	function getRecentAttendance( $steamid, $limit = 6 ) {

		global $database;
		try {
			// $database->beginTransaction( );
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "SELECT eventattendance.eventid, utctime, appid, title, clanid FROM eventattendance
				LEFT JOIN events ON eventattendance.eventid = events.eventid WHERE eventattendance.steamid = :steamid ORDER BY utctime desc limit :limit;" );
			$statement->execute( array(
				'steamid' => $steamid,
				'limit' => $limit ) );
			$events = $statement->fetchAll( PDO::FETCH_ASSOC );
			// $database->commit( );
			return $events;
		} catch ( Exception $e ) {

			return false;
		}
	}


	/**
	* Returns a list of SteamIDs along with any member details they may have shared with us for the event requested
	* @param string $eventid Steam's reference number for the event
	* @return array hash of attending SteamIDs, with details, ordered by SteamID
	*/
	function getEventAttendance( $eventid ) {

		global $database;
		try {
			// $database->beginTransaction( );
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "SELECT eventattendance.steamid, personaname, profileurl, avatar FROM eventattendance
				LEFT JOIN members ON eventattendance.steamid = members.steamid WHERE eventattendance.eventid = :eventid ORDER BY members.steamid desc limit 100;" );
			$statement->execute( array( 'eventid' => $eventid ) );
			$players = $statement->fetchAll( PDO::FETCH_ASSOC );
			// $database->commit( );
			return $players;
		} catch ( Exception $e ) {

			return false;
		}
	}


	/**
	* Adds supplied SteamID to Event
	* @param string $eventid Steam's reference number for the event
	* @param string $steamid SteamID of the user we want to add
	* @return boolean whether the action completed or not
	*/
	function addPlayerEventAttendance( $eventid, $steamid ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: once event capture is automated, retire this logging? */
			logDB( 'EVENTATTEND ADD ' . $eventid . ':' . $steamid );
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "INSERT INTO eventattendance (eventid, steamid) VALUES (:eventid, :steamid)
				ON DUPLICATE KEY UPDATE eventid=VALUES(eventid), steamid=VALUES(steamid);" );
			$statement->execute( array( 'eventid' => $eventid, 'steamid' => $steamid ) );
			$addition = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $addition;
		} catch ( Exception $e ) {

			return false;
		}
	}


	/**
	* Removes supplied SteamID from Event
	* @param string $eventid Steam's reference number for the event
	* @param string $steamid SteamID of the user we want to remove
	* @return boolean whether the action completed or not
	*/
	function removePlayerEventAttendance( $eventid, $steamid ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: safe-ify $id */
			logDB( 'EVENTATTEND REM ' . $eventid . ':' . $steamid );
			$statement = $database->prepare( "DELETE FROM eventattendance WHERE eventid = :eventid AND steamid = :steamid LIMIT 1;" );
			$statement->execute( array( 'eventid' => $eventid, 'steamid' => $steamid ) );
			$subtraction = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $subtraction;
		} catch ( Exception $e ) {

			return false;
		}
	}
