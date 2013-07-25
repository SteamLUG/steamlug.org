<?php
$pageTitle = "Servers";
$syncexternalJS = array('http://steamlug.org/scripts/jquery.min.js','http://steamlug.org/scripts/jquery.tablesorter.js');
?>
<?php
	include_once("includes/header.php");
	include_once("includes/GameQ.php");
	// 10 second cache
	header("Cache-Control: public, max-age=10");
	$Servers = file( "/var/www/dev.steamlug.org/serverlist.txt" );

	foreach ( $Servers as $Server )
	{
		list ( $ServerHost[], $Ports[], $GameType[] ) = preg_split ( "/(:|,)/", $Server );
	}

	$gq = new GameQ();
	foreach ( $ServerHost as $Index => $Host)
	{
		$gq->addServer(array(
			'type' => trim($GameType[$Index]),
			'host' => trim($Host) . ":" . trim($Ports[$Index]),
	));
	}

	$gq->setOption('timeout', 1); 
	$gq->setFilter('normalise');
	$results = $gq->requestData();

	function print_results($results)
	{
		foreach ($results as $id => $data)
		{
			print_table($data);
		}

	}
	
	function print_table($data)
	 {
		$serverHost = $data['gq_address'] . ":" . $data['gq_port'];
		$serverString = "";
		if (!$data['gq_online'])
		{
			$serverString .= "\t\t<tr>\n";
			$serverString .= "\t\t\t<td>\n";
			$serverString .= "\t\t\t<td>\n";
			$serverString .= "\t\t\t<td>\n";
			$serverString .= "\t\t\t<td><em>Server Unresponsive</em>\n";
			$serverString .= "\t\t\t<td><em>N/A</em>\n";
			$serverString .= "\t\t\t<td><em>N/A</em>\n";
			$serverString .= "\t\t\t<td><em>N/A</em>\n";
			$serverString .= "\t\t\t<td><span class='offline'>Offline</span>\n";
			$serverString .= "\t\t</tr>\n";
		}
		else
		{
			$serverLoc  = geoip_country_code_by_name($data['gq_address']);
			$serverString .= "\t\t<tr>\n";
			$serverString .= "\t\t\t<td><span style='display:none'>" . $serverLoc . "</span><img src='http://steamlug.org/images/" . $serverLoc . ".png' alt='Hosted in " . $serverLoc . "'>\n";
			$serverString .= "\t\t\t<td>" . (isset($data['secure']) ? "<img src='http://steamlug.org/images/vac.png' alt='VAC Enabled'>" : "") . "\n";
			$serverString .= "\t\t\t<td>" . ($data['gq_password'] == "1" ? "<img src='images/padlock.png' alt='Password Protected'>" : "") . "\n";
			$serverString .= "\t\t\t<td>" . (isset($data['game_descr']) ? ($data['game_descr'] == "Team Fortress" ? "Team Fortress 2" : $data['game_descr']) : ($data['gq_type'] == "killingfloor" ? "Killing Floor" : $data['gq_type'])) . "\n";
			$serverString .= "\t\t\t<td><a href='steam://connect/" . $serverHost . "'>" . $data['gq_hostname'] . "</a>\n";
			$serverString .= "\t\t\t<td>" . ($data['gq_numplayers'] ? $data['gq_numplayers'] : "0") . " / " . $data['gq_maxplayers'] . "\n";
			$serverString .= "\t\t\t<td>" . $data['gq_mapname'] . "\n";
			$serverString .= "\t\t\t<td><span class='online'>Online</span>\n";
			$serverString .= "\t\t</tr>\n";
		}
	echo $serverString;
	}
?>
		<header>
				<h1>SteamLUG Game Servers</h1>
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
				<table id='servers' class='tablesorter'>
					<thead>
						<tr>
							<th>
							<th><img src='/images/vac.png' alt='VAC Enabled'>
							<th><img src='/images/padlock.png' alt='Password Protected'>
							<th>Game
							<th>Servers
							<th>Players
							<th>Map
							<th>
						</tr>
					</thead>
					<tbody>
<?php

	print_results($results);
?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan=7>
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
							1: { sorter: false },
							2: { sorter: false },
						},
						sortList: [[7,1],[5,1],[0,0],[4,0]]
					}
				);
			}
		);
	</script>
<?php include_once("includes/footer.php"); ?>
