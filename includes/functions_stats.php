<?php
/**
* This should control the reporting/recording of playtime stats, ownership, etc
* and the computation of stats for display
*/
include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

function getYouTubeStat( $videoid ) {
	global $database;

	try {
		$statement = $database->prepare( "SELECT count FROM youtubestats WHERE videoid=:videoid LIMIT 1;" );
		$statement->execute( array( 'videoid' => $videoid ) );
		$views = $statement->fetch( PDO::FETCH_ASSOC );
		if ( $views === false)
			return 0;
		$views = $views[ 'count' ];
	} catch ( Exception $e ) {

		return 0;
	}
	return $views;
}

function refreshYouTubeStats( ) {
	global $database;
	include_once( 'functions_cast.php' );
	include_once( 'functions_youtube.php' );

	$youtubeids = array();
	foreach ( getCasts( ) as $cast ) {

		$meta = getCastHeader( $cast );
		if ( $meta == false )
			continue;
		if ( $meta[ 'YOUTUBE' ] == '' )
			continue;
		array_push( $youtubeids, $meta[ 'YOUTUBE' ] );
	}

	$viewStats = getVideoViews( $youtubeids );
	$storestats = $database->prepare( "INSERT INTO youtubestats (videoid, count) VALUES (:videoid, :count)
		ON DUPLICATE KEY UPDATE videoid=VALUES(videoid), count=VALUES(count);" );

// DRUNK ON SQL - this fails if the key is missing q.q {trying to avoid updating if count !> stored count}
//	$storestats = $database->prepare( "INSERT INTO youtubestats (videoid, count)
//		SELECT :videoid, :count FROM youtubestats WHERE videoid=:videoid AND count < :count
//		ON DUPLICATE KEY UPDATE videoid=VALUES(videoid), count=VALUES(count);" );

	try {
		$database->beginTransaction( );
		foreach( $viewStats as $video => $count ) {
			$storestats->execute( array( 'videoid' => $video, 'count' => $count ) );
		}
		$database->commit( );

	} catch ( Exception $e ) {

		return false;
	}
	return true;
}

/**
* Fetch members stats for all time
* @return array a listing of all the stats, ordered by date
*/
function getMemberStats( ) {
	global $database;
	$statement = $database->prepare( "SELECT * FROM memberstats ORDER BY date asc LIMIT 1000;" );

	try {

		$statement->execute( );
		$memberStats = $statement->fetchAll( PDO::FETCH_ASSOC );
		return $memberStats;
	} catch ( Exception $e ) {

		echo time( ) . ': Oops, database failure: ' . $e;
		return false;
	}
}

/**
* Store the provided stats for the date provided in our db.
* @param datetime $date the date to store info for
* @param integer $publicMembers the count of members in our group with public profiles
* @param integer $members the count of members in our group
* @param integer $appsmin measuring the smallest user account
* @param integer $appsmax measuring the largest user account
* @return void
*/
function storeMemberStats( $date, $publicMembers, $members, $appsmin, $appsmax ) {
	global $database;
	$storegroupstats = $database->prepare( 'INSERT INTO memberstats (date, countpublic, count, min, max) VALUES (:date, :pubcount, :count, :min, :max)' );

	try {
		$storegroupstats->execute( array(
			'date' => $date,
			'pubcount' => $publicMembers,
			'count' => $members,
			'min' => $appsmin,
			'max' => $appsmax ) );
	} catch ( Exception $e ) {

		echo time( ) . ': Oops, database failure: ' . $e;
	}
}
