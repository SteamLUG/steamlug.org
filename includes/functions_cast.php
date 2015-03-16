<?php
include_once('paths.php');
include_once('functions_avatars.php');

// TODO: what other functions do we want in here?
// our shownotes parsing? listing all casts?

function castHeader( $filecontents ) {

	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
						'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL', 'YOUTUBE' ), '');
	foreach ( array_slice( $filecontents, 0, 14 ) as $entry ) {
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
	$casts = scandir($notesPath, 1);
	$cast = array(); // TODO: prepopulate for failure?

	foreach( $casts as $castdir )
	{
		if ($castdir === '.' or $castdir === '..' or $castdir === '.git' or $castdir === 'README')
			continue;

		$filename = $notesPath .'/'. $castdir . "/episode.txt";

		if (!file_exists($filename))
			continue;

		$header = explode( "\n", file_get_contents($filename, false, NULL, 0, 1024) );
		$cast	= castHeader( $header );
		break;
	}
	return $cast;
}
