<?php
/**
* Simple wrapper around MurmurQuery, to get us the ability to swap out this helper script in future
*/

/**
*/

require_once( 'MurmurQuery.php' );
include_once( 'paths.php' );


/**
* Queries the mumble server on the default port, returns a tidied up version of
* the regular reply from our qt-mumur server
* @return array hash tree containing channels, users, from the queried server
*/
function getMumble( ) {

	global $mumbleServer;
	$murmur = new MurmurQuery();
	$murmur->setup( array (
				'host'		=> $mumbleServer,
				'port'		=> 27800,
				'timeout'	=> 200,
				'format'	=> 'json' ) );
	$murmur->query();
	return $murmur;
}

