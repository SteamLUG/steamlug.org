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

?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name = 'description' content = "<?php echo $description; ?>" />
		<meta name = 'keywords' content = "<?php echo $keywords; ?>" />
		<title>SteamLUG <?php echo $pageTitle; ?></title>
		<link rel="stylesheet" href="/css/style.css" type="text/css" />
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
	if ($isBeta)
	{
		echo "<img alt = 'beta ribbon' id = 'betaStamp' src = '/images/ribbon_beta2.png' />";
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
	$overviewPage = "";
	$aboutPage = "";
	$streamPage = "";
	$castPage = "";

	if (strpos($_SERVER["SCRIPT_NAME"], "news.php"))
	{
		$newsPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "irc.php"))
	{
		$chatPage = "current"; //parent nav item
		$ircPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "mumble.php"))
	{
		$chatPage = "current"; //parent nav item
		$mumblePage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "gaming.php"))
	{
		$gamingPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "events.php"))
	{
		$gamingPage = "current"; //parent nav item
		$eventsPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "servers.php"))
	{
		$serversPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "stream.php"))
	{
		$gamingPage = "current"; //parent nav item
		$streamPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "projects.php"))
	{
		$projectsPage = "current";
		$overviewPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "cast.php"))
	{
		$castPage = "current";
	}
	else if (strpos($_SERVER["SCRIPT_NAME"], "about.php"))
	{
		$aboutPage = "current";
	}
?>

		<nav>
			<ul>
				<li class = '<?php echo $newsPage; ?>'><a href = '/news'>News</a></li>
				<li class = '<?php echo $chatPage; ?>'><a href = '/irc'>Chat</a>
					<ul class = '<?php echo $chatPage; ?>'>
						<li class = '<?php echo $ircPage; ?>'><a href = '/irc'>IRC (text)</a></li>
						<li class = '<?php echo $mumblePage; ?>'><a href = '/mumble'>Mumble (voice)</a></li>
					</ul>
				</li>
				<li class = '<?php echo $castPage; ?>'><a href = '/cast'>SteamLUG Cast</a></li>
				<li class = '<?php echo $gamingPage; ?>'><a href = '/events'>Events</a>
					<ul>
						<li class = '<?php echo $eventsPage; ?>'><a href = '/events'>Events</a></li>
						<li class = '<?php echo $streamPage; ?>'><a href = '/stream'>Live Stream</a></li>
					</ul>
				</li>
				<li class = '<?php echo $serversPage; ?>'><a href = '/servers'>Servers</a></li>
				<li class = '<?php echo $projectsPage; ?>'><a href = '/projects'>Projects</a>
					<ul>
						<li class = '<?php echo $overviewPage; ?>'><a href = '/projects'>Overview</a></li>
					</ul>
				</li>
				<li class = '<?php echo $aboutPage; ?>'><a href = '/about'>About</a></li>
			</ul>
		</nav>
		
		<a id = 'groupLink' href = 'http://steamcommunity.com/groups/steamlug/'><p>Join our Steam group of over 5,000 members and take part in the community!<br /><br />Joining will also allow you to make use of upcoming website features!</p><img alt = 'Join our Steam Group!' src = 'images/group.png'></a>
