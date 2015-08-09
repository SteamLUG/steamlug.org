<?php
date_default_timezone_set('UTC');
include_once('includes/session.php');
include_once('includes/functions_steam.php');
include_once('includes/functions_db.php');
include_once('includes/functions_apps.php');

/* this can run for a good few minutes… about 80 users per minute ?! */
set_time_limit( 5400 );

// are we logged in? no → leave
if ( !login_check() ) {
	header( "Location: /" );
	exit();
} else {
	$me = $_SESSION['u'];
}
// are we admin? no → leave
if ( in_array( $me, getAdmins() ) ) {
} else {
	header( "Location: /" );
	exit();
}

$date = date( "Y-m-d" );
print $date . ": Starting stats gathering: " . date("c") . "\n<br>";

$database = connectDB();

foreach(  $database->query( "SELECT count(1) AS number FROM steamlug.memberstats WHERE `date`='" . $date . "' LIMIT 1" ) as $res ) {

	if ( $res[ 'number' ] == "1" ) {
		print $date . ": We have already captured stats for today! Ending script.";
		exit;
	}
}

if ( true ) {

	/* pick heuristic to decide to pull from db */
	// TODO: move this to _stats, make it callable to refresh our intl db without being
	// part of the stats update

	// request Steam give us their latest this of games
	$appslist = array( );
	foreach ( getSteamApps( ) as $app ) {

		$appslist[ $app[ 'appid' ] ] = array ( "name" => $app[ 'name' ], "owners" => 0, "playtime" => 0, "fortnight" => 0, "playersfortnight" => 0 );
	}
	storeAppsDB( $appslist );

} else {

	$appslist = getSteamAppsDB( );
}
print $date . ": " . count($appslist) . " known apps.\n<br>";


$members = getGroupMembers();
print $date . ": " . print count($members) . " members.\n<br>";

/* pointless stats tracking GET! */
$appsmin = 2000;
$appsmax = 0;
$publicMembers = 0;

foreach ( $members as $member ) {

	$memberGames = getMemberGames( $member );
	if ( count( $memberGames ) > 0 ) {

		$publicMembers++;
		/* print $member . " has " . $memberGames[ 'game_count' ] . " apps.\n<br>"; */

		if ( ($memberGames[ 'game_count' ] > 0) and array_key_exists( 'games', $memberGames ) ) {

			if ( $memberGames[ 'game_count' ] > $appsmax )
				$appsmax = $memberGames[ 'game_count' ];
			if ( $memberGames[ 'game_count' ] < $appsmin )
				$appsmin = $memberGames[ 'game_count' ];

			foreach ( $memberGames[ 'games' ] as $app ) {

				if ( array_key_exists( $app[ 'appid' ], $appslist ) ) {

					$appslist[ $app[ 'appid' ] ][ 'owners' ]++;
					$appslist[ $app[ 'appid' ] ][ 'playtime' ] += $app[ 'playtime_forever' ];
					if ( array_key_exists( "playtime_2weeks", $app ) ) {
						$appslist[ $app[ 'appid' ] ][ 'fortnight' ] += $app[ 'playtime_2weeks' ];
						$appslist[ $app[ 'appid' ] ][ 'playersfortnight' ]++;
					}
				} else {
					// panic?
					print "Game " . $app[ 'appid' ] . " doesn’t exist in Valve’s app output? lol.\n<br>";
				}
			}
		} else {
			$appsmin = 0;
			// eh? Faulty data from Steam?
			print $member . " has zero games on their profile.\n<br>";
		}

	} else {
		// User has a private profile. Do something with this knowledge?
		print $member . " has a private profile.\n<br>";
	}
	flush( );
}

print $date . ": Completed stats gathering: " . date("c") . "\n<br>";
print $date . ": " . $publicMembers . " public member profiles of " . count($members ) . " members read on " . date( "c" ) . "\n<br>";
flush( );

$storestats = $database->prepare( "INSERT INTO appstats (date, appid, owners, playtime, fortnight, playersfortnight) VALUES (:date, :appid, :owners, :playtime, :fortnight, :playersfortnight)" );

$storegroupstats = $database->prepare( "INSERT INTO memberstats (date, count, min, max) VALUES (:date, :count, :min, :max)" );

$storeapps = $database->prepare( "INSERT INTO apps (appid, name) VALUES (:appid, :name)
		ON DUPLICATE KEY UPDATE appid=VALUES(appid), name=VALUES(name);" );

try {
	$database->beginTransaction( );

	foreach ( $appslist as $appid=>$app ) {

		if ( $app[ 'owners' ] == 0 )
			continue;

		$storestats->execute( array(
			'date' => $date,
			'appid' => $appid,
			'owners' => $app[ 'owners' ],
			'playtime' => $app[ 'playtime' ],
			'fortnight' => $app[ 'fortnight' ],
			'playersfortnight' => $app[ 'playersfortnight' ] ) );
	}

	foreach ( $appslist as $appid => $app ) {

		$storeapps->execute( array(
			'appid' => $appid,
			'name' => $app[ 'name' ] ) );
	}

	$storegroupstats->execute( array(
		'date' => $date,
		'count' => count($members),
		'min' => $appsmin,
		'max' => $appsmax ) );
	$database->commit( );

} catch ( Exception $e ) {

	print $date . ": Oops, database failure: " . $e;
}

/* XXX where to write this?
$logger = fopen( 'stats.log', 'a' );
fwrite( $logger, $date . " Stored " . count($members) . " member profiles." );
fclose( $logger );
*/

print $date . ": Completed stats storing: " . date("c") . "\n<br>";
$completion = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
print $date . ": Process Time: {$completion}.";
print $date . ": Memory: " . memory_get_usage( ) . "\n<br>";
