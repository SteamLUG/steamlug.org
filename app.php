<?php
$pageTitle = "App";
$appID = isset( $_GET[ 'appid' ] ) ? ($_GET[ 'appid' ] != '' ? $_GET[ 'appid' ]: '0' ) : '0';

include_once('includes/session.php');
include_once('includes/functions_steam.php');
include_once('includes/functions_apps.php');

// are we supplying data via GET? → show app?
if ( $appID == '0' and !is_numeric( $appID ) ) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$gameProfile = getApp( $appID );

if ( $gameProfile == false ) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$recentEvents = getRecentEventsForApp( $appID );
$gameImages = getAppImages ( $appID );

$pageTitle = " – ‘{$gameProfile[ 'name' ]}’ Page";
include_once('includes/header.php');

$onlinux = ($gameProfile[ 'onlinux' ]) ? '<i class="fa-linux"> Yes</i>' : '<i class=""> No</i>';

echo <<<DOCUMENT
		<h1 class="text-center">{$gameProfile[ 'name' ]}</h1>
			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">{$gameProfile[ 'name' ]}</h3>
				</header>
				<div class="panel-body">
					<img class="eventimage" src="{$gameImages[ 'header' ]}" />
					<dl class="dl-horizontal">
					<dt>Group owners</dt><dd>{$gameProfile[ 'owners' ]}</dd>
					<dt>On Linux?</dt><dd>{$onlinux}</dd>
				</div>
			</article>
DOCUMENT;

if ( !empty( $recentEvents ) ) {

echo <<<HEADER
			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">Recent Events</h3>
				</header>
				<div class="panel-body">
HEADER;
	foreach ( $recentEvents as $event ) {
echo <<<EVENT
		<a href="/events/{$event['eventid']}"><time datetime="{$event['utctime']}">{$event['utctime']}</time> {$event['title']}</a>
EVENT;
	}
echo <<<FOOTER
				</div>
			</article>
FOOTER;

}
// TODO list related clans
// TODO list related servers
// TODO Optionally? list people that love playing this at events
include_once('includes/footer.php');

