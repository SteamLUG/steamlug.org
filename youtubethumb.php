<?php
header('Content-Encoding: UTF-8');
header('Content-Type: image/svg+xml');

include_once('includes/session.php');
include_once('includes/functions_avatars.php');
include_once('includes/functions_steam.php');
include_once('includes/functions_cast.php');
include_once('includes/functions_castvideo.php');

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

/* User wanting to see a specific cast, and shownotes file exists */
if ( $season !== "00" && $episode !== "00" ) {

	// We do no testing here, as this function will return an image even if
	// this episode does not exist (it will test the user, though!)
	print generateImage( $season, $episode );
}
