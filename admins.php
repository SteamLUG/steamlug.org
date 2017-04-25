<?php
	$pageTitle = "Admins";
	date_default_timezone_set('UTC');
	include_once('includes/session.php');
	// are we logged in? no → leave
	if ( !login_check() ) {
		header( "Location: /" );
		exit();
	} else {
		$me = $_SESSION['u'];
	}

	include_once('includes/header.php');
	include_once('includes/functions_steam.php');

	$groupcount = getGroupCount();

	// TODO this document needs more than just a list of members
	// TODO link to useful resources like github wiki references?
	echo <<<DOCUMENT
		<h1 class="text-center">Admin Page</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Helpful Stuff</h3>
			</header>
			<div class="panel-body">
				<p>Hey! So if you’re reading this, you’re one of SteamLUGs few admins. Hopefully you’ve been given all the links you need, but if not… here are some!</p>
				<p>Our <a href="https://github.com/SteamLUG/">GitHub</a> with all the <a href="https://github.com/SteamLUG/steamlug.org/">site source</a>, <a href="https://github.com/SteamLUG/steamlugcast-shownotes">SteamLUG Cast notes</a>, and <a href="https://github.com/SteamLUG/steamlug-gaming-servers">gaming servers list</a>. Only a few admins will have write access to the site repo, but notes and gaming servers should be r/w for contributors. For people that aren’t yet contributors, accepting their pull requests on these two repos is allowed.</p>
				<p>All of the site‐based event querying relies on XML files we pull from Steam and cache locally. They’re on a cronjob, if our local copy is old, you can prompt the server to get more recent copies by visiting <a href="https://data.steamlug.org/updatesteamlug.php">Update events</a>. </p>
				<p>For editors of SteamLUG Cast, we have this <a href="https://github.com/SteamLUG/steamlug.org/wiki/How-to-make-podcast">wiki page</a> (that needs updating) that shares advice on HOWTO produce the files, with a future plan to make some of it automated on this Website.</p>
				<p>We list previous Cast guests <a href="https://github.com/SteamLUG/steamlug.org/wiki/SteamLUG-Cast-Guests">here</a>, alongside future guests that have shown interest, and our wishlist on future guests.</p>
			</div>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Current Member Count <b> {$groupcount}</b></h3>
			</header>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Admins</h3>
			</header>
			<div class="panel-body panel-body-table">
				<table id="admins" class="table table-striped table-hover tablesorter">
					<thead>
						<tr>
							<th class="col-xs-1">Avatar
							<th class="col-xs-2">SteamID
							<th class="col-xs-6">Persona Name
							<th class="col-xs-3">Online?
						</tr>
					</thead>
					<tbody>
DOCUMENT;

	$approvedUsers = getAdminNames();
	$memaybe = "";
	// TODO should this pull Steam Group admins, to show if we have any differences?
	foreach ($approvedUsers as $admin) {
		print "\n<!-- ";
		print_r( $admin );
		print " -->\n";
		if ($admin['personastate'] != 0) {
			$thenDate = new DateTime(); $thenDate->setTimestamp($admin['lastlogoff']);
			$diff = date_diff($thenDate, new DateTime("now"));
			$admin['lastlogoffdate'] = '<time datetime="' . date("c",$admin['lastlogoff']) .
										'">' . $diff->format("%a days, %H hours") . '</time>';
		} else {
			$admin['lastlogoffdate'] = 'Offline';
		}
		if ( $admin['steamid'] == $me ) {
			$memaybe = ' class="you"';
		} else {
			$memaybe = '';
		}
		echo <<<ADMINUSER
				<tr{$memaybe}>
					<td><img src="{$admin['avatar']}" /></td>
					<td>{$admin['steamid']}</td>
					<td><a href="{$admin['profileurl']}">{$admin['personaname']}</a></td>
					<td>{$admin['lastlogoffdate']}</td>
				</tr>
ADMINUSER;

	}

	echo <<<DOCUMENT
					</tbody>
				</table>
			</div>
		</article>
DOCUMENT;
	include_once('includes/footer.php');


