<!DOCTYPE html>
<?php
// caching (60 seconds)
header("Cache-Control: public, max-age=60");
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

include_once('session.php');
include_once("functions_poll.php");
if(!login_check())
{
	$steam_login_verify = SteamSignIn::validate();
	if (!empty($steam_login_verify))
	{
		login($steam_login_verify);
		header("Location: " . $_SERVER['PHP_SELF']);
	}
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name = 'description' content = "<?php echo $description; ?>" />
		<meta name = 'keywords' content = "<?php echo $keywords; ?>" />
<?php
	if (!isset($rssLinks))
	{
		echo "\t\t<link rel='alternate' type='application/rss+xml' title='RSS' href='http://steamcommunity.com/groups/steamlug/rss/' />\n";
	}
	else
	{
		echo $rssLinks . "\n";
	}
?>
		<title>SteamLUG <?php echo $pageTitle; ?></title>
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="/css/style.css" type="text/css" />
		<script type="text/javascript" src="/js/jquery-2.1.1.min.js"></script>
		<!-- START Bootstrap !-->
		<link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="/css/bootstrap.slate.css" type="text/css" />
		<script type="text/javascript" src="/js/bootstrap.min.js"></script>
		<!-- END Bootstrap !-->
		
		<script>
			var serverTime = <?php echo microtime(true); ?>;
		</script>
<?php
	if (isset($externalJS))
	{
		foreach ($externalJS as $js)
		{
			echo "\t\t<script type = 'text/javascript' src = '" . $js . "' async = 'async'></script>\n";
		}
	}

	if (isset($syncexternalJS))
	{
		foreach ($syncexternalJS as $js)
		{
			echo "\t\t<script type = 'text/javascript' src = '" . $js . "'></script>\n";
		}
	}

	if (isset($extraJS))
	{
		echo "\t\t<script type = 'text/javascript'>\n";
		echo $extraJS;
		echo "\t\t</script>\n";
	}
	if (isset($extraCSS))
	{
		echo "\t\t<style type = 'text/css'>\n";
		echo $extraCSS;
		echo "\t\t</style>\n";
	}
?>
	</head>
	<body>
<?php
	$isBeta = true;

	$newsPage = "";
	$chatPage = "";
	$ircPage = "";
	$mumblePage = "";
	$groupPage = "";
	$gamingPage = "";
	$eventsPage = "";
	$serversPage = "";
	$projectsPage = "";
	$overviewPage = "";
	$aboutPage = "";
	$streamPage = "";
	$castPage = "";
	$pollPage = "";
	$pollArchivePage = "";

	if (strpos($_SERVER["SCRIPT_NAME"], "news.php"))
	{
		$newsPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "irc.php"))
	{
		$chatPage = "active"; //parent nav item
		$ircPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "mumble.php"))
	{
		$chatPage = "active"; //parent nav item
		$mumblePage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "gaming.php"))
	{
		$gamingPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "events.php"))
	{
		$gamingPage = "active"; //parent nav item
		$eventsPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "servers.php"))
	{
		$serversPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "stream.php"))
	{
		$gamingPage = "active"; //parent nav item
		$streamPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "projects.php"))
	{
		$projectsPage = "active";
		$overviewPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "polls.php"))
	{
		$projectsPage = "current";
		$pollPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "poll-archive.php"))
	{
		$projectsPage = "current";
		$pollArchivePage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "cast.php"))
	{
		$castPage = "active";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "about.php"))
	{
		$aboutPage = "active";
	}
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
					<li class="<?php echo $newsPage; ?>"><a href="/news">News</a></li>
					<li class="dropdown <?php echo $chatPage; ?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Chat <b class="caret"></b></a>
						<ul class="dropdown-menu <?php echo $chatPage; ?>">
							<li class="<?php echo $ircPage; ?>"><a href="/irc">IRC (text)</a></li>
							<li class="<?php echo $mumblePage; ?>"><a href="/mumble">Mumble (voice)</a></li>
						</ul>
					</li>
					<li class="<?php echo $castPage; ?>"><a href="/cast">SteamLUG Cast</a></li>
					<li class="dropdown <?php echo $gamingPage; ?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Events <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li class="<?php echo $eventsPage; ?>"><a href="/events">Events</a></li>
							<li class="<?php echo $streamPage; ?>"><a href="/stream">Live Stream</a></li>
						</ul>
					</li>
					<li class="dropdown <?php echo $projectsPage; ?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Projects <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li class="<?php echo $overviewPage; ?>"><a href="/projects">Overview</a></li>
							<li class="<?php echo $pollPage; ?>"><a href="/polls">Polls</a><li>
						</ul>
					</li>
					<li class="<?php echo $serversPage; ?>"><a href="/servers">Servers</a></li>
					<li class="<?php echo $aboutPage; ?>"><a href="/about">About</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right hidden-sm hidden-xs">
						<li class="navbar-brand"><span class="label label-success"><a href="http://steamcommunity.com/groups/steamlug/">Join our Steam Group</a></span></li>
					</ul>
				</div>
		</div>
	</nav>
		<div class="container">
