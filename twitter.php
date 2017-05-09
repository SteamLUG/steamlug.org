<?php
$pageTitle = 'Twitter';
date_default_timezone_set( 'UTC' );
include_once( 'includes/session.php' );

// TODO, verify what happens if we post over the tweet length limit
// TODO, verify what happens if our tweet exceeds length limit yet contains a URL that will be shortened
// TODO, verify CURL doesn’t have issues (apparently it will)

// are we logged in? no → leave
if ( ! login_check( ) ) {
	header( 'Location: /' );
	exit( );
} else {
	$me = $_SESSION['u'];
}

// are we admin? no → leave
if ( in_array( $me, getAdmins( ) ) ) {
} else {
	header( 'Location: /' );
	exit( );
}

include_once( 'includes/functions_events.php' );
include_once( 'includes/functions_cast.php' );
include_once( 'includes/functions_twitter.php' );

$action	= 'Failure';
$body	= '';
$style	= ' panel-success';

$nextGameEvent	= getNextEvent( false, 3600 );
$nextCastEvent	= getNextEvent( true, 3600 );
$latestCast		= getLatestCast( );
$recentTweets	= getRecentTweets( );

// are we supplying tweet, message via POST? → send tweet
if ( isset( $_POST['tweet'] ) and isset( $_POST['message'] ) ) {

	$action = 'Post Tweet';
	// set $body to a success or fail message
	$reply = postTweet( $_POST['message'] );
	// test reply here…
	if ( array_key_exists( 'errors', $reply ) ) {

		$style = 'panel-danger';
		// TODO, do additional checks in the future. Ask admins to copy/paste the error
		$body = "Error code {$reply['errors'][0]['code']}, with message: {$reply['errors'][0]['message']}<br>\n";
		$body .= '<!--' . print_r( $reply, true ) . '<br>Please copy/paste the above error, put it in a gist and share with webmaster. -->';

	} else {

		$body = "Sent ‘{$_POST['message']}’<br>\n";
	}
}

// are we supplying delete, key via POST? → delete tweet
if ( isset( $_POST['delete'] ) and isset( $_POST['key'] ) ) {

	$action = 'Delete Tweet';
	$tweet = $_POST['key'];
	// set $body to a success or fail message
	$reply = deleteTweet( $tweet );

	if ( array_key_exists( 'errors', $reply ) ) {
		$style = 'panel-danger';
		// TODO, do additional checks in the future. Ask admins to copy/paste the error
		$body = "Error code {$reply['errors'][0]['code']}, with message: {$reply['errors'][0]['message']}<br>\n";
		$body .= '<!--' . print_r( $reply, true ) . '<br>Please copy/paste the above error, put it in a gist and share with webmaster. -->';

	} else {
		// atm, assume it was all good?
		$body = "Deleted ‘{$tweet}’<br>\n";
	}
}
$tailJS = array( '/scripts/twitter.js' );
include_once( 'includes/header.php' );

echo '<h1 class="text-center">Tweet‐me‐stuff</h1>';

if ( $body !== '' ) {
	echo <<<ACTIONMSG
			<article class="panel panel-default {$style}">
				<header class="panel-heading">
					<h3 class="panel-title">{$action}</h3>
				</header>
				<div class="panel-body">
					{$body}
				</div>
			</article>
ACTIONMSG;
}

function formatTimeDifference( $diff ) {
	if ( $diff->invert == 0 ) {
		return 'the past';
	}
	if ( $diff->y > 0 || $diff->m > 0 || $diff->d > 0 ) {
		return 'the distant future';
	}
	$hours = $diff->h;
	if ( $hours == 0 && $diff->i <= 45 ) {
		return $diff->i . ' minutes';
	}
	if ( $diff->i > 45 ) {
		// Round up if we just missed the hour mark
		$hours += 1;
	} elseif ( $diff->i >= 15 ) {
		// Indicate that the difference is not close to a whole hour
		$hours .= '½';
	}
	return $hours . ( $hours === 1 ? ' hour' : ' hours' );
}

if ( $nextGameEvent != null ) {

	// TODO check current time vs now; this is heavily reliant on XML information
	$eventDate = new DateTime( ); $eventDate->setTimestamp( $nextGameEvent['utctime'] );
	$diff = date_diff( $eventDate, new DateTime( 'now' ) );
	$difference = formatTimeDifference( $diff );
	$laterMessage = 'Hey #Linux gamers, join us for some ' . $nextGameEvent['title'] . " in {$difference}! Everybody’s welcome " . $nextGameEvent['url'];
	$typicalMessage = 'Hey #Linux gamers, join us for some ' . $nextGameEvent['title'] . ' fun! Everybody’s welcome ' . $nextGameEvent['url'];
	$when = str_replace( 'T', ' ', str_replace( '+00:00', '', date( 'c', $nextGameEvent['utctime'] ) ) ) . '.';
} else {
	$when = 'a future date when someone creates the event!';
	$laterMessage = $typicalMessage = 'Hey #Linux gamers, join us for some gaming fun! Everybody’s welcome';
}
echo <<<EVENTMSG
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Event, gaming!</h3>
				</header>
				<div class="panel-body">
					<p>This takes place on {$when}</p>
					<form method="post" class="form-horizontal" action="/twitter/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet">
						<input class="control-input col-xs-11" type="text" name="message" size="70" placeholder="{$laterMessage}" value="{$laterMessage}"></div>
						<p>Best posted a few hours before event</p>
						</fieldset>
					</form>
					<form method="post" class="form-horizontal" action="/twitter/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet">
						<input class="control-input col-xs-11" type="text" name="message" size="70" placeholder="{$typicalMessage}" value="{$typicalMessage}"></div>
						<p>Best posted as we start gaming / when Steam event fires</p>
						</fieldset>
					</form>
				</div>
			</article>
EVENTMSG;

if ( $nextCastEvent != null ) {

	$eventDate = new DateTime( ); $eventDate->setTimestamp( $nextCastEvent['utctime'] );
	$diff = date_diff( $eventDate, new DateTime( 'now' ) );
	$difference = formatTimeDifference( $diff );
	$laterMessage = "Join us for the live recording of SteamLUG Cast in {$difference}, where we will be talking about %stuff. " . $nextCastEvent['url'];
	$typicalMessage = 'Join us for the live recording of SteamLUG Cast, where we will be talking about %stuff. ' . $nextCastEvent['url'];
	$when = str_replace( 'T', ' ', str_replace( '+00:00', '', date( 'c', $nextCastEvent['utctime'] ) ) ) . '.';
} else {
	$when = 'a future date when someone creates the event!';
	$laterMessage = $typicalMessage = 'Join us for the live recording of SteamLUG Cast';
}
echo <<<CASTRECORDINGMSG
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Cast, recording</h3>
				</header>
				<div class="panel-body">
					<p>This takes place on {$when}</p>
					<form method="post" class="form-horizontal" action="/twitter/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet">
						<input class="control-input col-xs-11" type="text" name="message" size="70" placeholder="{$laterMessage}" value="{$laterMessage}"></div>
						<p>Best posted a few hours before recording</p>
						</fieldset>
					</form>
					<form method="post" class="form-horizontal" action="/twitter/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet">
						<input class="control-input col-xs-11" type="text" name="message" size="70" placeholder="{$typicalMessage}" value="{$typicalMessage}"></div>
						<p>Best posted as we start recording / when Steam event fires, to encourage more people to get onto mumble</p>
						</fieldset>
					</form>
				</div>
			</article>
CASTRECORDINGMSG;

if ( $latestCast != false ) {
	// fetch latest episode and get deets

	$listHostsTwits = array( ); $listGuestsTwits = array( );
	foreach ( $latestCast['HOSTS2'] as $Host ) {
		if ( strlen( $Host['twitter'] ) > 0 )
			$listHostsTwits[] = '@' . $Host['twitter'];
		else
			$listHostsTwits[] = $Host['name'];
	}

	foreach ( $latestCast['GUESTS2'] as $Guest ) {
		if ( strlen( $Guest['twitter'] ) > 0 )
			$listGuestsTwits[] = '@' . $Guest['twitter'];
		else
			$listGuestsTwits[] = $Guest['name'];
	}
	$hosts = ( empty( $listHostsTwits ) ? '' : implode( ', ', $listHostsTwits) );
	$guests = ( empty( $listGuestsTwits ) ? '' : ' speaking with ' . implode( ', ', $listGuestsTwits) );
	$warning = ( $latestCast['PUBLISHED'] === '' ? '<span class="warning">In Progress</span>' : "<time datetime=\"{$latestCast['PUBLISHED']}\">{$latestCast['PUBLISHED']}</time>" );
	$typicalMessage = "SteamLUG Cast {$latestCast['SLUG']} ‘{$latestCast['TITLE']}’ with {$hosts}{$guests} is now available to listen to https://steamlug.org/cast/{$latestCast['SLUG']}";

	echo <<<CASTPUBLISHEDMSG
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Cast, publishing</h3>
				</header>
				<div class="panel-body">
					<p>{$warning}</p>
					<form method="post" class="form-horizontal" action="/twitter/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet">
						<input class="control-input col-xs-11" type="text" name="message" size="70" placeholder="{$typicalMessage}" value="{$typicalMessage}"></div>
						<p>Once YouTube video is processed, notes complete, RSS feed live, post this and Steam announcement</p>
						</fieldset>
					</form>
				</div>
			</article>
CASTPUBLISHEDMSG;
};

echo <<<DELETETABLEHEAD
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Delete Tweet?</h3>
				</header>
				<div class="panel-body panel-body-table">
					<table id="delete-tweets" class="table table-striped table-hover tablesorter">
						<thead>
							<tr>
								<th class="col-xs-2">When
								<th class="col-xs-9">Tweet
								<th class="col-xs-1">Delete?
							</tr>
						</thead>
						<tbody>
DELETETABLEHEAD;

	// recent tweets, option to delete
	foreach ( $recentTweets as $tweet ) {
		$tweet['created_at'] = str_replace( '+00:00', '', date( 'c', strtotime( $tweet['created_at'] ) ) );
		$tweet['created_str'] = '<time datetime="' . $tweet['created_at'] . '">' . $tweet['created_at'] . '</time>';
		echo <<<TWEET
							<tr>
								<td>{$tweet['created_str']}</td>
								<td>{$tweet['text']}</td>
								<td><form method="post" class="form-horizontal" action="/twitter/"><fieldset><input type="hidden" name="delete">
									<input type="hidden" name="key" value="{$tweet['id']}" /><input type="submit" value="x"/></fieldset></form></td>
							</tr>
TWEET;
	}

echo <<<DELETETABLEFOOTER
					</tbody>
				</table>
			</div>
		</article>
DELETETABLEFOOTER;

include_once( 'includes/footer.php' );
