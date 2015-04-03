<?php

if ( extension_loaded('curl') ) {

	function geturl( $url, $get = array(), $header = array() ) {

		$curl = curl_init( );
		curl_setopt_array( $curl, array(
				CURLOPT_URL => $url . '?' . http_build_query( $get ),
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 1,
				CURLOPT_TIMEOUT => 1 )
				);
		$result = curl_exec( $curl );
		$status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		if ( ( $status == 404 ) || ( $status == 0 ) || ( $status == 503 ) ) {
			return curl_error( $curl ) . ", " .curl_errno( $curl );
		}
		curl_close( $curl );
		return $result;
	}
} else {
	
	// should this ever work if curl does not exist? Unsure. Security.
	function geturl( $url, $get = array(), $header = array() ) {
		return false;
	}
}
