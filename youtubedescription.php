<?php
header('Content-Type: text/html; charset=UTF-8');
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

$filename = $path . "/s" . $season . "e" . $episode . "/episode.txt";
if ($season !== "00" && $episode !== "00" && file_exists($filename))
{
	$shownotes		= file($filename);

	$head = array_slice( $shownotes, 0, 14 );
	$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
						'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
				'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL', 'YOUTUBE' ), '');
	foreach ( $head as $entry ) {
		list($k, $v) = explode( ':', $entry, 2 );
		$meta[$k] = trim($v);
	}
	$epi = 's' . slenc($meta['SEASON']) . 'e' . slenc($meta['EPISODE']);

	$meta['RECORDED']  = ( $meta['RECORDED'] === '' ? "N/A" : $meta['RECORDED'] );

	echo "{$meta['DESCRIPTION']}<br>\n<br>\n";
	echo "Shownotes featuring full descriptions and links can be found at https://steamlug.org/cast/{$epi}<br>\n";
	echo "This cast was recorded on {$meta['RECORDED']}<br>\n<br>\n";

	foreach ( array_slice( $shownotes, 15 ) as $note)
	{
		preg_replace_callback(
			'/(\d+:\d+:\d+)\s+\*(.*)\*/',
			function($matches) { print slenc($matches[1]) . " " . slenc($matches[2]) . "<br>"; },
			$note );
	}
	echo "<br>\nSteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.<br>\n";
	echo "Visit our site http://steamlug.org/ and the cast homepage http://steamlug.org/cast<br>\n";
	echo "Email us feedback, questions, tips and suggestions to cast@steamlug.org<br>\n";
	echo "We can be followed on Twitter http://twitter.com/steamlug\n";
}
