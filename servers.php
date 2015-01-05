<?php
$pageTitle = "Servers";
$syncexternalJS = array('/scripts/jquery.js','/scripts/jquery.tablesorter.js','/scripts/jquery.tablesorter.widgets.js');
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
		if (!$data['gq_online'])
		{
			echo <<<SERVERSTRING
			<tr class="unresponsive">
				<td></td>
				<td></td>
				<td></td>
				<td><em>Server Unresponsive</em></td>
				<td><em>{$serverHost}</em></td>
				<td><em>N/A</em></td>
				<td><em>N/A</em></td>
				<td><span class="text-danger"><i class="fa fa-circle-o"></i></span></td>
			</tr>
SERVERSTRING;
		}
		else
		{
			/* this block of code should be better… TODO it please */
			$serverLoc	= geoip_country_code_by_name($data['gq_address']);
			$serverSec	= isset($data['secure']) ? '<i class="fa fa-shield"></i>' : "";
			$serverPass	= $data['gq_password'] == "1" ? '<i class="fa fa-shield"></i>' : "";
			$serverDesc	= isset($data['game_descr']) ? ($data['game_descr'] == "Team Fortress" ? "Team Fortress 2" : $data['game_descr']) : ($data['gq_type'] == "killingfloor" ? "Killing Floor" : $data['gq_type']);
			$serverNum	= ($data['gq_numplayers'] ? $data['gq_numplayers'] : "0") . " / " . $data['gq_maxplayers'];
			echo <<<SERVERSTRING
			<tr>
				<td><span style="display:none">{$serverLoc}</span><img src="/images/flags/{$serverLoc}.png" alt="Hosted in {$serverLoc}"></td>
				<td>{$serverSec}</td>
				<td>{$serverPass}</td>
				<td>{$serverDesc}</td>
				<td><a href="steam://connect/{$serverHost}">{$data['gq_hostname']}</a>
				<td>{$serverNum}</td>
				<td>{$data['gq_mapname']}</td>
				<td><span class="text-success"><i class="fa fa-circle"></i></span></td>
			</tr>
SERVERSTRING;
		}
	}
?>
		<h1 class="text-center">SteamLUG Game Servers</h1>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">About</h3>
			</div>
			<div class="panel-body">
				<p>Below you can find a list of our currently active game servers. Where possible, live information for the current map, number of players, etc. will be shown.</p>
				<p>If you would like to host a SteamLUG server, or help manage our existing ones,<br>please contact <a href = 'http://steamcommunity.com/id/swordfischer'>swordfischer</a>.</p>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Servers</h3>
			</div>
			<div class="panel-body">
				<table id="servers" class="table table-striped table-hover tablesorter">
					<thead>
						<tr>
							<th>
							<th><i class="fa fa-shield"></i>
							<th><i class="fa fa-lock"></i>
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
				</table>
			</div>
		</div>
<script>
		$(document).ready
		(
$(function() {

  $.extend($.tablesorter.themes.bootstrap, {
	table		: '',
    caption		: 'caption',
    header		: 'bootstrap-header',	// give the header a gradient background
    sortNone	: 'fa fa-unsorted',
    sortAsc		: 'fa fa-sort-up',		// includes classes for Bootstrap v2 & v3
    sortDesc	: 'fa fa-sort-down',	// includes classes for Bootstrap v2 & v3
  });
  $("#servers").tablesorter({
    theme : "bootstrap",
    headerTemplate : '{content} {icon}',
    widgets : [ "uitheme" ],
	headers: {
		1: { sorter: false },
		2: { sorter: false },
	},
	sortList: [[7,1],[5,1],[0,0],[4,0]]
  })
}));
</script>
<?php include_once("includes/footer.php"); ?>
