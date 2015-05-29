<?php
include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

/*
	Fetch and store details about steam titles in the db
	Should also do queries on the other tables for: previous/upcoming events for app
	Unsure on the overlap between this and functions_stats for app playtimes, etc
*/

function getSteamAppsDB( ) {

	global $database;
	$statement = $database->prepare( "SELECT appid, name FROM apps LIMIT 30000;" );

	try {

		$statement->execute( );
		$apps = $statement->fetchAll( PDO::FETCH_ASSOC );
		$appslist = array( );
		foreach ( $apps as $app ) {
			$appslist[ $app[ 'appid' ] ] = array ( "name" => $app[ 'name' ], "owners" => 0, "playtime" => 0, "fortnight" => 0 );
		}
		return $appslist;

	} catch ( Exception $e ) {

		print now(). ": Oops, database failure: " . $e;
	}
	return false;
}


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

function getApp( $appid ) {

	global $database;
	$statement = $database->prepare( "SELECT appid, name FROM apps WHERE appid = :appid LIMIT 1;" );

	try {

		$statement->execute( array( 'appid' => $appid ) );
		$app = $statement->fetch( PDO::FETCH_ASSOC );
		return array ( "name" => $app[ 'name' ], "appid" => $appid, "owners" => 0, "playtime" => 0, "fortnight" => 0 );

	} catch ( Exception $e ) {

		print now(). ": Oops, database failure: " . $e;
	}
	return false;
}
