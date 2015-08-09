<?php
	$pageTitle = "Servers";
	// 60 second cache
	ini_set('zlib.output_compression', 0);
	ini_set('implicit_flush', 1);
	$tailJS = array( '/scripts/jquery.tablesorter.min.js' );
	include_once('includes/header.php');
	include_once('includes/GameQ.php');
	include_once('includes/paths.php');
	$Servers = file( $serversList );

?>
		<h1 class="text-center">Game Servers</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">About</h3>
			</header>
			<div class="panel-body">
				<p>Below you can find a list of our currently active game servers. Where possible, live information for the current map, number of players, etc. will be shown.</p>
				<p>If you would like to host a server for SteamLUG, or help manage our existing ones,<br>please contact <a href = 'http://twitter.com/steamlug'>@steamlug</a>.</p>
			</div>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Servers</h3>
			</header>
			<div class="panel-body panel-body-table">
				<table id="servers" class="table table-striped table-hover tablesorter">
					<thead>
						<tr>
							<th><i class="fa-globe"></i>
							<th><i class="fa-shield"></i>
							<th><i class="fa-lock"></i>
							<th>Game
							<th>Servers
							<th>Players
							<th>Map
							<th><i class="fa-circle"></i>
						</tr>
					</thead>
					<tbody>
<?php
	flush(); /* visitor should get better indication that the page is actually loading now */

	foreach ( $Servers as $Server )
	{
		if ( strlen( $Server ) > 11 and strrpos($Server, '#', -strlen($Server)) === False ) {
			list ( $ServerHost[], $Ports[], $GameType[] ) = preg_split ( "/(:|,)/", $Server );
		}
	}
	$gq = new GameQ();
	foreach ( $ServerHost as $Index => $Host)
	{
		$gq->addServer(array(
			'type' => trim($GameType[$Index]),
			'host' => trim($Host) . ":" . trim($Ports[$Index]),
			));
	}

	$results = $gq->setOption('timeout', 1)
				->setFilter('normalise')
				->requestData();

	foreach ( $results as $id => $data )
	{
		if (!$data['gq_online'])
		{
			echo <<<SERVERSTRING
			<tr class="unresponsive">
				<td></td>
				<td></td>
				<td></td>
				<td><em>Server Unresponsive</em></td>
				<td><em>{$data['gq_address']}:{$data['gq_port']}</em></td>
				<td><em>0 ⁄ 0</em></td>
				<td><em>N/A</em></td>
				<td><i class="text-danger fa-circle-o"></i></span></td>
			</tr>
SERVERSTRING;
		} else {
			/* this block of code should be better… TODO it please */
			$serverLoc	= geoip_country_code_by_name($data['gq_address']);
			$serverSec	= !empty($data['secure']) ? '<i class="fa-shield"></i>' : '';
			$serverPass	= !empty($data['gq_password']) ? '<i class="fa-lock"></i>' : '';
			$serverDesc	= !empty($data['gq_name']) ? $data['gq_name'] : '';
			$serverDesc	= !empty($data['gq_steamappid']) ? '<a href="/app/' . $data['gq_steamappid'] . '">' . $data['gq_name'] . '</a>' : $data['gq_name'];
			$serverNum	= (!empty($data['gq_numplayers']) ? $data['gq_numplayers'] : '0') . ' ⁄ ' . $data['gq_maxplayers'];
			$serverMap	= substr( $data['gq_mapname'], 0, 18 );
			$connectPort	= (!empty($data['port']) ? $data['port'] : (isset($data['gameport']) ? $data['gameport'] : $data['gq_port']));
			$serverHost	= $data['gq_address'] . ":" . $connectPort;
			echo <<<SERVERSTRING
			<tr>
				<td><img src="/images/flags/{$serverLoc}.png" title="Hosted in {$serverLoc}" alt="{$serverLoc}" /></td>
				<td>{$serverSec}</td>
				<td>{$serverPass}</td>
				<td>{$serverDesc}</td>
				<td><a href="steam://connect/{$serverHost}">{$data['gq_hostname']}</a>
				<td>{$serverNum}</td>
				<td>{$serverMap}</td>
				<td><i class="text-success fa-circle"></i></td>
			</tr>
SERVERSTRING;
		}
	}
?>
					</tbody>
				</table>
			</div>
		</article>
<?php
$onload = <<<CALLTHESEPLS
$(document).ready(
	$(function() {
		$("#servers").tablesorter({
			theme : "bootstrap",
			headerTemplate : '{content} {icon}',
			headers: {
				1: { sorter: false, parser: false },
				2: { sorter: false, parser: false },
			},
			sortList: [[7,1],[5,1],[4,0],[0,0]],
			cssIconAsc: 'fa-sort-up',
			cssIconDesc: 'fa-sort-down',
			cssIconNone: 'fa-unsorted'
		});
	})
);
CALLTHESEPLS;
$tailScripts = array( $onload );
include_once('includes/footer.php');
