<?php
require_once( 'MurmurQuery.php' );
include_once( 'paths.php' );

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

