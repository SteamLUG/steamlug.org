<?php
$pageTitle = "Servers";
$syncexternalJS = array("https://steamlug.org/scripts/jquery.min.js", "https://steamlug.org/scripts/jquery.tablesorter.js", "https://steamlug.org/scripts/gameqajax.js");
?>
<?php
	include_once("includes/header.php");
	// 10 second cache
	header("Cache-Control: public, max-age=10");
	$gameqajax = "https://steamlug.org/gameqajax.php";
	$json = json_decode(file_get_contents("./serverlist.json"), true);
	$js = "";

	function print_rows($json, $gameqajax) {
		$servers = $json["servers"];
		foreach ($servers as $server) {
			$games = $server["games"];
			$host = $server["host"];
			foreach ($games as $game) {
				print_row($host, $game);
				$GLOBALS["js"] .= "\t\t\t$('tr[id=\"" . $host . ":" . $game["port"] . "\"]').gameqajax({\n";
				$GLOBALS["js"] .= "\t\t\t\t url: '" . $gameqajax . "',\n";
				$GLOBALS["js"] .= "\t\t\t\t host: '" . $host . "',\n";
				$GLOBALS["js"] .= "\t\t\t\t port: '" . $game["port"] . "',\n";
				$GLOBALS["js"] .= "\t\t\t\t type: '" . $game["gameq"] . "'\n";
				$GLOBALS["js"] .= "\t\t\t});\n";
			}
		}
	}
	
	function print_row($host, $game) {
		$country = geoip_country_code_by_name($host);
		$row = "";
		$row .= "\t\t<tr id='" . $host . ":" . $game["port"] . "'></td>\n";
		$row .= "\t\t\t<td><span style='display:none'>" . $country . "</span><img src='/images/flags/" . $country . ".png' alt='Hosted in " . $country . "'></td>\n";
		$row .= "\t\t\t<td></td>\n";
		$row .= "\t\t\t<td></td>\n";
		$row .= "\t\t\t<td>" . $game["game"] . "</td>\n";
		$row .= "\t\t\t<td><a href='steam://connect/" . $host . ":" . $game["port"] . "'>" . $game["desc"] . "</a></td>\n";
		$row .= "\t\t\t<td></td>\n";
		$row .= "\t\t\t<td></td>\n";
		$row .= "\t\t\t<td></td>\n";
		$row .= "\t\t</tr>\n";
		echo $row;
	}
?>
		<header>
			<h1>SteamLUG Game Servers</h1>
		</header>
		<section>
		
		<article id="about">
			<div class="shadow">
				<h1>About</h1>
				<p>Below you can find a list of our currently active game servers. Where possible, live information for the current map, number of players, etc. will be shown.</p>
				<p>If you would like to host a SteamLUG server, or help manage our existing ones,<br>please contact <a href="http://steamcommunity.com/id/swordfischer">swordfischer</a>.</p>
			</div>
		</article>
		<article>
			<noscript><div class="shadow"><h2>To see the status and availability of our servers, please enable Javascript</h2></div></noscript>
			<div class="shadow">
				<table id="servers" class="tablesorter">
					<thead>
						<tr>
							<th></th>
							<th><img src="/images/vac.png" alt="VAC Enabled"/></th>
							<th><img src="/images/padlock.png" alt="Password Protected"/></th>
							<th>Game</th>
							<th>Servers</th>
							<th>Players</th>
							<th>Map</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php
					print_rows($json, $gameqajax);
?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="7"></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</article>
	</section>
	<script>
		$(document).ready(function() {
			$("#servers").tablesorter ({
				headers: {
					1: { sorter: false },
					2: { sorter: false },
				},
				sortList: [[7,1],[5,1],[0,0],[4,0]]
			});
<?php
			echo $GLOBALS["js"];
?>
		});
	</script>
<?php include_once("includes/footer.php"); ?>
