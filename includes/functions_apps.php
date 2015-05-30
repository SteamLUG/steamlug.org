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

		print now(). ": Oops, database failure: " . $e;
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
	$statement = $database->prepare( "INSERT INTO apps (appid, name) VALUES (:appid, :name)
		ON DUPLICATE KEY UPDATE appid=VALUES(appid), name=VALUES(name);" );

	try {
		$database->beginTransaction( );

		foreach ( $apps as $appid=>$app ) {

			$statement->execute( array(
				'appid' => $appid,
				'name' => $app[ 'name' ] ) );
		}

		$database->commit( );

	} catch ( Exception $e ) {

		print now(). ": Oops, database failure: " . $e;
	}

}

/**
* Get details about this app from our database, retrieving additional information
* @param int $appid Steam's ID for the app
* @return array a hash of the details for this app, with members for name, appid, owners, playtime, fortnight, playersfortnight
*/
function getApp( $appid ) {

	global $database;
	$statement = $database->prepare( "SELECT appid, name FROM apps WHERE appid = :appid LIMIT 1;" );

	// TODO we want to expand this to expose our "for Linux" knowledge from steamdb linux list
	try {

		$statement->execute( array( 'appid' => $appid ) );
		$app = $statement->fetch( PDO::FETCH_ASSOC );
		return array ( "name" => $app[ 'name' ], "appid" => $appid, "owners" => 0, "playtime" => 0, "fortnight" => 0, "playersfortnight" => 0 );

	} catch ( Exception $e ) {

		print now(). ": Oops, database failure: " . $e;
	}
	return false;
}
