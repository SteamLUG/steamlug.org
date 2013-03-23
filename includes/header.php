<!DOCTYPE html>
<?php

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

?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name = 'description' content = "<?php echo $description; ?>" />
		<meta name = 'keywords' content = "<?php echo $keywords; ?>" />
		<title>SteamLUG <?php echo $pageTitle; ?></title>
		<link rel="stylesheet" href="css/style.css" type="text/css" />
		<script src="http://twitterjs.googlecode.com/svn/trunk/src/twitter.min.js" type="text/javascript"></script>

		<script>
			var serverTime = <?php echo microtime(true); ?>;
		</script>
		<!--<script type = "text/javascript" src="http://dfgc.jbushproductions.com/microtime2.php" async="async"></script>!-->
<?php
	if (isset($extraJS))
	{
		echo "\t\t<script type = 'text/javascript'>\n";
		echo $extraJS;
		echo "\t\t</script>\n";
	}
	if (isset($externalJS))
	{
		foreach ($externalJS as $js)
		{
			echo "\t\t<script type = 'text/javascript' src = '" . $js . "' async = 'async'></script>\n";
		}
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
	if ($isBeta)
	{
		echo "<img alt = 'beta ribbon' id = 'betaStamp' src = 'images/ribbon_beta2.png'>";
	}
	
	
	
	$newsPage = "";
	$chatPage = "";
	$ircPage = "";
	$mumblePage = "";
	$groupPage = "";
	$gamingPage = "";
	$eventsPage = "";
	$serversPage = "";
	$projectsPage = "";
	$aboutPage = "";

	if (strpos($_SERVER["SCRIPT_NAME"], "news.php"))
	{
		$newsPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "irc.php"))
	{
		$chatPage = "current";
		$ircPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "mumble.php"))
	{
		$chatPage = "current";
		$mumblePage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "group.php"))
	{
		$groupPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "gaming.php"))
	{
		$gamingPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "events.php"))
	{
		$gamingPage = "current";
		$eventsPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "servers.php"))
	{
		$gamingPage = "current";
		$serversPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "projects.php"))
	{
		$projectsPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "about.php"))
	{
		$aboutPage = "current";
	}
?>
		<nav>
			<ul>
				<li class = '<?php echo $newsPage; ?>'><a href = 'news'>News</a></li>
				<li class = '<?php echo $chatPage; ?>'><a href = 'irc'>Chat</a><ul class = '<?php echo $chatPage; ?>'><li class = '<?php echo $ircPage; ?>'><a href = 'irc'>IRC (text)</a></li><li class = '<?php echo $mumblePage; ?>'><a href = 'mumble'>Mumble (voice)</a></li></ul></li>
				<li class = '<?php echo $groupPage; ?>'><a href = 'http://steamcommunity.com/groups/steamlug/'>Group</a></li>
				<li class = '<?php echo $gamingPage; ?>'><a href = 'events'>Gaming</a><ul><li class = '<?php echo $eventsPage; ?>'><a href = 'events'>Events</a></li><li class = '<?php echo $serversPage; ?>'><a href = 'servers'>Servers</a></li></ul></li>
				<li class = '<?php echo $projectsPage; ?>'><a href = 'projects'>Projects</a></li>
				<li class = '<?php echo $aboutPage; ?>'><a href = 'about'>About</a></li>
			</ul>
		</nav>
