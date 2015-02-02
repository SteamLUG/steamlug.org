<?php
require_once("../steameventparser.php");

$parser = new SteamEventParser();
$eventarr = $parser->genData("steamlug");

$ical = "BEGIN:VCALENDAR\n";
$ical .= "VERSION:2.0\n";
$ical .= "PRODID:https://steamlug.org\n";


foreach ($eventarr['events'] as $event)
 {
 if($event['appid'] === 0) { continue; }
 $ical .= "
BEGIN:VEVENT\n
UID:" . md5(uniqid(mt_rand(), true)) . "@steamlug.org\n
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\n
DTSTART:" . date('Ymd', strtotime($event["date"])) . 'T' . date('His', strtotime($event["time"])) . "Z\n
DTEND:" . date('Ymd', strtotime($event["date"])) . 'T' . date('His', strtotime($event["time"])+7200) . "Z\n
SUMMARY:". $event["title"] . "\n
END:VEVENT\n";
 }

$ical .= "END:VCALENDAR\n";

header('Content-type: text/calendar; charset=utf-8');
header("Content-Disposition: inline; filename=events.ics" );
echo $ical;
exit;

?>
