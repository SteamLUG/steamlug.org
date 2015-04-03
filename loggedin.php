<?php
	$pageTitle = "Welcome!";
	include_once('includes/session.php');
	if (!login_check()) {
		/* people not logged in should not visit here */
		header("Location: /" );
		exit();
	}
	// TODO add timed meta redirect to last known location after 15?
	// TODO consider if this is sensible, considering we want people to read our privacy info

	include_once('includes/header.php');

	$joinGroup = <<<JOINLINK
<p>We noticed you’re not a part of SteamLUG, would you kindly <a class="label label-success group-join" href="http://steamcommunity.com/groups/steamlug/">join our Steam Group</a></p>
JOINLINK;
	if ( isset( $_SESSION['g'] ) and ( $_SESSION['g'] == true ) ) {
		$joinGroup = "";
	}

	if ( isset( $_GET['returnto'] ) ) {

		$return = "<p><a href=\"" . htmlspecialchars($_GET['returnto']) . "\">Return to what you were doing…</a></p>";
	}
	$you = htmlspecialchars($_SESSION['n'], ENT_NOQUOTES);
	echo <<<DOCUMENT
		<h1 class="text-center">Welcome, {$you}</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Authed, A OK</h3>
			</header>
			<div class="panel-body">
				<p>We’re excited to have you join us!</p>
				<p>As part of the sign‐in process, we temporarily store your SteamID, your username and avatar URL as they are now. We check and store if you are a member of our Steam group. If your profile is private, we will not have some of this data. All of this is retained in your user session with our server. To remove it, merely <a href="logout.php">Log out</a> and it will be forgotten.</p>

				{$joinGroup}

				{$return}

			</div>
		</article>
DOCUMENT;

	include_once('includes/footer.php');

