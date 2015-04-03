<?php

	include_once( 'creds.php' );
	// TODO this script relies on curl; make this fail too, if so?
	require_once( 'TwitterAPIExchange.php' );

	$twitterKeys = getTwitterKeys();
	// TODO move this into our variables
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
		Expose recent mentions so our admins can reply to users easily
	*/
	function getRecentMentions( $count = 8 ) {

		global $twitterKeys;
		$twit = new TwitterAPIExchange( $twitterKeys );
		$resource = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';

		$fields = array(
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
		// NOTE 403 error can mean duplicate or rate limited, but this API wont return that…
		// TODO error handling
		return json_decode( $result, true );

		// Good reply: Array ( [created_at] => Fri Mar 27 14:03:21 +0000 2015 [id] => 581456476167888896 [id_str] => 581456476167888896 [text] => @Corben78 🐧 🎮 [source] => SteamLUG.org [truncated] => [in_reply_to_status_id] => [in_reply_to_status_id_str] => [in_reply_to_user_id] => [in_reply_to_user_id_str] => [in_reply_to_screen_name] => [user] => Array ( [id] => 1282779350 [id_str] => 1282779350 [name] => SteamLUG [screen_name] => SteamLUG [location] => [profile_location] => [description] => The Steam Linux User Group! A multilingual community of Linux gamers which aims to be a fun, welcoming space for people of all backgrounds and aptitudes [url] => http://t.co/UV563TiKNB [entities] => Array ( [url] => Array ( [urls] => Array ( [0] => Array ( [url] => http://t.co/UV563TiKNB [expanded_url] => http://steamlug.org [display_url] => steamlug.org [indices] => Array ( [0] => 0 [1] => 22 ) ) ) ) [description] => Array ( [urls] => Array ( ) ) ) [protected] => [followers_count] => 339 [friends_count] => 5 [listed_count] => 23 [created_at] => Wed Mar 20 09:10:33 +0000 2013 [favourites_count] => 30 [utc_offset] => [time_zone] => [geo_enabled] => [verified] => [statuses_count] => 852 [lang] => en [contributors_enabled] => [is_translator] => [is_translation_enabled] => [profile_background_color] => C0DEED [profile_background_image_url] => http://abs.twimg.com/images/themes/theme1/bg.png [profile_background_image_url_https] => https://abs.twimg.com/images/themes/theme1/bg.png [profile_background_tile] => [profile_image_url] => http://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_image_url_https] => https://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_link_color] => 0084B4 [profile_sidebar_border_color] => C0DEED [profile_sidebar_fill_color] => DDEEF6 [profile_text_color] => 333333 [profile_use_background_image] => 1 [default_profile] => 1 [default_profile_image] => [following] => [follow_request_sent] => [notifications] => ) [geo] => [coordinates] => [place] => [contributors] => [retweet_count] => 0 [favorite_count] => 0 [entities] => Array ( [hashtags] => Array ( ) [symbols] => Array ( ) [user_mentions] => Array ( [0] => Array ( [screen_name] => Corben78 [name] => Corben Dallas [id] => 56698692 [id_str] => 56698692 [indices] => Array ( [0] => 0 [1] => 9 ) ) ) [urls] => Array ( ) ) [favorited] => [retweeted] => [lang] => und )

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

		// Good reply: Array ( [created_at] => Fri Mar 27 14:03:21 +0000 2015 [id] => 581456476167888896 [id_str] => 581456476167888896 [text] => @Corben78 🐧 🎮 [source] => SteamLUG.org [truncated] => [in_reply_to_status_id] => [in_reply_to_status_id_str] => [in_reply_to_user_id] => [in_reply_to_user_id_str] => [in_reply_to_screen_name] => [user] => Array ( [id] => 1282779350 [id_str] => 1282779350 [name] => SteamLUG [screen_name] => SteamLUG [location] => [profile_location] => [description] => The Steam Linux User Group! A multilingual community of Linux gamers which aims to be a fun, welcoming space for people of all backgrounds and aptitudes [url] => http://t.co/UV563TiKNB [entities] => Array ( [url] => Array ( [urls] => Array ( [0] => Array ( [url] => http://t.co/UV563TiKNB [expanded_url] => http://steamlug.org [display_url] => steamlug.org [indices] => Array ( [0] => 0 [1] => 22 ) ) ) ) [description] => Array ( [urls] => Array ( ) ) ) [protected] => [followers_count] => 339 [friends_count] => 5 [listed_count] => 23 [created_at] => Wed Mar 20 09:10:33 +0000 2013 [favourites_count] => 30 [utc_offset] => [time_zone] => [geo_enabled] => [verified] => [statuses_count] => 852 [lang] => en [contributors_enabled] => [is_translator] => [is_translation_enabled] => [profile_background_color] => C0DEED [profile_background_image_url] => http://abs.twimg.com/images/themes/theme1/bg.png [profile_background_image_url_https] => https://abs.twimg.com/images/themes/theme1/bg.png [profile_background_tile] => [profile_image_url] => http://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_image_url_https] => https://pbs.twimg.com/profile_images/3420706844/0169c9632f67b7928a84e723fb460380_normal.png [profile_link_color] => 0084B4 [profile_sidebar_border_color] => C0DEED [profile_sidebar_fill_color] => DDEEF6 [profile_text_color] => 333333 [profile_use_background_image] => 1 [default_profile] => 1 [default_profile_image] => [following] => [follow_request_sent] => [notifications] => ) [geo] => [coordinates] => [place] => [contributors] => [retweet_count] => 0 [favorite_count] => 0 [entities] => Array ( [hashtags] => Array ( ) [symbols] => Array ( ) [user_mentions] => Array ( [0] => Array ( [screen_name] => Corben78 [name] => Corben Dallas [id] => 56698692 [id_str] => 56698692 [indices] => Array ( [0] => 0 [1] => 9 ) ) ) [urls] => Array ( ) ) [favorited] => [retweeted] => [lang] => und )
	}
