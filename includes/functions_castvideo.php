<?php
include_once('paths.php');

function _nameplate( $string, $offset = 0, $guest = 0 ) {

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

// TODO convert to php-ffmpeg? in the future

function generateImage( $season, $episode ) {

	global $avatarKeyPath; /* TODO find a better location to write to! */

	// capture our generated SVG
	ob_start( );

	$slug = 's' . $season . 'e' . $episode;
	$meta = getCastHeader( $slug );
	// TODO check that filename is set, audio file exists

	if ($meta) {
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
			$guestsIncludeString .= _nameplate( $Guest, $startIndex, 1 );
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

/* call a longist running avconv, returns tempfile name? */
function generateVideo( $season, $episode ) {

	global $filePath;
	global $avatarKeyPath; /* TODO find a better location to write to! */

	/* TODO find a reasonable max generation time */
	set_time_limit( 240 );

	/*
	XXX apologies that this doesn’t use tmpfiles. I started with that, and then
	realised through use how utterly terrible imagemagick is with them. Firstly, to
	set output format to PNG, you prepend the filename‽ with png: OK, fair enough.
	That stops 60MB SVG output… Then it wont read http:// paths, so we change them
	to absolute filesystem file:// paths, … it wont read those either, finally we
	try with damning the spec and putting naked file paths, and it works, but only
	relatively, absolute paths fail. I worked worked those fixes into this script,
	and it was still failing.
	Turns out, it wont find files over symlinks either. So we remove the
	symlinks, put the files into place, test on CLI it works! But it is only
	resolving some of the images. To make our scripts easier, and avoiding touching
	convert in the first place, we rename all uploaded files to .png; I have no
	love for file extensions, quite happy with basenames and mime types. And this
	was the issue, DerRidda.png was a JPEG, and that was causing it to fail to
	load. On what fucking planet does software use file extensions as the sole way
	to resolve file contents?
	We know that, put that understanding into this script and… it still fails.
	We cannot use tmp files, as it tries to resolve the relative files from that
	file path; and we cannot use absolute paths to avoid doing all that.
	*/

	$slug = 's' . $season . 'e' . $episode;
	$meta = getCastHeader( $slug );
	/* TODO check that filename is set, audio file exists */
	$audiofile = $filePath  . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'] . '.ogg';

	$svgcontents = generateImage( $season, $episode );
	$svgcontents = str_replace( '/avatars', './avatars', $svgcontents );
	$svgcontents = str_replace( '/images/', './images/', $svgcontents );
	$svgcontents = str_replace( '/fonts/', './fonts/', $svgcontents );

	/* TODO: reg match on http references, check local cache for file and either dl & use, or use */


	$svgfile = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.svg';
	$svgfileref = fopen( $svgfile, 'w' );
	fwrite( $svgfileref, $svgcontents );
	fclose( $svgfileref );
	// TODO test file?

	// convert SVG into PNG
	$pngfile = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.png';
	$commandthumbnail = "rsvg-convert {$svgfile} > {$pngfile}";
	print "Running: ". $commandthumbnail . "\n";
	echo shell_exec( $commandthumbnail . ' 2>&1' );
	// TODO test file got created, or bail

	// take audio, image and make video
	$tmpmp4file = $avatarKeyPath  . '/' . $meta['FILENAME'] . '-temp.mp4';
	$commandvideo = "avconv -y -loglevel warning -loop 1 -framerate 1 -i {$pngfile} -i {$audiofile} -c:v libx264 -tune stillimage -pix_fmt yuv420p -c:a aac -strict experimental -b:a 192k -shortest {$tmpmp4file}";
	print "Running: ". $commandvideo . "\n";
	echo shell_exec( $commandvideo . ' 2>&1' );
	// TODO test file got created, or bail

	// if possible, make it a QT faststart file, so processing is quicker
	$mp4file = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.mp4';
	$commandfaststart = "qt-faststart {$tmpmp4file} {$mp4file}";
	print "Running: ". $commandfaststart . "\n";
	echo shell_exec( $commandfaststart . ' 2>&1' );
	// TODO test file got created, otherwise rename $tmpmp4file $mp4file

	// TODO test file exists, is not empty, otherwise return false

	return $mp4file;
}

