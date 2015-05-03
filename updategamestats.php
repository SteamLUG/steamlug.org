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

print "Starting stats gathering: " . date("c") . "\n<br>";

$database = connectDB();
/* TODO find if mysql has a similar call?
 $database->query( "PRAGMA synchronous = OFF" );
*/

$gameslist = array( );
foreach ( getSteamGames() as $game ) {
	
	$tempgame = array ( "name" => $game[ 'name' ], "owners" => 0, "playtime" => 0, "fortnight" => 0 );
	$gameslist[ $game[ 'appid' ] ] = $tempgame;
}
print count($gameslist) . " known games.\n<br>";


$members = getGroupMembers();
print count($members) . " members.\n<br>";

/* pointless stats tracking GET! */
$gamesmin = 2000;
$gamesmax = 0;

foreach ( $members as $member ) {

	$memberGames = getMemberGames( $member );
	if ( count( $memberGames ) > 0 ) {

		print $member . " has " . $memberGames[ 'game_count' ] . " games.\n<br>";

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

print "Completed stats gathering: " . date("c") . "\n<br>";

$date = date( "Y-m-d" );

/*
CREATE TABLE `steamlug`.`gamestats` (
  `date` DATE NOT NULL,
  `appid` INT NOT NULL,
  `owners` INT NULL,
  `playtime` BIGINT NULL,
  `fortnight` INT NULL,
  PRIMARY KEY (`date`, `appid`),
  UNIQUE INDEX (`date`, `appid`));
*/

$storestats = $database->prepare( "INSERT INTO gamestats (date, appid, owners, playtime, fortnight) VALUES (?,?,?,?,?)" );

/*
CREATE TABLE `steamlug`.`memberstats` (
  `date` DATE NOT NULL,
  `count` INT NULL,
  `min` INT NULL,
  `max` INT NULL,
  PRIMARY KEY (`date`));
*/

$storegroupstats = $database->prepare( "INSERT INTO memberstats (date, count, min, max) VALUES (?,?,?,?)" );

/*
CREATE TABLE `steamlug`.`games` (
  `appid` INT NOT NULL,
  `name` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`appid`));
*/
$storegames = $database->prepare( "REPLACE INTO games (appid, name) VALUES (?,?)" );

$storegroupstats->execute( array( $date, count($members), $gamesmin, $gamesmax ) );

foreach ( $gameslist as $appid=>$game ) {

	if ( $game[ 'owners' ] == 0 )
		continue;

	$storestats->execute( array( $date, $appid, $game[ 'owners' ], $game[ 'playtime' ], $game[ 'fortnight' ] ) );
	/*print "[" . $appid . "] " . $game[ 'name' ] . ": " . $game[ 'owners' ] . " owner, " . $game[ 'playtime' ] . " playtime, " .
		$game[ 'fortnight' ] . " fortnightly playtime.\n<br>";*/
} 

foreach ( $gameslist as $appid=>$game ) {

	$storegames->execute( array( $appid, $game[ 'name' ] ) );
}

/* XXX where to write this?
$logger = fopen( 'stats.log', 'a' );
fwrite( $logger, $date . " Stored " . count($members) . " member profiles." );
fclose( $logger );
*/

print "Complete!\n";
$completion = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
print "Process Time: {$completion}.";
print "Memory: " . memory_get_usage( ) . "\n<br>";
