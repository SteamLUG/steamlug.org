<?php
include_once( 'paths.php' );
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
	$updateStatus->privacyStatus = 'public';
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

/**
 * Returns YouTube playlist ID
 * @param integer $season the requested playlist season number
 * @return string|false the playlist ID if found, or false if not
 */
function getPlaylistId( $season ) {

	include_once( 'functions_geturl.php' );
	$googleKeys = getGoogleKeys( );
	if ( $season == '' )
		return;

	$season	= str_pad($season, 2, '0', STR_PAD_LEFT);
	$title	= 'SteamLUG Cast s' . $season;

	$queryURL = "https://www.googleapis.com/youtube/v3/playlists";
	/* Channel ID can be found at https://www.youtube.com/account_advanced, it can also be fetched via https://www.googleapis.com/youtube/v3/channels?key={YOUR_API_KEY}&forUsername={USER_NAME}&part=id but that is blergh */
	// TODO move channelId to creds?
	$params = array( 'key' => $googleKeys[ 'api_key' ], 'part' => 'snippet', 'maxResults' => '50', 'channelId' => 'UCdQCiWtqvmPwzizjmy_LkOg' );
	$reply = geturl( $queryURL, $params );

	if ( is_numeric( $reply ) )
		return false;

	$data = json_decode( $reply, true );
	$data = $data[ 'items' ];

	foreach ( $data as $playlist ) {

		if ( $title == $playlist[ 'snippet' ][ 'title' ] ) {
			return $playlist[ 'id' ];
		}
	}
	return false;
}

/* helper function, because */
function getVideoViews( $resourceIds ) {

	include_once( 'functions_geturl.php' );
	$googleKeys = getGoogleKeys( );
	if ( $resourceIds == '' )
		return;

	$viewCounts = array();
	$remainingVideos = $resourceIds;

	while ( count( $remainingVideos ) > 0 ) {

		$currentVideos = array_slice( $remainingVideos, 0, 30 );
		$remainingVideos = array_slice( $remainingVideos, 30 );

		$queryURL = "https://www.googleapis.com/youtube/v3/videos";
		$params = array( 'key' => $googleKeys[ 'api_key' ], 'part' => 'statistics', 'id' => join( ',', $currentVideos ) );

		$reply = geturl( $queryURL, $params );

		if ( is_numeric( $reply ) )
			return false;

		$data = json_decode( $reply, true );
		$data = $data[ 'items' ];
		foreach ( $data as $item ) {
			$viewCounts[ $item[ 'id' ] ] = $item[ 'statistics' ][ 'viewCount' ];
		}
	}
	return $viewCounts;
}

/* helper function, because */
function getVideoDetails( $resourceIds ) {

	include_once( 'functions_geturl.php' );
	$googleKeys = getGoogleKeys( );
	if ( $resourceIds == '' )
		return;

	$details = array();
	$remainingVideos = $resourceIds;

	while ( count( $remainingVideos ) > 0 ) {

		$currentVideos = array_slice( $remainingVideos, 0, 30 );
		$remainingVideos = array_slice( $remainingVideos, 30 );

		$queryURL = "https://www.googleapis.com/youtube/v3/videos";
		$params = array( 'key' => $googleKeys[ 'api_key' ], 'part' => 'snippet', 'id' => join( ',', $currentVideos ) );

		$reply = geturl( $queryURL, $params );

		if ( is_numeric( $reply ) )
			return false;

		$data = json_decode( $reply, true );
		$data = $data[ 'items' ];
		foreach ( $data as $item ) {
			$details[ $item[ 'id' ] ] = $item[ 'snippet' ];
		}
	}
	return $details;
}

