<?php
// our error pages probably don’t want to touch this
if ( !isset($skipAuth) ) {
	include_once('session.php');
}

if (!isset($description))
{
	$description = "SteamLUG - the Steam Linux User Group! A multilingual community of Linux gamers which aims to be a fun, welcoming space for people of all backgrounds and aptitudes.";
}
if (!isset($keywords))
{
	$keywords = "Linux, Gaming, Steam, Community";
}

if (!isset($pageTitle))
{
	$pageTitle = "Super Secret Unnamed Page!";
}

if (!isset($rssLinks))
{
	$rssLinks = '<link rel="alternate" type="application/rss+xml" title="RSS" href="http://steamcommunity.com/groups/steamlug/rss/" />';
}

$weareadmin = false;
$logIn = "";
if ( !isset($skipAuth) ) {
	if(!login_check())
	{
		$steam_login_verify = SteamSignIn::validate();
		if (!empty($steam_login_verify))
		{
			login($steam_login_verify);
			header( "Location: /loggedin/" );
			exit();
		} else {

			$steam_sign_in_url = SteamSignIn::genUrl();
			$logIn = <<<AUTHBUTTON
<li class="steamLogin"><a href="{$steam_sign_in_url}"><img src="/images/sits_01.png" alt="Log into Steam" /></a></li>
AUTHBUTTON;
		}
	} else {
		if ( isset( $_SESSION['a'] ) and ( $_SESSION['a'] != "" ) ) {
			$logIn = <<<SHOWAVATAR
<li class="steamLogin navbar-avatar"><a href="/logout"><img width="32" height="32" id="steamAvatar" alt="Your Steam avatar" src="{$_SESSION['a']}" /></a></li>
SHOWAVATAR;
		} else {
			$logIn = <<<SHOWAVATAR
<li class="steamLogin navbar-avatar"><a href="/logout"><img width="32" height="32" id="steamAvatar" alt="Default Steam avatar" src="/avatars/default.png" /></a></li>
SHOWAVATAR;
		}
		if ( in_array( $_SESSION['u'], getAdmins() ) ) {
			$weareadmin = true;
		}
	}
}
// send only after any cookie tweaks
header("Cache-Control: public, max-age=60");

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta charset="UTF-8" />
		<title>SteamLUG <?= $pageTitle; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="description" content="<?= $description; ?>" />
		<meta name="keywords" content="<?= $keywords; ?>" />
		<?= $rssLinks . "\n"; ?>
		<link rel="stylesheet" href="/css/bootstrap.steamlug.min.css" type="text/css" />
		<link rel="icon" href="/mobile-favicon.png" sizes="192x192" />
		<script type="text/javascript">
			var serverTime = <?= microtime(true); ?>;
		</script>

<?php

	if (isset($extraCrap))
	{
		echo $extraCrap;
	}
	if (isset($extraJS))
	{
		echo "\t\t<script type=\"text/javascript\">\n";
		echo $extraJS;
		echo "\n\t\t</script>\n";
	}
	if (isset($extraCSS))
	{
		echo "\t\t<style type=\"text/css\">\n";
		echo $extraCSS;
		echo "\t\t</style>\n";
	}
?>
	</head>
	<body>
<?php
	$newsPage = "";
	$chatMenu = "";
	$ircPage = "";
	$mumblePage = "";
	$groupPage = "";
	$gamingMenu = "";
	$eventsPage = "";
	$serversPage = "";
	$projectsMenu = "";
	$overviewPage = "";
	$aboutPage = "";
	$streamPage = "";
	$castPage = "";
	$pollPage = "";
	$pollArchivePage = "";
	$adminMenu = "";
	$avatarAdminPage = ""; $adminAdminPage = ""; $pollAdminPage = ""; $twitterAdminPage = "";
	$active = " class=\"active\"";

	if (strpos($_SERVER["SCRIPT_NAME"], "news.php"))
	{
		$newsPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "irc.php"))
	{
		$chatMenu = " active";
		$ircPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "mumble.php"))
	{
		$chatMenu = " active";
		$mumblePage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "gaming.php"))
	{
		$gamingMenu = " active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "events.php"))
	{
		$gamingMenu = " active";
		$eventsPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "servers.php"))
	{
		$serversPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "stream.php"))
	{
		$gamingMenu = " active";
		$streamPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "projects.php"))
	{
		$projectsMenu = " active";
		$overviewPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "polls.php"))
	{
		$projectsMenu = " active";
		$pollPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "poll-archive.php"))
	{
		$projectsMenu = " active";
		$pollArchivePage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "poll-admin.php"))
	{
		$adminMenu = " active";
		$pollAdminPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "avatar.php"))
	{
		$adminMenu = " active";
		$avatarAdminPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "admins.php"))
	{
		$adminMenu = " active";
		$adminAdminPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "twitter.php"))
	{
		$adminMenu = " active";
		$twitterAdminPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "cast.php"))
	{
		$castPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "cast-guests.php"))
	{
		$castPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "about.php"))
	{
		$aboutPage = $active;
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "about-peeps.php"))
	{
		$aboutPage = $active;
	}

	// TODO SteamLUG logo to replace navbar-brand, maybe SVG?
?>
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/"><img src="/images/steamlug.svg" alt="SteamLUG" id="steamLugLogo"/></a>
			</div>
			<div class="navbar-collapse collapse navbar-responsive-collapse" id="navbar">
				<ul class="nav navbar-nav">
					<li<?= $newsPage; ?>><a href="/news">News</a></li>
					<li class="dropdown<?= $chatMenu; ?>">
						<a href="/irc" class="dropdown-toggle" data-toggle="dropdown">Chat <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?= $ircPage; ?>><a href="/irc">IRC (text)</a></li>
							<li<?= $mumblePage; ?>><a href="/mumble">Mumble (voice)</a></li>
						</ul>
					</li>
					<li<?= $castPage; ?>><a href="/cast">Cast</a></li>
					<li class="dropdown<?= $gamingMenu; ?>">
						<a href="/events" class="dropdown-toggle" data-toggle="dropdown">Events <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?= $eventsPage; ?>><a href="/events">Events</a></li>
							<li<?= $streamPage; ?>><a href="/stream">Live Stream</a></li>
						</ul>
					</li>
					<li class="dropdown<?= $projectsMenu; ?>">
						<a href="/projects" class="dropdown-toggle" data-toggle="dropdown">Projects <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?= $overviewPage; ?>><a href="/projects">Overview</a></li>
							<li<?= $pollPage; ?>><a href="/polls">Polls</a></li>
						</ul>
					</li>
					<li<?= $serversPage; ?>><a href="/servers">Servers</a></li>
					<li<?= $aboutPage; ?>><a href="/about">About</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
<?php
	if ($weareadmin) {
?>
					<li class="dropdown<?= $adminMenu; ?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?= $avatarAdminPage; ?>><a href="/avatar">Avatars</a></li>
							<li<?= $pollAdminPage; ?>><a href="/poll-admin">Polls</a></li>
							<li<?= $twitterAdminPage; ?>><a href="/twitter">Twitter</a></li>
							<li<?= $adminAdminPage; ?>><a href="/admins">Admins</a></li>
							<li><a target="_blank" href="/transcriberer">Transcriberer</a></li>
							<li><a target="_blank" href="//data.steamlug.org/updatesteamlug.php">Update events</a></li>
						</ul>
					</li>
<?php 
	}
?>
					<?= $logIn; ?>
				</ul>
			</div>
		</div>
	</nav>
		<div class="container">
