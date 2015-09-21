<?php
include_once('paths.php');

/**
 * Private function, returns SVG-formatted person plate for our thumbnail
 * @param string $string of the form: ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ / ‘John Drinkwater {URL}’
 * @param integer $offset translate this nameplate by this value horizontally
 * @param boolean $guest translate this nameplate to the guest slot if true
 * @return string a rendered version of $string
 */
function _nameplate( $string, $offset = 0, $guest = false ) {

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

	$flip = ( $guest == true ? -23 : 107 );
	return <<<SVGPLATE
			<g transform="translate({$offset},0)">
				<use xlink:href="#person-holder" />
				<image xlink:href="{$avatar}" width="70" height="70" preserveAspectRatio="xMidYMid slice" clip-path="url(#avatar-clip)" />
				<text y="{$flip}" x="35">{$name}</text>
			</g>

SVGPLATE;
}

/**
 * Private function, returns SVG-formatted game plate for our thumbnail
 * @param string $string of the form: ‘000000’ (Steam appid) / ‘//example.com/image.png’ (URL)
 * @param integer $offset translate this nameplate by this value horizontally
 * @return string a rendered version of $string
 */
function _gameplate( $string, $offset ) {

	$url = "";
	foreach ( explode( " ", $string ) as $data ) {

		// appid numbers
		if ( preg_match( '/^([0-9]*)$/i', $data, $appidResult ) ) {
			$appid = $appidResult[1];

		} else {
			$url = $data;
		}
	}

	if ( isset($appid) ) {
		$images = getAppImages( $appid );
		$url = $images[ 'capsule_lg' ];

		/* TODO check this is all good, all the time */
		$localcopy = "/avatars/apps/{$appid}.capsule_184x69.jpg";
		if ( file_exists( '.' . $localcopy ) and !is_dir( '.' . $localcopy ) ) {
			$url = $localcopy;
		} else {
			if ( writeURLToLocation( 'http:' . $url, '.' . $localcopy ) ) {
				/* TODO verify we always get jpg files back */
				$url = $localcopy;
			}
		}
	}

	return <<<GAMEPLATE
				<g transform="translate({$offset},0)">
					<rect width="190" height="75" x="-3" y="-3" rx="6" ry="6" style="opacity:0.25;fill:#000000;filter:url(#blur)" />
					<image xlink:href="{$url}" width="184" height="69" preserveAspectRatio="xMidYMid meet" clip-path="url(#game-clip)" />
				</g>

GAMEPLATE;
}

/**
 * Generates an SVG image used to render the YouTube video
 * @param integer $season the season
 * @param integer $episode and episode for this specific cast episode
 * @return string an SVG rendered version of this episode
 */
function generateImage( $season, $episode ) {

	global $avatarKeyPath; /* TODO find a better location to write to! */

	$slug = 's' . $season . 'e' . $episode;
	$meta = getCastHeader( $slug );

	ob_start( );

	if ( $meta ) {
		$devGames = array_map('trim', explode(',', $meta['ADDITIONAL']));
		$listGames = [];

		$guestsBlockOffset = 0; $hostsBlockOffset = 0;
		$titleOffset = 360; // where to offset title with no guests
		$guestsIncludeString = ""; $hostsIncludeString = "";
		$alignment = array(0, 610, 520, 430, 340, 250, 160, 50);

		$hostsBlockOffset = $alignment[count($meta['HOSTS'])]; $startIndex = 0;
		foreach ($meta['HOSTS'] as $Host) {

			if ($Host == "") break;
			$hostsIncludeString .= _nameplate( $Host, $startIndex ) ;
			$startIndex += 180;
		}

		$guestsBlockOffset = $alignment[count($meta['GUESTS'])]; $startIndex = 0;
		foreach ($meta['GUESTS'] as $Guest) {

			if ($Guest == "") break;
			$guestsIncludeString .= _nameplate( $Guest, $startIndex, true );
			$startIndex += 180;
		}
		$gamesString = "";

		if ( strlen( $meta['ADDITIONAL'] ) > 0 ) {

			$startIndex = 0;
			foreach ($devGames as $Game) {
				array_push( $listGames, _gameplate( $Game, $startIndex ) );
				$startIndex += 200;
			}
			$games = count($listGames);
			$alignment = array(0, -91, -191, -291, -391, -491);
			$gamesBlockOffset = $alignment[$games];

			$plural = count($meta['GUESTS']) > 1 ? "s" : "";
			$gamesString = <<<GAMESINTRO
				<text id="game-name" style="font-size:23px">With Special Guest{$plural} and Developer{$plural} of</text>
				<g transform="translate({$gamesBlockOffset},26)">

GAMESINTRO;

			$gamesString .= join("", $listGames) . "\t\t\t</g>";
			// TODO this needs to test if any games are being discussed
			$titleOffset = 250;
		}

		$castEntry = <<<THUMB
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="1280" height="720">
	<defs>
	<linearGradient x1="640" y1="0" x2="640" y2="127" id="shade-top" gradientUnits="userSpaceOnUse" >
		<stop style="stop-opacity:1" offset="0" />
		<stop style="stop-opacity:0" offset="1" />
	</linearGradient>
	<linearGradient x1="640" y1="720" x2="640" y2="592" id="shade-bot" gradientUnits="userSpaceOnUse" >
		<stop style="stop-opacity:1" offset="0" />
		<stop style="stop-opacity:0" offset="1" />
	</linearGradient>
	<style type="text/css"> @font-face { font-family: 'Orbitron'; src: local('Orbitron'), url('/fonts/orbitron-medium-webfont.woff') format('woff'); font-weight: normal; font-style: normal; } </style>
	<filter x="-0.06" y="-0.06" width="1.12" height="1.12" color-interpolation-filters="sRGB" id="blur">
		<feGaussianBlur id="feGaussianBlur15022-7" stdDeviation="1.6111817" />
	</filter>
	<clipPath clipPathUnits="userSpaceOnUse" id="game-clip">
		<rect width="184" height="69" rx="8" ry="8" />
	</clipPath>
	<clipPath id="avatar-clip">
		<rect width="66" height="66" rx="3" ry="3" x="2" y="2" style="color:#000000;fill:none" />
	</clipPath>
	<linearGradient x1="1940" y1="-262" x2="1940" y2="-76" id="border-stroke" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.32851643,0,0,0.32851646,-413.47663,427.76239)">
		<stop style="stop-color:#8ecafc;stop-opacity:1" offset="0" />
		<stop style="stop-color:#73a0c7;stop-opacity:1" offset="1" />
	</linearGradient>
	<g id="person-holder">
		<rect width="78" height="78" rx="5.5" ry="5.5" x="-4" y="-4" id="bg" style="opacity:0.3;fill:#000000;filter:url(#blur)" />
		<rect width="70" height="70" rx="5.5" ry="5.5" id="image-bg" fill="#000" />
		<rect width="70" height="70" rx="5.5" ry="5.5" id="border" fill="none" style="stroke:url(#border-stroke);stroke-width:4" />
	</g>
	</defs>
	<g font-family="Orbitron" font-size="20px" fill="#8dc9fa">
	<g id="background">
		<image xlink:href="/images/youtubebg.png" width="1280" height="720" />
	</g>
	<g id="gutters">
		<rect width="1280" height="130" fill="url(#shade-top)" />
		<rect width="1280" height="90"	fill="#323232" />
		<rect width="1280" height="130" y="590" fill="url(#shade-bot)" />
		<rect width="1280" height="90"	y="630" fill="#323232" />
	</g>

	<g id="episode" style="font-size:50px;line-height:125%;text-anchor:middle">
		<g transform="translate(640,{$titleOffset})" >
			<text y="-30" id="title">SteamLUG Cast s{$meta['SEASON']} e{$meta['EPISODE']}</text>
			<text y="30" id="subtitle" style="font-size:36px"><tspan>‘ </tspan>{$meta['TITLE']}<tspan> ’</tspan></text>
		</g>
		<g transform="translate(640,460)">
{$gamesString}
		</g>
	</g>

	<g id="peeps" style="color:#000000;font-weight:400;line-height:125%;text-anchor:middle">
		<g id="hosts" transform="translate({$hostsBlockOffset},10)">
{$hostsIncludeString}		</g>
		<g id="guests" transform="translate({$guestsBlockOffset},640)">
{$guestsIncludeString}		</g>
	</g>
	</g>
</svg>
THUMB;

	echo $castEntry;

	} else {

	echo <<<FAILURE
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="1280" height="720">
	<defs>
		<style type="text/css"> @font-face { font-family: 'Orbitron'; src: local('Orbitron'), url('/fonts/orbitron-medium-webfont.woff') format('woff'); font-weight: normal; font-style: normal; } </style>
	</defs>
	<g font-family="Orbitron" font-size="50px" fill="#8dc9fa">
	<g id="background">
		<image xlink:href="/images/youtubebg.png" width="1280" height="720" />
	</g>
	<g id="episode" style="line-height:125%;text-anchor:middle">
		<g transform="translate(640,360)" style="fill:red" >
			<text y="-30" id="title">SteamLUG Cast s{$season} e{$episode}</text>
			<text y="30" id="subtitle" style="font-size:36px"><tspan>‘ </tspan>Unmade or Unloved<tspan> ’</tspan></text>
		</g>
	</g>
	</g>
</svg>
FAILURE;
	}

	$svgcontents = ob_get_clean( );
	return $svgcontents;
}

/**
 * Generates a video that we can upload to YouTube. This calls generateImage( ) directly.
 * Note this is long-running, and as such needs to call set_time_limit( )
 * @param integer $season the season
 * @param integer $episode and episode for this specific cast episode
 * @return string location of the rendered file on the server
 */
function generateVideo( $season, $episode ) {

	global $filePath;
	global $avatarKeyPath; /* TODO find a better location to write to! */

	/* TODO find a reasonable max generation time */
	set_time_limit( 240 );

	/* TODO use tmp files */

	$slug = 's' . $season . 'e' . $episode;
	$meta = getCastHeader( $slug );
	/* TODO check that filename is set, audio file exists */
	$audiofile = $filePath  . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'] . '.ogg';

	$svgcontents = generateImage( $season, $episode );
	$svgcontents = str_replace( '/avatars', './avatars', $svgcontents );
	$svgcontents = str_replace( '/images/', './images/', $svgcontents );
	$svgcontents = str_replace( '/fonts/', './fonts/', $svgcontents );

	/* TODO: reg match on http references, check local cache for file and either dl & use, or use */
	$svgfile	= $avatarKeyPath  . '/' . $meta['FILENAME'] . '.svg';
	$pngfile	= $avatarKeyPath  . '/' . $meta['FILENAME'] . '.png';
	$mp4filetmp	= $avatarKeyPath  . '/' . $meta['FILENAME'] . '-temp.mp4';
	$mp4file	= $avatarKeyPath  . '/' . $meta['FILENAME'] . '.mp4';

	$svgfileref = fopen( $svgfile, 'w' );
	fwrite( $svgfileref, $svgcontents );
	fclose( $svgfileref );
	/* TODO test file? */

	$commandthumbnail = "rsvg-convert {$svgfile} > {$pngfile}";
	print "Running: ". $commandthumbnail . "\n";
	echo shell_exec( $commandthumbnail . ' 2>&1' );
	/* TODO test file got created, or bail */

	$commandvideo = "ffmpeg -y -loglevel warning -loop 1 -framerate 1 -i {$pngfile} -i {$audiofile} -c:v libx264 -tune stillimage -pix_fmt yuv420p -c:a aac -strict experimental -b:a 192k -shortest {$mp4filetmp}";
	print "Running: ". $commandvideo . "\n";
	echo shell_exec( $commandvideo . ' 2>&1' );
	/* TODO test file got created, or bail */

	$commandfaststart = "qt-faststart {$mp4filetmp} {$mp4file}";
	print "Running: ". $commandfaststart . "\n";
	echo shell_exec( $commandfaststart . ' 2>&1' );
	/* TODO test file got created, otherwise rename $mp4filetmp $mp4file */

	/* TODO test file exists, is not empty, otherwise return false */
	return $mp4file;
}

