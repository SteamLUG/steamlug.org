<?php
/**
* Simple wrapper around CURL, to avoid all the cruft in other scripts
*/

/**
*/

if ( extension_loaded('curl') ) {


	/**
	* Our simple replacement for file_get_contents, with added bonus of param & header config
	* If it fails, it will return a number. Currently naïvely tests for only a few errors.
	* @param string $url the intended URL, avoid baking query params into this
	* @param array $get query parameters for the fetched resource
	* @param array $header Accept headers and the like, each entry should be a full Header: Line string
	* @return mixed|integer the contents of the requested resource, or the status code if it fails
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

	/**
	* @ignore this is defined above, in case libcurl is not available, no need for duplication
	*/
	function geturl( $url, $get = array(), $header = array() ) {
		return 0;
	}
}
