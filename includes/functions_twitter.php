<?php

	include_once( 'creds.php' );
	// TODO this script relies on curl; make this fail too, if so?
	require_once( 'TwitterAPIExchange.php' );

	$twitterKeys = getTwitterKeys();
	$screenname	= 'SteamLUG';

	/*
		Expose recent tweets so our admins can delete a mistake
	*/
	function getRecentTweets( $count = 8 ) {

		global $screenname, $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

		$fields = array(
			'screen_name' => $screenname,
			'count' => $count,
			'trim_user' => true,
		);

		$result = $twit->setGetfields( $fields )
					->buildOauth( $resource, 'GET' )
					->performRequest( );
		// TODO error handling
		return json_decode( $result, true );
	}

	/*
		Accept any text, push to Twitter?
	*/
	function postTweet( $message ) {

		if ( $message == "" )
			return array( 'errors' => array( array( "code" => -1, "message" => 'No message given?' ), array() ) );

		// TODO do checks for >140 and being < a few

		global $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/update.json';

		$fields = array(
			'status' => $message,
			'trim_user' => true,
		);

		$result = $twit->setPostfields( $fields )
					->buildOauth( $resource, 'POST' )
					->performRequest( );
		// TODO error handling
		return json_decode( $result, true );

	}

	/*
		Take ID to tweet, pass it to twitter
		deleteTweet( '577505080074756097' )
	*/
	function deleteTweet( $tweetId ) {

		// Returns if we cannot delete this tweet
		// Array ( [errors] => Array ( [0] => Array ( [message] => Your credentials do not allow access to this resource [code] => 220 ) ) )

		if ( !isset( $tweetId ) )
			return array( 'errors' => array( array( "code" => -1, "message" => 'No message id given?' ), array() ) );

		// TODO do checks for >140 and being < a few

		global $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/destroy/' . $tweetId . '.json';

		$fields = array(
			'id' => $tweetId,
			'trim_user' => true,
		);

		$result = $twit->setPostfields( $fields )
					->buildOauth( $resource, 'POST' )
					->performRequest( );
		// TODO error handling
		return json_decode( $result, true );

	}
