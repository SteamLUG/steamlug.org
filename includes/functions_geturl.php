<?php

if ( extension_loaded('curl') ) {

	/* Our simple replacement for file_get_contents, with added bonus of param & header config
	* If it fails, it will return a number. Currently naïvely tests for only a few errors.
	*/
	function geturl( $url, $get = array(), $header = array() ) {

		$curl = curl_init( );
		curl_setopt_array( $curl, array(
				CURLOPT_URL => $url . '?' . http_build_query( $get ),
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 6,
				CURLOPT_TIMEOUT => 8 )
				);
		$result = curl_exec( $curl );
		$status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		if ( ( $status == 404 ) || ( $status == 0 ) || ( $status == 503 ) ) {
			return $status;
		}
		curl_close( $curl );
		return $result;
	}
} else {

	/* Curl isn’t on the server. */
	function geturl( $url, $get = array(), $header = array() ) {
		return 0;
	}
}
