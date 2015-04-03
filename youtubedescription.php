<?php
header('Content-Type: text/html; charset=UTF-8');

include_once('includes/functions_cast.php');

function slenc($u)
{
	return htmlentities($u,ENT_QUOTES, "UTF-8");
}

$filename = $notesPath . "/s" . $season . "e" . $episode . "/episode.txt";
if ($season !== "00" && $episode !== "00" && file_exists($filename))
{
	$shownotes			= file( $filename );
	$meta				= castHeader( array_slice( $shownotes, 0, 14 ) );

	$meta['RECORDED']	= ( $meta['RECORDED'] === '' ? "N/A" : $meta['RECORDED'] );

	echo "{$meta['DESCRIPTION']}<br>\n<br>\n";
	echo "Shownotes featuring full descriptions and links can be found at https://steamlug.org/cast/{$meta['SLUG']}<br>\n";
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
