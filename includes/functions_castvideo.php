<?php
include_once('paths.php');

// TODO: what other functions do we want in here?
// TODO convert to php-ffmpeg? in the future

function generateImage( $season, $episode ) {

	global $notesPath;
	global $filePath;
	global $avatarKeyPath; /* TODO find a better location to write to! */

	/* TODO: refactor youtubethumb into here
	*/
}

/* call a longist running avconv, returns tempfile name? */
function generateVideo( $season, $episode ) {

	global $notesPath;
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

	$filename = $notesPath . "/s" . $season . "e" . $episode . "/episode.txt";
	$slug = 's' . $season . 'e' . $episode;
	$shownotes = file( $filename );
	$meta = castHeader( array_slice( $shownotes, 0, 14 ) );
	// TODO check that filename is set, audio file exists

	$audiofile = $filePath  . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'] . '.ogg';

	// capture our generated SVG
	ob_start( );
	/**
	* @ignore Privately used to prevent another script sending header info
	* TODO move the functions from youtubethumb into functions
	*/
	define( 'INTERNAL_USE_ONLY', 1701 );
	include 'youtubethumb.php';
	$svgcontents = ob_get_clean( );
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

