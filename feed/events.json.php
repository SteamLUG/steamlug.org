<?php
require_once("../rbt_prs.php");
require_once("../steameventparser.php");
$parser = new SteamEventParser();
$data = $parser->genData("steamlug");
$month = gmstrftime("%m")-0; // Yuck, apparently the 0 breaks something?
$year = gmstrftime("%Y");
$data = $parser->genData("steamlug", $month, $year);

$data2 = $parser->genData("steamlug", ( $month >= 12 ? 1 : ( $month +1 ) ), ( $month >= 12 ? ( $year + 1 ) : $year ));
$data3 = $parser->genData("steamlug", ( $month <= 1 ? 12 : ( $month -1 ) ), ( $month <= 1 ? ( $year -1 ) : $year ));

$data['events'] = array_merge($data['events'], $data2['events']);
$data['pastevents'] = array_merge($data['pastevents'], $data3['pastevents']);

$d = explode("-", $data['events'][0]['date']);
$t = explode(":", $data['events'][0]['time']);
echo json_encode($data);
?>
