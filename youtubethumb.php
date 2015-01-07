<?php
header('Content-Encoding: UTF-8');
header('Content-Type: image/svg+xml');
$season  = isset($_GET["s"]) ? intval($_GET["s"]) : "0";
$season  = str_pad($season, 2, '0', STR_PAD_LEFT);
$episode = isset($_GET["e"]) ? intval($_GET["e"]) : "0";
$episode = str_pad($episode, 2, '0', STR_PAD_LEFT);

$path = "/var/www/archive.steamlug.org/steamlugcast";
$url  = "//archive.steamlug.org/steamlugcast";

function slenc($u)
{
	return htmlentities($u,ENT_QUOTES, "UTF-8");
}

/* TODO: join this to our steamlug user system; TODO: make steamlug user system */
$hostAvatars = array(
	"swordfischer" =>	"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/87/87542ec881993993fe2c5268224689538e264fac_full.jpg",
	"ValiantCheese" =>	"//gravatar.com/avatar/916ffbb1cd00d10f5de27ef4f9846390",
	"johndrinkwater" =>	"//gravatar.com/avatar/751a360841982f0d0418d6d81b4beb6d",
	"MimLofBees" =>		"//pbs.twimg.com/profile_images/2458841225/cnm856lvnaz4hhkgz6yg.jpeg",
	"DerRidda" =>		"//pbs.twimg.com/profile_images/2150739768/pigava.jpeg",
	"mnarikka" =>		"//pbs.twimg.com/profile_images/523529572243869696/lb04rKRq.png",
	"Nemoder" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/0d/0d4a058f786ea71153f85262c65bb94490205b59_full.jpg",
	"beansmyname" =>	"//pbs.twimg.com/profile_images/2821579010/3f591e15adcbd026095f85b88ac8a541.png",
	"Corben78" =>		"//pbs.twimg.com/profile_images/313122973/Avatar.jpg",
	"Buckwangs" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
	"Cockfight" =>		"//steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/bb/bb21fbb52d66cd32526b27b51418e5aa0ca97a9f_full.jpg",
);

/* we take a ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ and spit out SVG */
function nameplate( $string ) {

	global $hostAvatars;
	/* first case, johndrinkwater */
	if ( array_key_exists( $string, $hostAvatars ) ) {
		$avatar = $hostAvatars["$string"];
		return <<<SVGPLATE
	<g id="{$string}">
		<image xlink:href="{$avatar}" width="70" height="70" preserveAspectRatio="xMidYMid slice" clip-path="url(#avatar-clip)" />
		<text y="107" x="35">{$string}</text>
	</g>

SVGPLATE;
	}

	/* third case, John Drinkwater (@twitter) */
	if ( preg_match( '/([[:alnum:] ]+)\s+\(@([a-z0-9_]+)\)/i', $string, $matches) ) {
		$host = $matches[2];
		if ( array_key_exists( $host, $hostAvatars ) )
			$avatar = $hostAvatars["$host"];
		return <<<SVGPLATE
	<g id="{$host}">
		<image xlink:href="{$avatar}" width="70" height="70" preserveAspectRatio="xMidYMid slice" clip-path="url(#avatar-clip)" />
		<text y="107" x="35">{$host}</text>
	</g>

SVGPLATE;
	}

	/* second case, @johndrinkwater */
	if (preg_match( '/@([a-z0-9_]+)/i', $string, $matches)) {
		$host = $matches[1];
		if ( array_key_exists( $host, $hostAvatars ) )
			$avatar = $hostAvatars["$host"];
		return <<<SVGPLATE
	<g id="{$host}">
		<image xlink:href="{$avatar}" width="70" height="70" preserveAspectRatio="xMidYMid slice" clip-path="url(#avatar-clip)" />
		<text y="107" x="35">{$host}</text>
	</g>

SVGPLATE;
	}
	/* unmatched, why? blank or Nemoder :^) */
	return "";
}


/* we take a ‘johndrinkwater’ / ‘@johndrinkwater’ / ‘John Drinkwater (@twitter)’ and spit out text */
function name( $string ) {

	global $hostAvatars;
	/* first case, johndrinkwater */
	if ( array_key_exists( $string, $hostAvatars ) ) {
		return $string;
	}

	/* third case, John Drinkwater (@twitter) */
	if ( preg_match( '/([[:alnum:] ]+)\s+\(@([a-z0-9_]+)\)/i', $string, $matches) ) {
		$host = $matches[2];
		if ( array_key_exists( $host, $hostAvatars ) )
			return $host;
		else
			return "broken-host";
	}

	/* second case, @johndrinkwater */
	if (preg_match( '/@([a-z0-9_]+)/i', $string, $matches)) {
		$host = $matches[1];
		if ( array_key_exists( $host, $hostAvatars ) )
			return $host;
		else
			return "broken-host";
	}
	/* unmatched, why? blank or Nemoder :^) */
	return "";
}

$filename = $path . "/s" . $season . "e" . $episode . "/episode.txt";
/* User wanting to see a specific cast, and shownotes file exists */
if ($season !== "00" && $episode !== "00" && file_exists($filename))
{
	$shownotes		= file($filename);

	$head = array_slice( $shownotes, 0, 10 );
	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
						'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL' ), '');
	foreach ( $head as $entry ) {
		list($k, $v) = explode( ':', $entry, 2 );
		$meta[$k] = trim($v); /* TODO remember to slenc() stuff! */
	}

	$epi = 's' . slenc($meta['SEASON']) . 'e' . slenc($meta['EPISODE']);
	$archiveBase = $url . '/' . $epi . '/' . $meta['FILENAME'];
	$episodeBase = $path .'/' . $epi . '/' . $meta['FILENAME'];

	$meta['PUBLIC'] = $meta['PUBLISHED'];
	$meta['TITLE'] = slenc($meta['TITLE']);

	$castHosts			= array_map('trim', explode(',', $meta['HOSTS']));
	$castGuests			= array_map('trim', explode(',', $meta['GUESTS']));
	$listHosts = []; $listGuests = [];
	foreach ($castHosts as $Host) {
		array_push( $listHosts, nameplate( $Host ) );
	}
	foreach ($castGuests as $Guest) {
		array_push( $listGuests, nameplate( $Guest ) );
	}
	$hostsString = join("", $listHosts);
	$guestsString = join("", $listGuests);

	$guestsBlockOffset = 0; $hostsBlockOffset = 0;
	$guestsIncludeString = "";$hostsIncludeString = "";

	if (!empty($listHosts)) {
		$hosts = count($listHosts);
		$startIndex = 0;
		foreach ($castHosts as $Host) {
			$you = name($Host);
			$hostsIncludeString .= <<<HOSTINCLUDE
			<g transform="translate({$startIndex},0)"><use xlink:href="#person-holder" /><use xlink:href="#{$you}" /></g>

HOSTINCLUDE;
			$startIndex += 180;
		}
		$hostsBlockOffset = [0, 610, 520, 430, 340, 250][$hosts];
	}

	if (!empty($listGuest)) {
		$guest = count($listGuests);
		$startIndex = 0;
		foreach ($castGuests as $Guest) {
			$you = name($Guest);
			$guestsIncludeString .= <<<HOSTINCLUDE
			<g transform="translate({$startIndex},0)"><use xlink:href="#person-holder" /><use xlink:href="#{$you}" /></g>

HOSTINCLUDE;
			$startIndex += 180;
		}
		$guestsBlockOffset = [0, 610, 520, 430, 340, 250][$guests];
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
	<linearGradient x1="1940" y1="-262" x2="1940" y2="-76" id="border-stroke-online" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.32851643,0,0,0.32851646,-413.47663,427.76239)">
		<stop style="stop-color:#b2fa4f;stop-opacity:1" offset="0" />
		<stop style="stop-color:#89c73e;stop-opacity:1" offset="1" />
	</linearGradient>

	<!-- our background noise -->
	<filter x="0" y="0" width="1" height="1" color-interpolation-filters="sRGB" id="gravel">
		<feTurbulence type="fractalNoise" baseFrequency=".7" />
		<feComponentTransfer>
			<feFuncR type="linear" slope="2" intercept="-.8"/>
			<feFuncG type="linear" slope="2" intercept="-.8"/>
			<feFuncB type="linear" slope="2" intercept="-.8"/>
		</feComponentTransfer>
		<feColorMatrix type="saturate" values="0"/>
		<feComponentTransfer>
			<feFuncA type="table" tableValues="0 .2"/>
		</feComponentTransfer>
	</filter>

	<!-- Cheese’s Steam emulation avatar border -->
	<g id="person-holder">
		<rect width="78" height="78" rx="5.5" ry="5.5" x="-4" y="-4" id="bg" style="opacity:0.3;fill:#000000;filter:url(#blur)" />
		<rect width="70" height="70" rx="5.5" ry="5.5" id="image-bg" style="fill:#000000;" />
		<rect width="70" height="70" rx="5.5" ry="5.5" id="border" style="fill:none;stroke:url(#border-stroke);stroke-width:4;" />
	</g>

	<!-- HOSTS -->
{$hostsString}

	<!-- GUESTS -->
{$guestsString}

	</defs>
	<g id="background">
		<!-- <rect width="1280" height="720" id="b" style="fill:#1a1a1a;stroke:none" />
		<rect width="1280" height="720" id="bg" style="filter:url(#gravel)" /> -->
		<image xlink:href="https://archive.steamlug.org/1280x720_bg.png" width="1280" height="720" />
	</g>
	<g id="gutters">
		<rect width="1280" height="130" style="fill:url(#shade-top);overflow:visible;" />
		<rect width="1280" height="90"	style="fill:#323232;overflow:visible;" />
		<rect width="1280" height="130" y="590" style="fill:url(#shade-bot);overflow:visible;" />
		<rect width="1280" height="90"	y="630" style="fill:#323232;overflow:visible;" />
	</g>

	<g id="episode" style="font-style:normal;font-size: 50px; line-height:125%;text-anchor:middle;fill:#8dc9fa;stroke:none;font-family:Orbitron">
		<!-- With no guests, this should move back to centre: 640, 360 -->
		<!-- with guests: 640, 250 -->
		<g transform="translate(640,360)" >
			<text y="-30" id="title">SteamLUG Cast s{$meta['SEASON']} e{$meta['EPISODE']}</text>
			<text y="30" id="subtitle" style="font-size:36px;"><tspan>‘ </tspan>{$meta['TITLE']}<tspan> ’</tspan></text>
		</g>
<!--
		<g transform="translate(640,460)">
			<text id="game-name" style="font-size:23px;">With Special Guest and Developer of</text>
			<g transform="translate(-92,26)">
				<rect width="190" height="75" x="-3" y="-3" rx="6" ry="6" style="opacity:0.25;fill:#000000;filter:url(#blur)" />
				<image xlink:href="https://steamcdn-a.akamaihd.net/steamcommunity/public/images/apps/250110/fb87c80ce91f88fb6bea88abff26b4c4ec97e512.jpg" width="184" height="69" preserveAspectRatio="xMidYMid meet" clip-path="url(#game-clip)" />
			</g>
			<g transform="translate(18,26)">
				<rect width="190" height="75" x="-3" y="-3" rx="6" ry="6" style="opacity:0.25;fill:#000000;filter:url(#blur)" />
				<image xlink:href="https://steamcdn-a.akamaihd.net/steamcommunity/public/images/apps/35720/7d7c3b93bd85ad1db2a07f6cca01a767069c6407.jpg" width="184" height="69" preserveAspectRatio="xMidYMid meet" clip-path="url(#game-clip)" />
			</g>
		</g>
-->
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
		<image xlink:href="https://archive.steamlug.org/1280x720_bg.png" width="1280" height="720" />
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
