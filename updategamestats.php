<?php
include_once('includes/session.php');
include_once('includes/functions_steam.php');
include_once('includes/functions_db.php');

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

$gameslist = array( );
foreach ( getSteamGames() as $game ) {

	$tempgame = array ( "name" => $game[ 'name' ], "owners" => 0, "playtime" => 0, "fortnight" => 0 );
	$gameslist[ $game[ 'appid' ] ] = $tempgame;
}
print $date . ": " . count($gameslist) . " known games.\n<br>";


$members = getGroupMembers();
print $date . ": " . print count($members) . " members.\n<br>";

/* pointless stats tracking GET! */
$gamesmin = 2000;
$gamesmax = 0;
$publicMembers = 0;

foreach ( $members as $member ) {

	$memberGames = getMemberGames( $member );
	if ( count( $memberGames ) > 0 ) {

		$publicMembers++;
		/* print $member . " has " . $memberGames[ 'game_count' ] . " games.\n<br>"; */

		if ( ($memberGames[ 'game_count' ] > 0) and array_key_exists( 'games', $memberGames ) ) {

			if ( $memberGames[ 'game_count' ] > $gamesmax )
				$gamesmax = $memberGames[ 'game_count' ];
			if ( $memberGames[ 'game_count' ] < $gamesmin )
				$gamesmin = $memberGames[ 'game_count' ];

			foreach ( $memberGames[ 'games' ] as $game ) {

				if ( array_key_exists( $game[ 'appid' ], $gameslist ) ) {

					$gameslist[ $game[ 'appid' ] ][ 'owners' ]++;
					$gameslist[ $game[ 'appid' ] ][ 'playtime' ] += $game[ 'playtime_forever' ];
					if ( array_key_exists( "playtime_2weeks", $game ) ) {
						$gameslist[ $game[ 'appid' ] ][ 'fortnight' ] += $game[ 'playtime_2weeks' ];
					}
				} else {
					// panic?
					print "Game " . $game[ 'appid' ] . " doesn’t exist in Valve’s game output? lol.\n<br>";
				}
			}
		} else {
			$gamesmin = 0;
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

$storestats = $database->prepare( "INSERT INTO gamestats (date, appid, owners, playtime, fortnight) VALUES (:date, :appid, :owners, :playtime, :fortnight)" );

$storegroupstats = $database->prepare( "INSERT INTO memberstats (date, count, min, max) VALUES (:date, :count, :min, :max)" );

$storegames = $database->prepare( "REPLACE INTO games (appid, name) VALUES (:appid, :name)" );

try {
	$database->beginTransaction( );

	foreach ( $gameslist as $appid=>$game ) {

		if ( $game[ 'owners' ] == 0 )
			continue;

		$storestats->execute( array(
			'data' => $date,
			'appid' => $appid,
			'owners' => $game[ 'owners' ],
			'playtime' => $game[ 'playtime' ],
			'fortnight' => $game[ 'fortnight' ] ) );
	}

	foreach ( $gameslist as $appid=>$game ) {

		$storegames->execute( array(
			'appid' => $appid,
			'name' => $game[ 'name' ] ) );
	}

	$storegroupstats->execute( array(
		'date' => $date,
		'count' => count($members),
		'min' => $gamesmin,
		'max' => $gamesmax ) );
	$database->commit( );

} catch ( Exception $e ) {

	print $date . ": Oops, database failure";
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
