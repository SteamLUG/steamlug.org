<?php
$pageTitle = "Generating YouTube Video";
ini_set('implicit_flush', 1);
include_once('includes/session.php');

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

include_once('includes/functions_cast.php');
include_once('includes/functions_youtube.php');
include_once('includes/header.php');

ob_flush();

$action	= "Failure";
$body	= "";
$style	= " panel-success";

$filename = $notesPath . "/s" . $season . "e" . $episode . "/episode.txt";
if ($season !== "00" && $episode !== "00" && file_exists($filename)) {
	// TODO test we have audio?

	flush(); /* visitor should get better indication that the page is actually loading now */

	// TODO verify we dont have an existing youtube hash?
	ob_start();
	$reply = generateVideo( $season, $episode );
	$debugoutput = ob_get_clean();

	if ( $reply === false ) {
		$style = "panel-danger";
	} else {
		$action = "Success";
	}
	/* XXX debug */
	$body .= '<pre>'.print_r( $debugoutput, true ).'</pre>';
	$body .= '<p>'.print_r( $reply, true ).'</p>';
} else {

	$body = "<p>You didn’t supply a valid episode.</p>";
}

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

include_once('includes/footer.php');
