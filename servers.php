<?php
$pageTitle = "Servers";
$syncexternalJS = array('http://steamlug.org/scripts/jquery.min.js','http://steamlug.org/scripts/jquery.tablesorter.js');
?>
<?php
	include_once("includes/header.php");
	include_once("includes/SourceQuery/SourceQuery.class.php");
	// 10 second cache
	header("Cache-Control: public, max-age=10");
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
				<p>If you would like to host a SteamLUG server, or help manage our existing ones,<br>please contact <a href = 'http://steamcommunity.com/id/swordfischer'>swordfischer</a>.</p>
			</div>
		</article>
		<article>
			<div class='shadow'>
				<table id='servers' class='tablesorter' cellspacing=0>
					<thead>
						<tr>
							<th>
							<th><img src='http://steamlug.org/images/vac.png'>
							<th>Game
							<th>Servers
							<th>Players
							<th>Map
							<th>
						</tr>
					</thead>
					<tbody>
<?php
define( 'SQ_TIMEOUT', 2 );
define( 'SQ_ENGINE', SourceQuery :: SOURCE );

$Servers = file( "/var/www/cenobite.swordfischer.com/servers2.txt" );

foreach ( $Servers as $Server )
	{
		list ( $ServerHost[], $Ports[] ) = preg_split ( "/:/", $Server );
	}

$Query = new SourceQuery( );
foreach ( $ServerHost as $Index => $Host)
	{
		$Query->Connect( $Host, $Ports[$Index], SQ_TIMEOUT, SQ_ENGINE );
		$Info = $Query->GetInfo( );

		$serverString = "";

		if (!isset($Info["GamePort"]))
		{
		$serverString .= "\t\t<tr>\n";
		$serverString .= "\t\t\t<td>\n";
		$serverString .= "\t\t\t<td>\n";
		$serverString .= "\t\t\t<td><em>Server Unresponsive</em>\n";
		$serverString .= "\t\t\t<td><em>" . $Host . ":" . $Ports[$Index] . "</em>\n";
		$serverString .= "\t\t\t<td><em>N/A</em>\n";
		$serverString .= "\t\t\t<td><em>N/A</em>\n";
		$serverString .= "\t\t\t<td><span class='offline'>Offline</span>\n";
		$serverString .= "\t\t<tr>\n";
		}
		else
		{
		$serverString .= "\t\t<tr>\n";
		$serverString .= "\t\t\t<td><span style='display:none'>" . geoip_country_code_by_name($Host) . "</span><img src='http://steamlug.org/images/" . geoip_country_code_by_name($Host) . ".png'>\n";
		$serverString .= "\t\t\t<td>" . ($Info["Secure"] ? "<img src='http://steamlug.org/images/vac.png'>" : "") . "\n";
		$serverString .= "\t\t\t<td>" . $Info["ModDesc"] . "\n";
		$serverString .= "\t\t\t<td><a href='steam://connect/" . $Host . ":" . $Info["GamePort"] . "'>" . $Info["HostName"] . "</a>\n";
		$serverString .= "\t\t\t<td>" . $Info["Players"] . "\n";
		$serverString .= "\t\t\t<td>" . $Info["Map"] . "\n";
		$serverString .= "\t\t\t<td><span class='online'>Online</span>\n";
		$serverString .= "\t\t<tr>\n";
		}
		echo $serverString;
	}
?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan=8>
						</tr>
					</tfoot>
				</table>
			</div>
		</article>
	</section>
	<script>
		$(document).ready
		(
			function()
			{
				$("#servers").tablesorter
				(
					{
						headers: {
							1: { sorter: false }
						},
						sortList: [[6,1],[4,1],[0,0],[3,0]]
					}
				);
			}
		);
	</script>
<?php include_once("includes/footer.php"); ?>
