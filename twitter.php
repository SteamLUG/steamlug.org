<?php
$pageTitle = "Avatars";
include_once('includes/session.php');
include_once('includes/paths.php');

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
include_once('includes/functions_twitter.php');

$action = "Failure";
$body = "";
$style = " panel-success";

$nextGameEvent = getNextEvent( false );
$nextCastEvent = getNextEvent( true );

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
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$nextGameEvent['title'];?>" value="<?=$nextGameEvent['title'];?>"></div>
						<p>Best posted a few hours before event</p>
						</fieldset>
					</form>
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$nextGameEvent['title'];?>" value="<?=$nextGameEvent['title'];?>"></div>
						<p>Best posted as we start gaming / when Steam event fires</p>
						</fieldset>
					</form>
				</div>
			</article>
<?php
print "<!--\n";
print_r ( $nextCastEvent );
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
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$nextCastEvent['title'];?>" value="<?=$nextCastEvent['title'];?>"></div>
						<p>Best posted a few hours before recording</p>
						</fieldset>
					</form>
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$nextCastEvent['title'];?>" value="<?=$nextCastEvent['title'];?>"></div>
						<p>Best posted as we start recording / when Steam event fires, to encourage more people to get onto mumble</p>
						</fieldset>
					</form>
				</div>
			</article>
<?php
	// fetch latest episode and get deets
	$castPublish = "blah blah";
?>
			<article class="panel panel-default twit">
				<header class="panel-heading">
					<h3 class="panel-title">Cast, publishing</h3>
				</header>
				<div class="panel-body">
					<form method="get" class="form-horizontal" action="/twitter.php/">
						<fieldset>
						<input type="hidden" name="tweet">
						<div class="form-group"><input type="submit" class="col-xs-1 btn btn-primary" value="Tweet"><input maxlength="140" class="control-input col-xs-11" name="message" placeholder="<?=$castPublish;?>" value="<?=$castPublish;?>"></div>
						<p>Once YouTube video is processed, notes complete, RSS feed live, post this and Steam announcement</p>
						</fieldset>
					</form>
				</div>
			</article>
<?php include_once('includes/footer.php');

