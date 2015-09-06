<?php
include_once('paths.php');
include_once('functions_avatars.php');
$season  = isset($_GET["s"]) ? intval($_GET["s"]) : "0";
$season  = str_pad($season, 2, '0', STR_PAD_LEFT);
$episode = isset($_GET["e"]) ? intval($_GET["e"]) : "0";
$episode = str_pad($episode, 2, '0', STR_PAD_LEFT);
$slug = 's' . $season . 'e' . $episode;

/* TODO retire function( slug ) for function( season, episode ) */

// TODO: what other functions do we want in here?
// our shownotes parsing? listing all casts?
// validate File Headers?

/**
 * Private function, returns header metadata prepared for use
 * @param array $header slice of length 14, taken from current cast file
 * @return array
 */
function _castHeader( $header ) {

	global $filePath;
	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
				'SEASON', 'EPISODE', 'DURATION', 'FILENAME', 'RATING',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL', 'YOUTUBE' ), '');
	foreach ( $header as $entry ) {
		list($k, $v) = explode( ':', $entry, 2 );
		$meta[$k] = trim($v);
	}
	$meta['EPISODE']	= str_pad($meta['EPISODE'], 2, '0', STR_PAD_LEFT);
	$meta['SEASON']		= str_pad($meta['SEASON'], 2, '0', STR_PAD_LEFT);
	$meta['SLUG']		= 's' . $meta['SEASON'] . 'e' . $meta['EPISODE'];
	$meta['ABSFILENAME']= $filePath . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'];

	// Explicit -> yes, Clean -> clean, * -> no
	$meta[ 'ISEXPLICIT' ] = ( $meta[ 'RATING' ] == 'Explicit' ? 'yes' :
		($meta[ 'RATING' ] == 'Clean' ? 'clean' : 'no' ) );
	$meta[ 'MEDIARATING' ] = ( $meta[ 'RATING' ] == 'Explicit' ? 'adult' : 'nonadult' );

	$meta['HOSTS']		= array_map('trim', explode(',', $meta['HOSTS']));
	$meta['HOSTS2']		= array();
	foreach ($meta['HOSTS'] as $Host) { $meta['HOSTS2'][] = parsePersonString( $Host );	}

	$meta['GUESTS']		= array_map('trim', explode(',', $meta['GUESTS']));
	if ( $meta['GUESTS'][0] == "" )
		array_pop( $meta['GUESTS'] );
	$meta['GUESTS2']	= array();
	foreach ($meta['GUESTS'] as $Guest) { $meta['GUESTS2'][] = parsePersonString( $Guest ); }

	return $meta;
}

/**
 * Returns data similar to getCastHeader(), metadata about an episode, rather than the episode slug
 * if you need that reference, it is returned in the array as [ 'SLUG' ]
 * @return array a dictionary of metadata for this file, with all keys in uppercase
 */
function getLatestCast( ) {

	$latest = getCasts( true );
	if ( !$latest )
		return false;

	return getCastHeader( $latest );
}

/**
 * Returns header metadata for a cast, already prepared for use
 * @param string $castid needs to be 's00e00' formatted
 * @return array a dictionary of metadata for this file, with all keys in uppercase
 */
function getCastHeader( $castid = '' ) {

	global $notesPath;

	$filename = $notesPath .'/'. $castid . "/episode.txt";
	if ( !file_exists( $filename ) )
		return false;

	// TODO: s02e09 has longest pragma so far; suggest we pick a low cap and enforce it
	$header = file_get_contents( $filename, false, NULL, 0, 1000 );
	$header	= array_slice( explode( "\n", $header ), 0, 16 );
	return _castHeader( $header );
}

/**
 * Returns shownotes for a cast, already prepared for use
 * @param string $castid needs to be 's00e00' formatted
 * @return array each line of the cast notes
 */
function getCastBody( $castid = '' ) {

	global $notesPath;

	$filename = $notesPath .'/'. $castid . "/episode.txt";
	if ( !file_exists( $filename ) )
		return false;

	$shownotes = file( $filename );
	return array_slice( $shownotes, 16 );
}
/**
 * Returns slugs for all the existing Casts, whether published or not
 * @param boolean $shallow being set to true bails at the first result (a lazy way to avoid load for getLatestCast()
 * @return boolean|string|array false (no matches), string (1 match, only when shallow==true), array (1 or more matches)
 */
function getCasts( $shallow = false ) {

	global $notesPath;
	$casts = array( );
	$notes = scandir($notesPath, 1);
	foreach( $notes as $castdir ) {

		if ($castdir === '.' or $castdir === '..' or $castdir === '.git' or $castdir === 'README')
			continue;

		$filename = $notesPath .'/'. $castdir . "/episode.txt";

		if ( !file_exists( $filename ) )
			continue;

		if ( $shallow )
			return $castdir;

		array_push( $casts, $castdir );
	}
	if ( empty( $casts ) )
		return false;

	return $casts;
}

