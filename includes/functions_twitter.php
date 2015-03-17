<?php

	include_once( 'creds.php' );
	// TODO this script relies on curl; make this fail too, if so?
	require_once( 'TwitterAPIExchange.php' );

	$twitterKeys = getTwitterKeys();
	$screenname	= 'SteamLUG';

	/*
		Expose recent tweets so our admins can delete a mistake
	*/
	function getRecentTweets() {

		global $screenname, $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$count = 8;

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
			return;

		// TODO do checks for >140 and being < a few

		global $screenname, $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/update.json';

		$fields = array(
			'status' => $message,
			'trim_user' => true,
		);

		$result = $twit->setGetfields( $fields )
					->buildOauth( $resource, 'GET' )
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
			return;

		// TODO do checks for >140 and being < a few

		global $screenname, $twitterKeys;
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
