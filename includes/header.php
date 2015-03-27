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

$weareadmin = false;
$logIn = "";
if ( !isset($skipAuth) ) {
	if(!login_check())
	{
		$steam_login_verify = SteamSignIn::validate();
		if (!empty($steam_login_verify))
		{
			login($steam_login_verify);
			// TODO this isn’t secure, fix that
			if ( array_key_exists( 'REDIRECT_URL', $_SERVER ) ) {
				header( "Location: /loggedin/?returnto=" . preg_replace('/\?.*$/', '', $_SERVER["REDIRECT_URL"]) );
			} else {
				header( "Location: /loggedin/" );
			}
			exit();
		} else {

			$steam_sign_in_url = SteamSignIn::genUrl();
			$logIn = <<<AUTHBUTTON
				<li class="steamLogin"><a href="{$steam_sign_in_url}"><img src="//steamcommunity.com/public/images/signinthroughsteam/sits_large_noborder.png" alt="Log into Steam" /></a></li>
AUTHBUTTON;
		}
	} else {
		if ( isset( $_SESSION['a'] ) and ( $_SESSION['a'] != "" ) ) {
			$logIn = <<<SHOWAVATAR
				<li class="steamLogin navbar-avatar"><a href="/logout"><img width="32" height="32" id="steamAvatar" src="{$_SESSION['a']}" /></a></li>
SHOWAVATAR;
		} else {
			$logIn = <<<SHOWAVATAR
				<li class="steamLogin navbar-avatar"><a href="/logout"><img width="32" height="32" id="steamAvatar" src="/avatars/default.png" /></a></li>
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
		<title>SteamLUG <?php echo $pageTitle; ?></title>
		<meta name="viewport" content="width=400, initial-scale=1" />
		<meta name="description" content="<?php echo $description; ?>" />
		<meta name="keywords" content="<?php echo $keywords; ?>" />
<?php
	if (!isset($rssLinks))
	{
		echo "\t\t<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS\" href=\"http://steamcommunity.com/groups/steamlug/rss/\" />\n";
	}
	else
	{
		echo $rssLinks . "\n";
	}
?>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" />
		<link rel="stylesheet" href="/css/style.css" type="text/css" />
		<link rel="stylesheet" href="/css/bootstrap.slate.css" type="text/css" />
		<script type="text/javascript" src="/scripts/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="/scripts/bootstrap.min.js"></script>
		<script type="text/javascript">
			var serverTime = <?php echo microtime(true); ?>;
		</script>
<?php
	if (isset($deferJS))
	{
		foreach ($deferJS as $js)
		{
			echo "\t\t<script type=\"text/javascript\" src=\"" . $js . "\" defer=\"defer\"></script>\n";
		}
	}

	if (isset($externalJS))
	{
		foreach ($externalJS as $js)
		{
			echo "\t\t<script type=\"text/javascript\" src=\"" . $js . "\" async=\"async\"></script>\n";
		}
	}

	if (isset($syncexternalJS))
	{
		foreach ($syncexternalJS as $js)
		{
			echo "\t\t<script type=\"text/javascript\" src=\"" . $js . "\"></script>\n";
		}
	}

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
				<a class="navbar-brand" href="/">SteamLUG</a>
			</div>
			<div class="navbar-collapse collapse navbar-responsive-collapse" id="navbar">
				<ul class="nav navbar-nav">
					<li<?php echo $newsPage; ?>><a href="/news">News</a></li>
					<li class="dropdown<?php echo $chatMenu; ?>">
						<a href="/irc" class="dropdown-toggle" data-toggle="dropdown">Chat <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?php echo $ircPage; ?>><a href="/irc">IRC (text)</a></li>
							<li<?php echo $mumblePage; ?>><a href="/mumble">Mumble (voice)</a></li>
						</ul>
					</li>
					<li<?php echo $castPage; ?>><a href="/cast">Cast</a></li>
					<li class="dropdown<?php echo $gamingMenu; ?>">
						<a href="/events" class="dropdown-toggle" data-toggle="dropdown">Events <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?php echo $eventsPage; ?>><a href="/events">Events</a></li>
							<li<?php echo $streamPage; ?>><a href="/stream">Live Stream</a></li>
						</ul>
					</li>
					<li class="dropdown<?php echo $projectsMenu; ?>">
						<a href="/projects" class="dropdown-toggle" data-toggle="dropdown">Projects <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?php echo $overviewPage; ?>><a href="/projects">Overview</a></li>
							<li<?php echo $pollPage; ?>><a href="/polls">Polls</a><li>
						</ul>
					</li>
					<li<?php echo $serversPage; ?>><a href="/servers">Servers</a></li>
<?php
	if ($weareadmin) {
?>
					<li class="dropdown<?php echo $adminMenu; ?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?php echo $avatarAdminPage; ?>><a href="/avatar">Avatars</a></li>
							<li<?php echo $pollAdminPage; ?>><a href="/poll-admin">Polls</a><li>
							<li<?php echo $twitterAdminPage; ?>><a href="/twitter">Twitter</a><li>
							<li<?php echo $adminAdminPage; ?>><a href="/admins">Admins</a></li>
							<li><a target="_blank" href="/transcriberer">Transcriberer</a><li>
							<li><a target="_blank" href="//data.steamlug.org/updatesteamlug.php">Update events</a><li>
							<li<?php echo $aboutPage; ?>><a href="/about">About</a></li>
						</ul>
					</li>
<?php
	} else {
?>
					<li<?php echo $aboutPage; ?>><a href="/about">About</a></li>
<?php } ?>
					<?php echo $logIn; ?>
					</ul>
				</div>
		</div>
	</nav>
		<div class="container">
