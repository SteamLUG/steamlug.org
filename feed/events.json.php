<?php
require_once("../rbt_prs.php");
require_once("../steameventparser.php");
$parser = new SteamEventParser();
$data = $parser->genData("steamlug");
$d = explode("-", $data['events'][0]['date']);
$t = explode(":", $data['events'][0]['time']);
$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";

$month = gmstrftime("%m");
$year = gmstrftime("%Y");
$data2 = $parser->genData("steamlug", $month >= 12 ? 1: $month +1, $month >= 12 ? $year + 1: $year);
$data3 = $parser->genData("steamlug", $month <= 1 ? 12: $month -1, $month <= 1 ? $year -1: $year);

$data['events'] = array_merge($data['events'], $data2['events']);
$data['pastevents'] = array_merge($data['pastevents'], $data3['pastevents']);
echo json_encode($data);
?>
