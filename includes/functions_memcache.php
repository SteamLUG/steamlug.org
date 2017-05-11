<?php
/**
* Function collection for fetching and storing variables in our memcache.
*/

include_once( 'creds.php' );

/**
* This connects to the memcache store
* @return object handle to the memcache store, or false if there was a failure
*/
function connectMemcache( ) {

	$conn = false;
	try {
		$conn = memcache_connect( getMCHost( ), getMCPort( ) );
	} catch( Exception $e ) {
		echo $e->getMessage( );
	}
	return $conn;
}

/**
* This fetches a variable from the memcache store, if the variable is not found
* it will call the provided function, and store that in the system with the
* expiry time supplied
* @param object $memcache a connection to the memcache daemon
* @param object $variable a handle to the name of the data which it is stored under
* @param integer $expiry number in seconds for the data to persist
* @param function $function a function that returns the data you wish to store, in case the memcache data has expired
* @return object the data requested, either fresh or < expiry
*/
function fetchOrStore( $memcache, $variable, $expiry, $function ) {
	$value = memcache_get( $memcache, $variable );
	if ( ! $value ) {
		$value = $function( );
		memcache_set( $memcache, $variable, $value, false, $expiry );
		// TODO this can fail to set, though we return $value so nothing will be lost?
	}
	return $value;
}
