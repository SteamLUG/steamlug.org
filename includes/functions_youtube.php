<?php
include_once('paths.php');
require_once( 'Google/autoload.php' );
require_once( 'Google/Client.php' );
require_once( 'Google/Service/YouTube.php' );

$googleKeys = getGoogleKeys( );

$client = new Google_Client();
$client->setApplicationName( 'SteamLUG.org Website' );
$client->setDeveloperKey( $googleKeys[ 'api_key' ] );
$client->setClientId( $googleKeys[ 'client_id' ] );
$client->setClientSecret( $googleKeys[ 'client_secret' ] );
$client->setScopes( 'https://www.googleapis.com/auth/youtube' );
// XXX ?
//$redirect = filter_var( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL );
//$client->setRedirectUri( $redirect );

$youtube = new Google_Service_YouTube( $client );

// TODO: what other functions do we want in here?
// TODO YouTube video stats?
// TODO convert to php-ffmpeg? in the future

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
	// TODO check that filename is set, audio file exists

	$audiofile = $filePath  . '/' . $meta['SLUG'] . '/' . $meta['FILENAME'] . '.ogg';

	// capture our generated SVG
	ob_start( );
	define( 'INTERNAL_USE_ONLY', 1701 );
	include 'youtubethumb.php';
	$svgcontents = ob_get_clean( );
	$svgcontents = str_replace( '/avatars', './avatars', $svgcontents );
	$svgcontents = str_replace( '/images/', './images/', $svgcontents );

	/* TODO: reg match on http references, check local cache for file and either dl & use, or use */


	$svgfile = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.svg';
	$svgfileref = fopen( $svgfile, 'w' );
	fwrite( $svgfileref, $svgcontents );
	fclose( $svgfileref );
	// TODO test file?

	// convert SVG into PNG
	$pngfile = $avatarKeyPath  . '/' . $meta['FILENAME'] . '.png';
	$commandthumbnail = "convert -size 1280x720 -type optimize -strip svg:{$svgfile} png:{$pngfile}";
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

/* submits file to youtube, returns youtube slug */
function uploadVideo( $season, $episode ) {

	global $youtube;

	// Check to ensure that the access token was successfully acquired.
	if ( $client->getAccessToken( ) ) {

		try {
			// XXX pick file name
			$videoPath = "/path/to/file.mp4";

			$snippet = new Google_Service_YouTube_VideoSnippet( );
			$snippet->setTitle( 'SteamLUG Cast s03e00 ‐' );

			// XXX capture from youtube description
			$snippet->setDescription( 'Test description' );

			// TODO licence? comments? language? can we leave these as default?
			$snippet->setTags( array( 'linux', 'gaming', 'steam', 'steamlug', 'lug', 'podcast', 'steamlugcast', 'gaming on linux', 'steam for linux', 'linux steam', 'linux games', 'gaming on fedora', 'steam for fedora', 'fedora steam', 'fedora games', 'gaming on ubuntu', 'steam for ubuntu', 'ubuntu steam', 'ubuntu games', 'gaming on arch', 'steam for arch', 'arch steam', 'arch games' ) );

			// https://developers.google.com/youtube/v3/docs/videoCategories/list#try-it using ‘snippet’ and ‘GB’
			$snippet->setCategoryId( '20' );

			$status = new Google_Service_YouTube_VideoStatus( );
			$status->privacyStatus = 'unlisted';

			$video = new Google_Service_YouTube_Video( );
			$video->setSnippet( $snippet );
			$video->setStatus( $status );

			$chunkSizeBytes = 2 * 1024 * 1024;
			$client->setDefer( true );

			$insertRequest = $youtube->videos->insert( "status,snippet", $video );

			$media = new Google_Http_MediaFileUpload(
				$client, $insertRequest, 'video/*', null, true, $chunkSizeBytes );
			$media->setFileSize( filesize( $videoPath ) );

			// Read the media file and upload it chunk by chunk.
			$status = false;
			$handle = fopen( $videoPath, 'rb' );
			while ( !$status && !feof( $handle ) ) {
				$chunk = fread( $handle, $chunkSizeBytes );
				$status = $media->nextChunk( $chunk );
			}

			fclose( $handle );

			// If you want to make other calls after the file upload, set setDefer back to false
			$client->setDefer( false );

			// good!

		} catch ( Google_ServiceException $e ) {
			// ' A service error occurred: '. htmlspecialchars( $e->getMessage( ) )
		} catch ( Google_Exception $e ) {
			// 'An client error occurred: ' . htmlspecialchars( $e->getMessage( ) )
		}

	} else {
		// 'We’re missing an access token'
		// TODO something here :^)
	}

}

/* adds video to playlist, and changes visibility from unlisted to public */
function publishVideo( $season, $episode ) {

	// XXX copy try block from above
	// XXX fetch cast header
	global $youtube;

	$reply = $youtube->videos->listVideos( 'snippet,status' , array('id' => '' /* id from meta */ ) );
	$videoList = $reply[ 'items' ];
	$video = $videoList[ 0 ];
	$videoStatus = $video[ 'status' ];

	$updateStatus = new Google_Service_YouTube_VideoStatus( $videoStatus );
	$updateStatus->privacyStatus 'public';
	$updateVideo = new Google_Service_YouTube_Video( $video );
	$updateVideo->setStatus( $updateStatus );

	$update_response = $youtube->videos->update( 'snippet,status', $updateVideo );

	// TODO playlist support!

	// XXX debug
	// print_r( $update_response );
	return true;

}

/* returns a list of recently uploaded videos. Maybe to assist in unpublish/delete support in future? */
function getVideos( ) {

	/* TODO this */
	return array( );
}

/* helper function, because */
function addVideoToPlaylist( $playlistId, $resourceId ) {
}

/* helper function, because */
function getPlaylistId( $season ) {

	/* GET https://www.googleapis.com/youtube/v3/playlists?part=snippet&maxResults=50&mine=true&key={YOUR_API_KEY}

	We need to match on snippet.title (SteamLUG Cast s{$season}) and return snippet.channelId (UCdQCiWtqvmPwzizjmy_LkOg)
	{
	 "kind": "youtube#playlistListResponse",
	 "etag": "\"IHLB7Mi__JPvvG2zLQWAg8l36UU/gzl8Sk03FOcqCAwDAcDwHro--iQ\"",
	 "pageInfo": {
	  "totalResults": 7,
	  "resultsPerPage": 50
	 },
	 "items": [
	  {
	   "kind": "youtube#playlist",
	   "etag": "\"IHLB7Mi__JPvvG2zLQWAg8l36UU/NAdOFAq3-7KimHGn9i75BKjjNRk\"",
	   "id": "PL6S8WuxT3Rt_CmyTXnY8I0gHxnDzR7mC0",
	   "snippet": {
		"publishedAt": "2015-01-31T14:26:22.000Z",
		"channelId": "UCdQCiWtqvmPwzizjmy_LkOg",
		"title": "SteamLUG Cast s03",
		"description": "",
		"thumbnails": {
		 "default": {
		  "url": "https://i.ytimg.com/vi/jxYCYqAjRRw/default.jpg",
		  "width": 120,
		  "height": 90
		 },
	*/
}
