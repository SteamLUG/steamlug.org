<?php
include_once( 'paths.php' );
include_once( 'functions_avatars.php' );
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

	global $castFilePath;
	global $castPublicURL;

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
	$meta['ABSFILENAME']= $castFilePath . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'];
	$meta['ARCHIVE']	= $castPublicURL . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'];

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
 * Private function, returns parsed cast shownotes
 * @param array $lines shownote lines taken from current cast file
 * @param bool $feed produce feed (XML) friendly markup
 * @return string HTML-formatted shownotes!
 */
function _castBody( $lines, $feed = false ) {

	if ( $lines === false )
		return false;

	$text = "";
	foreach ( $lines as $line ) {

		$line = preg_replace_callback( '/\d+:\d+:\d+\s+\*(.*)\*/', function($matches) {
			return "\n<h4>" . slenc($matches[1]) . "</h4>\n<dl class=\"dl-horizontal\">"; }, $line );
		if ( $feed ) {
			$line = preg_replace_callback( '/(\d+:\d+:\d{2})(?!])/', function($matches){
				return "<time datetime=\"" . slenc($matches[1]) . '">' . slenc($matches[1]) . "</time>"; }, $line);
			$line = preg_replace_callback( '/^<time.*$/', function($matches) {
				return "<li>" . $matches[0] . "</li>"; }, $line);
			$line = preg_replace_callback( '/(?i)\b((?:(https?|irc):\/\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/', function($matches) {
				return "[<a href=\"" . slenc($matches[0]) . "\" class=\"text-info\">" . slenc($matches[0]) . "</a>]"; }, $line );
		} else {
			$line = preg_replace_callback( '/(\d+:\d+:\d+)\s+(.*)$/', function($matches) {
				return "<dt>" . slenc($matches[1]) . "</dt>\n\t<dd>" . slenc($matches[2]) . "</dd>"; }, $line );
			$line = preg_replace_callback( '/(\d+:\d+:\d{2})(?!])/', function($matches) {
				return "<time id=\"ts-" . slenc($matches[1]) . "\" datetime=\"" . slenc($matches[1]) . "\">" . slenc($matches[1]) . "</time>"; }, $line );
			$line = preg_replace_callback( '/(?i)\b((?:(https?|irc):\/\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/', function($matches) {
				return "[<a href=\"" . slenc($matches[0]) . "\" class=\"text-info\">source</a>]"; }, $line );
		}
		$line = preg_replace_callback( '/`([^`]*)`/', function($matches) {
			return "<code>" . $matches[1] . "</code>"; }, $line );
		$line = preg_replace_callback( '/(?i)\b((?:(steam):\/\/[^ \n<]*))/', function($matches) {
			return "<a href=\"" . slenc($matches[0]) . "\" class=\"steam-link\">" . slenc($matches[0]) . "</a>"; }, $line );
		$line = preg_replace_callback( '/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/', function($matches) {
			return "<a href=\"mailto:". slenc($matches[0]) . "\" class=\"mail-link\">" . slenc($matches[0]) . "</a>"; }, $line );
		$line = preg_replace_callback( '/((?<=^|\s|\(|>))@([A-Za-z0-9_]+)/i', function($matches) {
			return $matches[1] . "<a href=\"https://twitter.com/" . slenc($matches[2]) . "\" class=\"twitter-link\">" . slenc($matches[2]) . "</a>"; }, $line );
		$line = preg_replace_callback( '/^\n$/', function($matches) {
			return "</dl>\n"; }, $line );
		$line = preg_replace_callback( '/\t\[(\w+)\](.*)/', function($matches) {
			return "\t<dd>&lt;<span class=\"nickname\">" . $matches[1] . "</span>&gt; " . $matches[2] . "</dd>";	}, $line );
		$line = preg_replace_callback( '/\t((?!<dd).*)$/', function($matches) {
			return "\t<dd>" . $matches[1] . "</dd>"; }, $line );
		$line = preg_replace_callback( '/  (.*)/', function($matches) {
			return "<p>" . $matches[1] . "</p>\n";	}, $line );
		$line = preg_replace_callback( '/\[(\w\d+\w\d+)#([0-9:]*)\]/', function($matches) {
			return "<a href=\"/cast/" . $matches[1] . "#ts-" . $matches[2] . "\">" . $matches[1] . " @ " . $matches[2] . "</a>"; }, $line );
		$line = preg_replace_callback( '/\[(\w\d+\w\d+)\]/', function($matches) {
			return "<a href=\"/cast/" . $matches[1] . "\">" . $matches[1] . "</a>"; }, $line );
		if ( $feed ) {
			/* Misc tidy up to match our current output (so we don’t trigger a bunch of new episode downloads */
			$line = preg_replace( '/ter-link\">/', "ter-link\">@", $line);
			$line = preg_replace( '/ class="[^"]*"/', "", $line);
			$line = preg_replace( '/dl>/', "ul>", $line);
			$line = preg_replace( '/dd>/', "li>", $line);
			$line = preg_replace( '/h4>/', "p>", $line);
			$line = preg_replace( '/"\/cast\//', "\"https://steamlug.org/cast/", $line);
			$line = preg_replace( '/\t/', "", $line);
		}
		$text .= $line;
	}
	return $text;
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

	global $castNotesRepo;

	$filename = $castNotesRepo .'/'. $castid . "/episode.txt";
	if ( !file_exists( $filename ) )
		return false;

	// TODO: s02e09 has longest pragma so far; suggest we pick a low cap and enforce it
	$header = file_get_contents( $filename, false, NULL, 0, 1000 );
	$header	= array_slice( explode( "\n", $header ), 0, 15 );
	return _castHeader( $header );
}

/**
 * Returns shownotes for a cast
 * @param string $castid needs to be 's00e00' formatted
 * @return array|false each line of the cast notes, or false if the file does not exist
 */
function getCastBody( $castid = '' ) {

	global $castNotesRepo;

	$filename = $castNotesRepo .'/'. $castid . "/episode.txt";
	if ( !file_exists( $filename ) )
		return false;

	$shownotes = file( $filename );
	return array_slice( $shownotes, 16 );
}
/**
 * Returns slugs for all the existing Casts, whether published or not
 * @param boolean $shallow being set to true bails at the first result (a lazy way to avoid load for getLatestCast()
 * @return string|array|false string (1 match, only when shallow==true), array (1 or more matches), false (no matches)
 */
function getCasts( $shallow = false ) {

	global $castNotesRepo;
	$casts = array( );
	$notes = scandir($castNotesRepo, 1);
	foreach( $notes as $castdir ) {

		if ($castdir === '.' or $castdir === '..' or $castdir === '.git' or $castdir === 'README')
			continue;

		$filename = $castNotesRepo .'/'. $castdir . "/episode.txt";

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

