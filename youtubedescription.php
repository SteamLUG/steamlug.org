<?php
header('Content-Type: text/html; charset=UTF-8');

include_once('includes/functions_cast.php');

function slenc($u)
{
	return htmlentities($u,ENT_QUOTES, "UTF-8");
}

/* User wanting to see a specific cast, and shownotes file exists */
if ( $season !== "00" && $episode !== "00" && ($meta = getCastHeader( $slug ) ) ) {

	$shownotes			= getCastBody( $slug );
	$meta['RECORDED']	= ( $meta['RECORDED'] === '' ? "N/A" : $meta['RECORDED'] );

	echo "{$meta['DESCRIPTION']}<br>\n<br>\n";
	echo "Shownotes featuring full descriptions and links can be found at https://steamlug.org/cast/{$meta['SLUG']}<br>\n";
	echo "This cast was recorded on {$meta['RECORDED']}<br>\n<br>\n";

	foreach ( $shownotes as $note ) {

		preg_replace_callback(
			'/(\d+:\d+:\d+)\s+\*(.*)\*/',
			function($matches) { print slenc($matches[1]) . " " . slenc($matches[2]) . "<br>"; },
			$note );
	}
	echo "<br>\nSteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.<br>\n";
	echo "Visit our site https://steamlug.org/ and the cast homepage https://steamlug.org/cast<br>\n";
	echo "Email us feedback, questions, tips and suggestions to cast@steamlug.org<br>\n";
	echo "We can be followed on Twitter https://twitter.com/steamlug\n";
}
