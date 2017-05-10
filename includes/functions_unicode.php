<?php
/**
* Function collection for … no good reason, just wanted a linkable function reference for:
*/

/**
 * Converts a two letter ISO country code (from geoip_country_code_by_name) and emits Unicode regional indicators.
 * @param string $country ISO 3166-1 alpha-2 country code, two characters long. If false, returns ''
 * @return string Some lovely Unicode, that may be rendered as a flag, depending on platform support
*/
function country_code_to_unicode( $country ) {

	if ( ( $country === false ) or ( strlen( $country ) != 2 ) )
		return '🌍';

	$country = strtoupper( $country );

	$char1 = ord( $country[0] ) - 65;
	$char2 = ord( $country[1] ) - 65;

	if (( $char1 > 25 ) or ( $char2 > 25 ) or
		( $char1 <  0 ) or ( $char2 <  0 ) )
		return '🌍';

	$start = 0x1F1E6;
	$char1 += $start; $char2 += $start;
	return mb_convert_encoding('&#' . $char1 . ';&#' . $char2 . ';', 'UTF-8', 'HTML-ENTITIES');
}
