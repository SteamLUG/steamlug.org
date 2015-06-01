<?php

	include_once( 'creds.php' );
	// TODO this script relies on curl; make this fail too, if so?
	require_once( 'TwitterAPIExchange.php' );

	$twitterKeys = getTwitterKeys();
	// TODO move this into our variables
	$screenname	= 'SteamLUG';


	/**
	* This produces a lovely HTML5 <time> output with human^WEnglish-readable output as well
	* probably needs moving to another library
	* @param string $timestamp Twitter's idea of a joke, a time stamp of the format EEE MMM dd HH:mm:ss ZZZZZ yyyy
	* @return string a lovely <time> reply, with some human (read, localisation nightmare) formatting
	*/
	function humanTime( $timestamp ) {

		$time = strtotime( $timestamp );
		$diff = ( time() - $time );
		$today = ( $diff < 86400 ) ?
				( ( $diff < 3600 ) ? ((int)($diff / 60) . ' <abbr title="minutes">m</abbr>' ) : ( (int)($diff / 3600) . ' <abbr title="hours">h</abbr>' ) ) : date( 'd M', $time );
		return '<time datetime="' . date( DATE_ISO8601, $time ) . '" title="Time posted: ' . date ( 'd M y H:i:s' , $time ) . ' (UTC)">' . $today . '</time>';

	}


	/**
	* Twitter hands use a text version of the tweet, and a massive JSON to represent their
	* tweaks to it. We have to parse most of these (TODO this function lacks support for $symbols, other entities)
	* @param array $tweet a json blob of a tweet, from Twitter API
	* @return string a mixed-down fully HTMLed version of the tweet with linked users, hashtags, URLs
	*/
	function populateTweet( $tweet ) {

		$hashtagP	= '<a href="//twitter.com/search?q=%%23%s&amp;src=hash" class="hashtag" rel="nofollow" target="_blank">#%s</a>';
		$urlP		= '<a href="%s" class="url" el="nofollow" target="_blank" title="%s">%s</a>';
		$userP		= '<a href="//twitter.com/%s" class="twatter" rel="nofollow" target="_blank" title="%s">@%s</a>';
		$mediaP		= '<a href="%s" class="media" rel="nofollow" target="_blank" title="%s">%s</a>';

		$entities = array();
		foreach ( $tweet[ 'entities' ][ 'hashtags' ] as $e ) {

			$entities[ ] = array(
				'start' => $e[ 'indices' ][ 0 ], 'length' => $e[ 'indices' ][ 1 ] - $e[ 'indices' ][ 0 ],
				'replace' => sprintf( $hashtagP, strtolower($e[ 'text' ]), $e[ 'text' ]) );
		}

		foreach ( $tweet[ 'entities' ][ 'urls' ] as $e ) {

			$entities[ ] = array(
				'start' => $e[ 'indices' ][ 0 ], 'length' => $e[ 'indices' ][ 1 ] - $e[ 'indices' ][ 0 ],
				'replace' => sprintf( $urlP, $e[ 'expanded_url' ], $e[ 'expanded_url' ], $e[ 'display_url' ]) );
		}

		foreach ( $tweet[ 'entities' ][ 'user_mentions' ] as $e ) {

			$entities[ ] = array(
				'start' => $e[ 'indices' ][ 0 ], 'length' => $e[ 'indices' ][ 1 ] - $e[ 'indices' ][ 0 ],
				'replace' => sprintf( $userP, strtolower($e[ 'screen_name' ]), $e[ 'name' ], $e[ 'screen_name' ]) );
		}

		if ( array_key_exists( 'media', $tweet[ 'entities' ]) ) {
			foreach ( $tweet[ 'entities' ][ 'media' ] as $e ) {

				$entities[ ] = array(
					'start' => $e[ 'indices' ][ 0 ], 'length' => $e[ 'indices' ][ 1 ] - $e[ 'indices' ][ 0 ],
					'replace' => sprintf( $mediaP, $e[ 'url' ], $e[ 'expanded_url' ], $e[ 'display_url' ]) );
			}
		}

		usort( $entities, function($a, $b) { return $b[ 'start' ] - $a[ 'start' ]; } );
		$replacement = $tweet[ 'text' ];
		foreach( $entities as $e ) {
			$replacement = mb_substr( $replacement, 0, $e[ 'start' ], 'UTF-8' ) . $e[ 'replace' ] .
				mb_substr( $replacement, $e[ 'start' ] + $e[ 'length' ], null, 'UTF-8' );
		}
		return $replacement;
	}


	/**
	* Fetch recent tweets from our main account (currently hard-coded)
	* @param integer $limit limit the requested tweets to this amount (use this! saves lots of b/w)
	* @return array hash of content from the Twitter API.
	*/
	function getRecentTweets( $limit = 8 ) {

		global $screenname, $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

		$fields = array(
			'screen_name' => $screenname,
			'count' => $limit,
			'trim_user' => false,
			'include_entities' => true,
		);

		$result = $twit->setGetfields( $fields )
					->buildOauth( $resource, 'GET' )
					->performRequest( );
		// TODO error handling
		return json_decode( $result, true );
	}


	/**
	* Fetch recent mentions from our main account (currently hard-coded)
	* Included so our backend could offer ability to reply to users
	* @param integer $limit limit the requested tweets to this amount (use this! saves lots of b/w)
	* @return array hash of content from the Twitter API.
	*/
	function getRecentMentions( $limit = 8 ) {

		global $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';

		$fields = array(
			'count' => $limit,
			'trim_user' => true,
		);

		$result = $twit->setGetfields( $fields )
					->buildOauth( $resource, 'GET' )
					->performRequest( );
		// TODO error handling
		return json_decode( $result, true );
	}


	/**
	* Sends a tweet from our main account (currently hard-coded)
	* @param string $message your lovely status update to our audience
	* @return array hash blog back from the Twitter API
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
		// NOTE 403 error can mean duplicate or rate limited, but this API wont return that…
		// TODO error handling
		return json_decode( $result, true );

		// Good reply: Array ( [created_at] => Fri Mar 27 14:03:21 +0000 2015 [id] => 581456476167888896 [id_str] => 581456476167888896 [text] => @Corben78 🐧 🎮 [source] => SteamLUG.org [truncated] => [in_reply_to_status_id] => [in_reply_to_status_id_str] => [in_reply_to_user_id] => [in_reply_to_user_id_str] => [in_reply_to_screen_name] => [user] => Array ( [id] => 1282779350 [id_str] => 1282779350 [name] => SteamLUG [screen_name] => SteamLUG [location] => [profile_location] => [description] => The Steam Linux User Group! A multilingual community of Linux gamers which aims to be a fun, welcoming space for people of all backgrounds and aptitudes [url] => http://t.co/UV563TiKNB [entities] => Array ( [url] => Array ( [urls] => Array ( [0] => Array ( [url] => http://t.co/UV563TiKNB [expanded_url] => http://steamlug.org [display_url] => steamlug.org [indices] => Array ( [0] => 0 [1] => 22 ) ) ) ) [description] => Array ( [urls] => Array ( ) ) ) [protected] => [followers_count] => 339 [friends_count] => 5 [listed_count] => 23 [created_at] => Wed Mar 20 09:10:33 +0000 2013 [favourites_count] => 30 [utc_offset] => [time_zone] => [geo_enabled] => [verified] => [statuses_count] => 852 [lang] => en [contributors_enabled] => [is_translator] => [is_translation_enabled] => [profile_background_color] => C0DEED [profile_background_image_url] => http://abs.twimg.com/images/themes/theme1/bg.png [profile_background_image_url_https] => https://abs.twimg.com/images/themes/theme1/bg.png [profile_background_tile] => [profile_image_url] => http://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_image_url_https] => https://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_link_color] => 0084B4 [profile_sidebar_border_color] => C0DEED [profile_sidebar_fill_color] => DDEEF6 [profile_text_color] => 333333 [profile_use_background_image] => 1 [default_profile] => 1 [default_profile_image] => [following] => [follow_request_sent] => [notifications] => ) [geo] => [coordinates] => [place] => [contributors] => [retweet_count] => 0 [favorite_count] => 0 [entities] => Array ( [hashtags] => Array ( ) [symbols] => Array ( ) [user_mentions] => Array ( [0] => Array ( [screen_name] => Corben78 [name] => Corben Dallas [id] => 56698692 [id_str] => 56698692 [indices] => Array ( [0] => 0 [1] => 9 ) ) ) [urls] => Array ( ) ) [favorited] => [retweeted] => [lang] => und )

	}


	/**
	* Deletes a tweet from our main account (currently hard-coded)
	* use as: deleteTweet( '577505080074756097' )
	* @param string $tweetId Twitter-namespaced ID of the status to delete
	* @return array hash blog back from the Twitter API
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

		// Good reply: Array ( [created_at] => Fri Mar 27 14:03:21 +0000 2015 [id] => 581456476167888896 [id_str] => 581456476167888896 [text] => @Corben78 🐧 🎮 [source] => SteamLUG.org [truncated] => [in_reply_to_status_id] => [in_reply_to_status_id_str] => [in_reply_to_user_id] => [in_reply_to_user_id_str] => [in_reply_to_screen_name] => [user] => Array ( [id] => 1282779350 [id_str] => 1282779350 [name] => SteamLUG [screen_name] => SteamLUG [location] => [profile_location] => [description] => The Steam Linux User Group! A multilingual community of Linux gamers which aims to be a fun, welcoming space for people of all backgrounds and aptitudes [url] => http://t.co/UV563TiKNB [entities] => Array ( [url] => Array ( [urls] => Array ( [0] => Array ( [url] => http://t.co/UV563TiKNB [expanded_url] => http://steamlug.org [display_url] => steamlug.org [indices] => Array ( [0] => 0 [1] => 22 ) ) ) ) [description] => Array ( [urls] => Array ( ) ) ) [protected] => [followers_count] => 339 [friends_count] => 5 [listed_count] => 23 [created_at] => Wed Mar 20 09:10:33 +0000 2013 [favourites_count] => 30 [utc_offset] => [time_zone] => [geo_enabled] => [verified] => [statuses_count] => 852 [lang] => en [contributors_enabled] => [is_translator] => [is_translation_enabled] => [profile_background_color] => C0DEED [profile_background_image_url] => http://abs.twimg.com/images/themes/theme1/bg.png [profile_background_image_url_https] => https://abs.twimg.com/images/themes/theme1/bg.png [profile_background_tile] => [profile_image_url] => http://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_image_url_https] => https://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_link_color] => 0084B4 [profile_sidebar_border_color] => C0DEED [profile_sidebar_fill_color] => DDEEF6 [profile_text_color] => 333333 [profile_use_background_image] => 1 [default_profile] => 1 [default_profile_image] => [following] => [follow_request_sent] => [notifications] => ) [geo] => [coordinates] => [place] => [contributors] => [retweet_count] => 0 [favorite_count] => 0 [entities] => Array ( [hashtags] => Array ( ) [symbols] => Array ( ) [user_mentions] => Array ( [0] => Array ( [screen_name] => Corben78 [name] => Corben Dallas [id] => 56698692 [id_str] => 56698692 [indices] => Array ( [0] => 0 [1] => 9 ) ) ) [urls] => Array ( ) ) [favorited] => [retweeted] => [lang] => und )
	}
