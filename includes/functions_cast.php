<?php
include_once('paths.php');
include_once('functions_avatars.php');
$season  = isset($_GET["s"]) ? intval($_GET["s"]) : "0";
$season  = str_pad($season, 2, '0', STR_PAD_LEFT);
$episode = isset($_GET["e"]) ? intval($_GET["e"]) : "0";
$episode = str_pad($episode, 2, '0', STR_PAD_LEFT);

// TODO: what other functions do we want in here?
// our shownotes parsing? listing all casts?

function castHeaderFromString( $filecontents ) {

	return castHeader( array_slice( explode( "\n", $filecontents ), 0, 14 ) );
}

function castHeader( $header ) {

	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
						'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL', 'YOUTUBE' ), '');
	foreach ( $header as $entry ) {
		list($k, $v) = explode( ':', $entry, 2 );
		$meta[$k] = trim($v);
	}
	$meta['EPISODE']	= str_pad($meta['EPISODE'], 2, '0', STR_PAD_LEFT);
	$meta['SEASON']		= str_pad($meta['SEASON'], 2, '0', STR_PAD_LEFT);

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

function getLatestCast( ) {

	global $notesPath;

	foreach( scandir($notesPath, 1) as $castdir )
	{
		if ($castdir === '.' or $castdir === '..' or $castdir === '.git' or $castdir === 'README')
			continue;

		$filename = $notesPath .'/'. $castdir . "/episode.txt";
		if (!file_exists($filename))
			continue;

		// TODO: s02e09 has longest pragma so far; suggest we pick a low cap and enforce it 
		$header = file_get_contents($filename, false, NULL, 0, 950);
		return castHeaderFromString( $header );
	}
	return array();
}
