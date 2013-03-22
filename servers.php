<?php

$pageTitle = "Servers";
?>
<?php
	include_once("includes/header.php");
	include_once("includes/SourceQuery/SourceQuery.class.php");
?>
		<header>
			<hgroup>
				<h1>SteamLUG Game Servers</h1>
			</hgroup>
		</header>
		<section>
		
		<article id='about'>
			<div class="shadow">
				<h1>About</h1>
				<p>Below you can find a list of our currently active game servers. Where possible, live information for the current map, number of players, etc. will be shown.</p>
				<p>If you would like to host a SteamLUG server, or help manage our existing ones, please see *** NEED A LINK HERE *** this discussion thread.</p>
			</div>
		</article>
<?php
define( 'SQ_TIMEOUT', 1 );
define( 'SQ_ENGINE', SourceQuery :: SOURCE );

$ServerHost = "dannebrog.steamlug.org";
$Ports = array( "27020", "27024", "27028", "27032", "27030", "27022", "27026", "27018_");

$Query = new SourceQuery( );
foreach ($Ports as $Port)
	{
		$Query->Connect( $ServerHost, $Port, SQ_TIMEOUT, SQ_ENGINE );
		$Info = $Query->GetInfo( );
		$serverString  = "\t<article>\n";
		$serverString .= "\t\t<div class = 'shadow'>\n";
		$serverString .= "\t\t\t<h1><a href='steam://connect/" . $ServerHost . ":" . $Info["GamePort"] . "'>" . $Info["HostName"] . "</a></h1>\n";
		$serverString .= "\t\t\t<a href='steam://connect/" . $ServerHost . ":" . $Info["GamePort"] . "'><img class='serverimg' src='http://cdn.steampowered.com/v/gfx/apps/" . $Info["AppID"] . "/header.jpg' alt = 'Game logo' /></a>\n";
		$serverString .= "\t\t\t<dl>\n";
		$serverString .= "\t\t\t<dt>Map</dt><dd>" . $Info["Map"] . "</dd>\n";
		$serverString .= "\t\t\t<dt>Host</dt><dd>" . $ServerHost . "</dd>\n";
		$serverString .= "\t\t\t<dt>Port</dt><dd>" . $Info["GamePort"] . "</dd>\n";
		$serverString .= "\t\t\t<dt>Players</dt><dd>" . $Info["Players"] . "</dd>\n";
		$serverString .= "\t\t\t<dt>Max Players</dt><dd>" . $Info["MaxPlayers"] . "</dd>\n";
		$serverString .= "\t\t\t<dt>Bots</dt><dd>" . $Info["Bots"] . "</dd>\n";
		$serverString .= "\t\t\t<dt>Secure</dt><dd>" . $Info["Secure"] . "</dd>\n";
		$serverString .= "\t\t\t<dt>Version</dt><dd>" . $Info["Version"] . "</dd>\n";
		$serverString .= "\t\t\t</dl>\n";
		$serverString .= "\t\t\t<p class = 'serverlink'><a href='steam://connect/" . $ServerHost . ":" . $Info["GamePort"] . "'>Click here to join</a></p>\n";
		$serverString .= "\t\t</div>\n";
		$serverString .= "\t</article>\n";
		echo $serverString;
	}
?>
		</section>
<?php include_once("includes/footer.php"); ?>
