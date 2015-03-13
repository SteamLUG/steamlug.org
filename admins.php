<?php
	$pageTitle = "Admins";
	include_once('includes/session.php');
	// are we logged in? no → leave
	if ( !login_check() ) {
		header( "Location: /" );
		exit();
	} else {
		$me = $_SESSION['u'];
	}

	include_once('includes/header.php');
	include_once('includes/paths.php');
	include_once('includes/functions_steam.php');

	echo <<<DOCUMENT
		<h1 class="text-center">Site Admins</h1>
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
							<th class="col-xs-5">Persona Name
							<th class="col-xs-1">Location
							<th class="col-xs-3">Last On
						</tr>
					</thead>
					<tbody>
DOCUMENT;

	$approvedUsers = getAdminNames();
	$memaybe = "";
	foreach ($approvedUsers as $admin) {
		$thenDate = new DateTime(); $thenDate->setTimestamp($admin['lastlogoff']);
		$diff = date_diff($thenDate, new DateTime("now"));
		$admin['lastlogoffdate'] = '<time datetime="' . date("c",$admin['lastlogoff']) . 
									'">' . $diff->format("%a days, %H hours") . '</time>';
		if ( $admin['steamid'] == $me ) {
			$memaybe = ' class="you"';
		} else {
			$memaybe = '';
		}
		echo <<<ADMINUSER
				<tr{$memaybe}>
					<td><img src="{$admin['avatar']}" /></td>
					<td>{$admin['steamid']}</td>
					<td>{$admin['personaname']}</td>
					<td>{$admin['loccountrycode']}</td>
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


