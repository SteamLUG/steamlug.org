<?php
$pageTitle = "Twitter";
date_default_timezone_set('UTC');
include_once('includes/session.php');

// TODO, remove 140 maxlength limits, URL will get shrunk, and we don’t want messages cropped
// TODO, verify CURL doesn’t have issues (apparently it will)

// are we logged in? no → leave
if ( !login_check() ) {
	header( "Location: /" );
	exit();
} else {
	$me = $_SESSION['u'];
}

// are we admin? no → leave
if ( in_array( $me, getAdmins() ) ) {
} else {
	header( "Location: /" );
	exit();
}

include_once('includes/functions_events.php');
include_once('includes/functions_cast.php');
include_once('includes/functions_twitter.php');

$action = "Failure";
$body = "";
$style = " panel-success";

$nextGameEvent = getNextEvent( false );
$nextCastEvent = getNextEvent( true );
$latestCast = getLatestCast( );
$recentTweets = getRecentTweets( );

// are we supplying a tweet via GET? → send tweet
if ( isset( $_GET['tweet'] ) and isset( $_GET['message'] ) ) {

	// set $body to a success or fail message
}

include_once('includes/header.php');

print "<h1 class=\"text-center\">Tweet‐me‐stuff</h1>";

if ( $body !== "" ) {
print <<<ACTIONMSG
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

print "<!--\n";
print_r ( $nextGameEvent );
$laterMessage = 'Hey #Linux gamers, join us for some ' . $nextGameEvent['title'] . ' in %time! Everybody’s welcome ' . $nextGameEvent['url'];
$typicalMessage = 'Hey #Linux gamers, join us for some ' . $nextGameEvent['title'] . ' fun! Everybody’s welcome ' . $nextGameEvent['url'];
print "-->\n";
?>
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Event, gaming!</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$laterMessage;?>" value="<?=$laterMessage;?>"></div>
						<p>Best posted a few hours before event</p>
						</fieldset>
					</form>
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$typicalMessage;?>" value="<?=$typicalMessage;?>"></div>
						<p>Best posted as we start gaming / when Steam event fires</p>
						</fieldset>
					</form>
				</div>
			</article>
<?php
print "<!--\n";
print_r ( $nextCastEvent );
$laterMessage = "Join us for the live recording of SteamLUG Cast in %time, where we will be talking about %stuff. " . $nextCastEvent['url'];
$typicalMessage = "Join us for the live recording of SteamLUG Cast, where we will be talking about %stuff. " . $nextCastEvent['url'];
print "-->\n";
?>
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Cast, recording</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$laterMessage;?>" value="<?=$laterMessage;?>"></div>
						<p>Best posted a few hours before recording</p>
						</fieldset>
					</form>
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$typicalMessage;?>" value="<?=$typicalMessage;?>"></div>
						<p>Best posted as we start recording / when Steam event fires, to encourage more people to get onto mumble</p>
						</fieldset>
					</form>
				</div>
			</article>
<?php
	// fetch latest episode and get deets
	// TODO this is waiting on a new functions_cast(?) to have some easy-to-use data calls
	print "<!--\n";
	print_r ( $latestCast );
	print "-->\n";

	$slug = 's' . $latestCast['SEASON'] . 'e' . $latestCast['EPISODE'];
	$title = $latestCast['TITLE'];
	$listHostsTwits = array(); $listGuestsTwits = array();
	foreach ($latestCast['HOSTS2'] as $Host) {
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
	$hosts = ( empty($listHostsTwits) ? '' : implode( ', ', $listHostsTwits) );
	$guests = ( empty($listGuestsTwits) ? '' : ' speaking with ' . implode( ', ', $listGuestsTwits) );
	$typicalMessage = "SteamLUG Cast {$slug} ‘{$title}’ with {$hosts}{$guests} is now available to listen to https://steamlug.org/cast/{$slug}";
?>
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Cast, publishing</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$typicalMessage;?>" value="<?=$typicalMessage;?>"></div>
						<p>Once YouTube video is processed, notes complete, RSS feed live, post this and Steam announcement</p>
						</fieldset>
					</form>
				</div>
			</article>
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
<?php

	// recent tweets, option to delete
	foreach ($recentTweets as $tweet) {
		print "\n<!-- ";
		print_r( $tweet );
		print " -->\n";
		$tweet['created_at'] = str_replace( '+00:00', '', date("c", strtotime( $tweet['created_at'] ) ) );
		$tweet['created_str'] = '<time datetime="' . $tweet['created_at'] . '">' . $tweet['created_at'] . '</time>';
		echo <<<TWEET
				<tr>
					<td>{$tweet['created_str']}</td>
					<td>{$tweet['text']}</td>
					<td><input type="hidden" name="tweet" value="{$tweet['id']}" /><input type="submit" value="x"/></td>
				</tr>
TWEET;

	}
?>
					</tbody>
				</table>
			</div>
		</article>
<?php include_once('includes/footer.php');

