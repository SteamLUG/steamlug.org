<?php
include_once('paths.php');

// TODO: what other functions do we want in here?
// TODO YouTube video stats?
// TODO convert to php-ffmpeg? in the future
// TODO overwrite support!

/* TODO probably move this to function_cast/castvideo as it is oddly cast specific */
/* call a longist running avconv, returns tempfile name? */
/* this maybe ought to be in functions_cast as it is cast-specific rather than youtube */
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
	$shownotes = file( $filename );
	$meta = castHeader( array_slice( $shownotes, 0, 14 ) );

	// capture our generated SVG
	ob_start();
	include 'youtubethumb.php';
	$svgcontents = ob_get_clean();
	$svgcontents = str_replace( '/avatars', './avatars', $svgcontents );
	$svgcontents = str_replace( '/images/', './images/', $svgcontents );

	$svgfile = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.svg';
	$svgfileref = fopen( $svgfile, 'w' );
	fwrite( $svgfileref, $svgcontents );
	fclose( $svgfileref );

	// convert SVG into PNG
	$pngfile = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.png';

	$commandthumbnail = "convert -size 1280x720 -type optimize -strip svg:{$svgfile} png:{$pngfile}";
	print $commandthumbnail . "\n";
	echo exec( $commandthumbnail );

	// take audio, image and make video
	$mp4file = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.mp4';
	$audiofile = $filePath  . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'] . '.ogg';
	$commandvideo = "avconv -loop 1 -framerate 1 -i {$pngfile} -i {$audiofile} -c:v libx264 -tune stillimage -pix_fmt yuv420p -c:a aac -strict experimental -b:a 192k -shortest {$mp4file}";
    print $commandvideo . "\n";
	echo shell_exec( $commandvideo . ' 2>&1' );

	// if possible, make it a QT faststart file, so processing is quicker
	/*
	$outputfileref = tmpfile();
	$sighPHPpointlessness = stream_get_meta_data( $outputfileref );
	$outputfile =  $sighPHPpointlessness['uri'];
	$commandfaststart = "qt-faststart {$mp4file} {$outputfile}";
    print $commandfaststart . "\n";
	*/
	// echo exec( $commandfaststart );

	// put our final temp file somewhere we can reference, and return that filename
	/* removed as we no longer use tmp files :'( */

	// test file is good, otherwise return false

	return $mp4file;
}

/* submits file to youtube, returns youtube slug */
function uploadVideo( $season, $episode, $videofile ) {
}

/* adds video to playlist, and changes visibility from unlisted to public */
function publishVideo( $season, $episode ) {
}
