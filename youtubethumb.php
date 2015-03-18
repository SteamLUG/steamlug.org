<?php
header('Content-Encoding: UTF-8');
header('Content-Type: image/svg+xml');

include_once('includes/functions_avatars.php');
include_once('includes/functions_cast.php');

function nameplate( $string, $offset = 0, $guest = 0 ) {

	$person = parsePersonString( $string );
	$name = $person['name'];
	if ( strlen( $name ) == 0 && strlen( $person['nickname'] ) > 0 ) {
		$name = $person['nickname'];
	}
	if ( strlen( $name ) == 0 && strlen( $person['twitter'] ) > 0 ) {
		$name = $person['twitter'];
	}
	if ( strlen( $name ) == 0 )
		return;

	$avatar = $person['avatar'];
	if ( strlen( $avatar ) == 0 )
		$avatar = "unknown-host-" . md5( $string );

	$flip = ( $guest == 1 ? -23 : 107 );
	return <<<SVGPLATE
			<g transform="translate({$offset},0)">
				<use xlink:href="#person-holder" />
				<image xlink:href="{$avatar}" width="70" height="70" preserveAspectRatio="xMidYMid slice" clip-path="url(#avatar-clip)" />
				<text y="{$flip}" x="35">{$name}</text>
			</g>

SVGPLATE;
}

/* we take a ‘######’ / //example.com/imageofgame.png and split out SVG */
function gameplate( $string, $offset ) {

	$url = "";
	foreach ( explode( " ", $string ) as $data ) {

		// appid numbers
		if ( preg_match( '/^([0-9]*)$/i', $data, $appidResult ) ) {
			$appid = $appidResult[1];

		} else {
			$url = $data;
		}
	}

	if ( isset($appid) )
		$url = "//steamcdn-a.akamaihd.net/steam/apps/{$appid}/capsule_184x69.jpg";

	return <<<GAMEPLATE
				<g transform="translate({$offset},0)">
					<rect width="190" height="75" x="-3" y="-3" rx="6" ry="6" style="opacity:0.25;fill:#000000;filter:url(#blur)" />
					<image xlink:href="{$url}" width="184" height="69" preserveAspectRatio="xMidYMid meet" clip-path="url(#game-clip)" />
				</g>

GAMEPLATE;
}

$filename = $notesPath . "/s" . $season . "e" . $episode . "/episode.txt";
/* User wanting to see a specific cast, and shownotes file exists */
if ($season !== "00" && $episode !== "00" && file_exists($filename))
{
	$shownotes			= file( $filename );
	$meta				= castHeader( array_slice( $shownotes, 0, 14 ) );

	$devGames			= array_map('trim', explode(',', $meta['ADDITIONAL']));
	$listGames = [];

	$guestsBlockOffset = 0; $hostsBlockOffset = 0;
	$titleOffset = 360; // where to offset title with no guests
	$guestsIncludeString = ""; $hostsIncludeString = "";
	$alignment = array(0, 610, 520, 430, 340, 250, 160, 50);

	$hostsBlockOffset = $alignment[count($meta['HOSTS'])]; $startIndex = 0;
	foreach ($meta['HOSTS'] as $Host) {

		if ($Host == "") break;
		$hostsIncludeString .= nameplate( $Host, $startIndex ) ;
		$startIndex += 180;
	}

	$guestsBlockOffset = $alignment[count($meta['GUESTS'])]; $startIndex = 0;
	foreach ($meta['GUESTS'] as $Guest) {

		if ($Guest == "") break;
		$guestsIncludeString .= nameplate( $Guest, $startIndex, 1 );
		$startIndex += 180;
	}
	$gamesString = "";

	if ( strlen( $meta['ADDITIONAL'] ) > 0 ) {

		$startIndex = 0;
		foreach ($devGames as $Game) {
			array_push( $listGames, gameplate( $Game, $startIndex ) );
			$startIndex += 200;
		}
		$games = count($listGames);
		$alignment = array(0, -91, -191, -291, -391, -491);
		$gamesBlockOffset = $alignment[$games];

		$plural = count($castGuests) > 1 ? "s" : "";
		$gamesString = <<<GAMESINTRO
			<text id="game-name" style="font-size:23px;">With Special Guest{$plural} and Developer{$plural} of</text>
			<g transform="translate({$gamesBlockOffset},26)">

GAMESINTRO;

		$gamesString .= join("", $listGames) . "\t\t\t</g>";
		// TODO this needs to test if any games are being discussed
		$titleOffset = 250;
	}

	$castEntry = <<<THUMB
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="1280" height="720">
	<defs>
	<!-- Cheese’s gutters, minimised like fook -->
	<linearGradient x1="640" y1="0" x2="640" y2="127" id="shade-top" gradientUnits="userSpaceOnUse" >
		<stop style="stop-opacity:1" offset="0" />
		<stop style="stop-opacity:0" offset="1" />
	</linearGradient>
	<linearGradient x1="640" y1="720" x2="640" y2="592" id="shade-bot" gradientUnits="userSpaceOnUse" >
		<stop style="stop-opacity:1" offset="0" />
		<stop style="stop-opacity:0" offset="1" />
	</linearGradient>

	<!-- A /very/ minimised version of Cheese’s avatar border clips and strokes -->
	<filter x="-0.06" y="-0.06" width="1.12" height="1.12" color-interpolation-filters="sRGB" id="blur">
		<feGaussianBlur id="feGaussianBlur15022-7" stdDeviation="1.6111817" />
	</filter>
	<clipPath clipPathUnits="userSpaceOnUse" id="game-clip">
		<rect width="184" height="69" rx="8" ry="8" />
	</clipPath>
	<clipPath id="avatar-clip">
		<rect width="66" height="66" rx="3" ry="3" x="2" y="2" style="color:#000000;fill:none;stroke:none;visibility:visible;display:inline;overflow:visible" />
	</clipPath>
	<linearGradient x1="1940" y1="-262" x2="1940" y2="-76" id="border-stroke" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.32851643,0,0,0.32851646,-413.47663,427.76239)">
		<stop style="stop-color:#8ecafc;stop-opacity:1" offset="0" />
		<stop style="stop-color:#73a0c7;stop-opacity:1" offset="1" />
	</linearGradient>

	<!-- Cheese’s Steam emulation avatar border -->
	<g id="person-holder">
		<rect width="78" height="78" rx="5.5" ry="5.5" x="-4" y="-4" id="bg" style="opacity:0.3;fill:#000000;filter:url(#blur)" />
		<rect width="70" height="70" rx="5.5" ry="5.5" id="image-bg" style="fill:#000000;" />
		<rect width="70" height="70" rx="5.5" ry="5.5" id="border" style="fill:none;stroke:url(#border-stroke);stroke-width:4;" />
	</g>
	</defs>
	<g id="background">
		<image xlink:href="/images/youtubebg.png" width="1280" height="720" />
	</g>
	<g id="gutters">
		<rect width="1280" height="130" style="fill:url(#shade-top);overflow:visible;" />
		<rect width="1280" height="90"	style="fill:#323232;overflow:visible;" />
		<rect width="1280" height="130" y="590" style="fill:url(#shade-bot);overflow:visible;" />
		<rect width="1280" height="90"	y="630" style="fill:#323232;overflow:visible;" />
	</g>

	<g id="episode" style="font-style:normal;font-size: 50px; line-height:125%;text-anchor:middle;fill:#8dc9fa;stroke:none;font-family:Orbitron">
		<g transform="translate(640,{$titleOffset})" >
			<text y="-30" id="title">SteamLUG Cast s{$meta['SEASON']} e{$meta['EPISODE']}</text>
			<text y="30" id="subtitle" style="font-size:36px;"><tspan>‘ </tspan>{$meta['TITLE']}<tspan> ’</tspan></text>
		</g>
		<g transform="translate(640,460)">
 {$gamesString}
		</g>
	</g>

	<g id="peeps" style="color:#000000;fill:#8dc9fa;stroke:none;font-family:Orbitron;font-size:20px;font-weight:400;overflow:visible;line-height:125%;text-anchor:middle;">
		<g id="hosts" transform="translate({$hostsBlockOffset},10)">
{$hostsIncludeString}		</g>
		<g id="guests" transform="translate({$guestsBlockOffset},640)">
{$guestsIncludeString}		</g>
	</g>
</svg>
THUMB;

	echo $castEntry;

} else {

	echo <<<FAILURE
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="1280" height="720">
	<g id="background">
		<image xlink:href="/images/youtubebg.png" width="1280" height="720" />
	</g>
	<g id="episode" style="font-style:normal;font-size: 50px; line-height:125%;text-anchor:middle;fill:#8dc9fa;stroke:none;font-family:Orbitron">
		<g transform="translate(640,360)" style="fill:red" >
			<text y="-30" id="title">SteamLUG Cast s{$season} e{$episode}</text>
			<text y="30" id="subtitle" style="font-size:36px;"><tspan>‘ </tspan>Unmade or Unloved<tspan> ’</tspan></text>
		</g>
	</g>
</svg>
FAILURE;

}
