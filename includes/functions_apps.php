<?php
/**
* Function collection for fetching and storing details about Steam apps in our db.
*/

/**
*/

// Unsure on the overlap between this and functions_stats for app playtimes, etc

include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

/**
* Retrieve a listing of the known apps from our database
* @return array|false a list of apps known, indexed by appid, with members of: name, owners, playtime, fortnight, playersfortnight
*/
function getSteamAppsDB( ) {

	global $database;
	$statement = $database->prepare( "SELECT appid, name FROM apps LIMIT 30000;" );

	try {

		$statement->execute( );
		$apps = $statement->fetchAll( PDO::FETCH_ASSOC );
		$appslist = array( );
		foreach ( $apps as $app ) {
			$appslist[ $app[ 'appid' ] ] = array ( "name" => $app[ 'name' ], "owners" => 0, "playtime" => 0, "fortnight" => 0, "playersfortnight" => 0 );
		}
		return $appslist;

	} catch ( Exception $e ) {

		echo time( ) . ': Oops, database failure: ' . $e;
	}
	return false;
}

/**
* Store the provided list in our database, updating known apps names if they have changed
* @param array $apps a hash of apps, indexed by appid, with the member 'name'
* @return void
*/
function storeAppsDB( $apps ) {

	global $database;
	$statement = $database->prepare( "INSERT INTO apps (appid, name, onlinux) VALUES (:appid, :name, :onlinux)
		ON DUPLICATE KEY UPDATE appid=VALUES(appid), name=VALUES(name), onlinux=VALUES(onlinux);" );

	try {
		$database->beginTransaction( );

		foreach ( $apps as $appid=>$app ) {

			$statement->execute( array(
				'appid' => $appid,
				'onlinux' => $app[ 'onlinux' ],
				'name' => $app[ 'name' ] ) );
		}

		$database->commit( );

	} catch ( Exception $e ) {

		echo time( ) . ': Oops, database failure: ' . $e;
	}

}

/**
* Get details about this app from our database, retrieving additional information
* @param integer $appid Steam's ID for the app
* @return array a hash of the details for this app, with members for name, appid, owners, playtime, fortnight, playersfortnight
*/
function getApp( $appid ) {

	global $database;
	$statement = $database->prepare( "SELECT apps.appid as appid, name, owners, fortnight, playersfortnight, onlinux, date FROM apps
					LEFT JOIN appstats ON apps.appid = appstats.appid WHERE apps.appid = :appid ORDER BY date desc LIMIT 1;" );

	try {

		$statement->execute( array( 'appid' => $appid ) );
		if ( $statement->rowCount( ) > 0 ) {
			$app = $statement->fetch( PDO::FETCH_ASSOC );
			return $app;
		} else {
			return false;
		}

	} catch ( Exception $e ) {

		echo time( ) . ': Oops, database failure: ' . $e;
	}
	return false;
}


/**
* Returns a list of most recent events for the AppID given, along with some details for the event,
* and attendance too.
* @param string $appid AppID of the Game we want the details for
* @param integer $limit How many events to return, defaulting to 6
* @return array hash of attended events, with details, ordered by time the events were
*/
function getRecentEventsForApp( $appid, $limit = 6 ) {

	/* TODO: should this be only dates in past, include dates in future, what? */
	global $database;
	try {
		// $database->beginTransaction( );
		/* TODO: safe-ify $id */
		$statement = $database->prepare( "SELECT appid, events.eventid, count(eventattendance.eventid) as players, title, utctime, clanid FROM steamlug.events
			LEFT JOIN eventattendance ON eventattendance.eventid = events.eventid WHERE events.appid = :appid GROUP BY eventattendance.eventid ORDER BY utctime desc limit :limit;" );
		$statement->execute( array(
			'appid' => $appid,
			'limit' => $limit ) );
		$events = $statement->fetchAll( PDO::FETCH_ASSOC );
		// $database->commit( );
		return $events;
	} catch ( Exception $e ) {

		return false;
	}
}
