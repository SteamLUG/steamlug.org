<?php
include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

	function getRecentAttendance( $steamid ) {

		global $database;
		try {
			// $database->beginTransaction( );
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "SELECT eventattendance.eventid, utctime, appid, title, clanid FROM steamlug.eventattendance
				LEFT JOIN events ON eventattendance.eventid = events.eventid WHERE eventattendance.steamid = :steamid ORDER BY utctime desc limit 10;" );
			$statement->execute( array( 'steamid' => $steamid ) );
			$events = $statement->fetchAll( PDO::FETCH_ASSOC );
			// $database->commit( );
			return $events;
		} catch ( Exception $e ) {

			return false;
		}
	}

	function getEventAttendance( $eventid ) {

		global $database;
		try {
			// $database->beginTransaction( );
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "SELECT eventattendance.steamid, personaname, profileurl, avatar FROM steamlug.eventattendance
				LEFT JOIN members ON eventattendance.steamid = members.steamid WHERE eventattendance.eventid = :eventid ORDER BY members.steamid desc limit 100;" );
			$statement->execute( array( 'eventid' => $eventid ) );
			$players = $statement->fetchAll( PDO::FETCH_ASSOC );
			// $database->commit( );
			return $players;
		} catch ( Exception $e ) {

			return false;
		}
	}


	// add player to event
	function addPlayerEventAttendance( $eventid, $steamid ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: once event capture is automated, retire this logging? */
			logDB( 'EVENTATTEND ADD ' . $eventid . ':' . $steamid );
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "INSERT INTO steamlug.eventattendance (eventid, steamid) VALUES (:eventid, :steamid)
				ON DUPLICATE KEY UPDATE eventid=VALUES(eventid), steamid=VALUES(steamid);" );
			$statement->execute( array( 'eventid' => $eventid, 'steamid' => $steamid ) );
			$addition = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $addition;
		} catch ( Exception $e ) {

			return false;
		}
	}

	// remove player from event (shouldnt be used often)
	function removePlayerEventAttendance( $eventid, $steamid ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: safe-ify $id */
			logDB( 'EVENTATTEND REM ' . $eventid . ':' . $steamid );
			$statement = $database->prepare( "DELETE FROM steamlug.eventattendance WHERE eventid = :eventid AND steamid = :steamid LIMIT 1;" );
			$statement->execute( array( 'eventid' => $eventid, 'steamid' => $steamid ) );
			$subtraction = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $subtraction;
		} catch ( Exception $e ) {

			return false;
		}
	}
