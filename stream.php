<?php
	$pageTitle = "Stream";
	include_once('includes/header.php');
    include_once('includes/MurmurQuery.php');
	require_once('rbt_prs.php');
	require_once('steameventparser.php');

        header("Cache-Control: public, max-age=10");

        $settings               =       array
        (
                'host'          =>      '130.226.217.215',
                'port'          =>      27800,
                'timeout'       =>      200,
                'format'        =>      'json'
        );
	$pagetitle="Live Stream";
        $murmur = new MurmurQuery();
        $murmur->setup($settings);
        $murmur->query();

	$pageTitle = "Live Stream";

        $statusOnline = "Pending";
        $statusChannels = 0;
        $statusUsers = 0;

        $status = $murmur->get_status();
        $info = $status['original'];
	$parser = new SteamEventParser();

	$month = gmstrftime("%m")-0; // Yuck, apparently the 0 breaks something?
	$year = gmstrftime("%Y");
	$data = $parser->genData("steamlug", $month, $year);

if ( extension_loaded('curl') ) {
	// well damn, if we don’t have curl, ignore this block
/*
http://api.hitbox.tv/user/cheeseness
{"user_name":"Cheeseness","user_cover":"\/static\/img\/channel\/cover_538dbcda57552.png","user_status":"1","user_logo":"\/static\/img\/channel\/Cheeseness_531fed2c2557e_large.jpg","user_logo_small":"\/static\/img\/channel\/Cheeseness_531fed2c2557e_small.jpg","user_is_broadcaster":true,"followers":"14","user_partner":null,"user_id":"417381","is_live":"1","live_since":"2015-02-03 13:36:44","twitter_account":null,"twitter_enabled":null}

https://api.twitch.tv/kraken/streams?channel=steamlug
{"streams":[],"_to tal":0,"_links":{"self":"https://api.twitch.tv/kraken/streams?channel=steamlug&limit=25&offset=0","next":"https://api.twitch.tv/kraken/streams?channel=steamlug&limit=25&offset=25","featured":"https://api.twitch.tv/kraken/streams/featured","summary":"https://api.twitch.tv/kraken/streams/summary","followed":"https://api.twitch.tv/kraken/streams/followed"}}
*/

	/* This should return a JSON string, or an error! */
	function curl_url( $url, $get ) {

		/* Twitch for now, hitbox later - so this will come from function call */
		$header = array( 'Accept: application/vnd.twitchtv.v3.json' );

		$curl = curl_init( );
		curl_setopt_array( $curl, array(
				CURLOPT_URL => $url . '?' . http_build_query( $get ),
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 2,
				CURLOPT_TIMEOUT => 2 )
				);
		$result = curl_exec( $curl );
		$status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		if ( ( $status == 404 ) || ( $status == 0 ) || ( $status == 503 ) ) {
			return curl_error( $curl ) . ", " .curl_errno( $curl );
		}
		curl_close( $curl );

		return $result;
	}

	$maybeOnline = curl_url( 'https://api.twitch.tv/kraken/streams?channel=steamlug', array() );
	$streamStatus = @json_decode( $maybeOnline );

}
?>
		<h1>Live Stream</h1>
				<div class="panel panel-default">
					<header class="panel-heading">
						<h3 class="panel-title">Watch us as we play</h3>
					</header>
					<div class="panel-body">
						<p>You can live follow our event as they're streamed by a SteamLUG administrator.</p>
					</div>
				</div>
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<header class="panel-heading">
						<h3 class="panel-title">Mumble Status</h3>
					</header>
					<div class="panel-body">
<?php
        $users = $murmur->get_users();
        $channels = $murmur->get_channels();

        if($murmur->is_online())
        {
                $statusOnline = "Online";
                $statusChannels = count($channels);
                $statusUsers = count($users);
        }
        else
        {
                $statusOnline = "Offline";
                $statusChannels = "N/A";
                $statusUsers = "N/A";
        }
	echo <<<STATUS
					<dl class="dl-horizontal">
						<dt>Server</dt><dd>Online</dd>
						<dt>Version</dt><dd>{$info['x_gtmurmur_server_version']}</dd>
						<dt>Channels</dt><dd>{$statusChannels}</dd>
						<dt>Users</dt><dd>{$statusUsers} ⁄ {$info['x_gtmurmur_max_users']}</dd>
					</dl>
STATUS;
?>
					<p><a href="/mumble" class="btn btn-primary">Click for details</a></p>
					</div>
				</div>
			</div>
			<div class="col-md-6">
        <div class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title"><a href="<?=$data["events"][0]["url"];?>"><?=$data["events"][0]["title"];?></a></h3>
			</header>
			<div class="panel-body">
				<p class="text-center">
					<img src="<?=$data["events"][0]["img_header"];?>" alt="<?=$data["events"][0]["title"];?>" />
				</p>
				<p>
					<a href="<?=$data["events"][0]["url"];?>" class="btn btn-primary">Click for details</a>
				</p>
			</div>
			</div>
		</div>
		</div>
        <div class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title"><a href="https://twitch.tv/steamlug">SteamLUG @ Twitch.TV</a></h3>
			</header>
			<div class="panel-body">
				<object type="application/x-shockwave-flash" width="100%" height="600px" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=steamlug">
					<param name="allowFullScreen" value="true" />
					<param name="allowScriptAccess" value="always" />
					<param name="allowNetworking" value="all" />
					<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
					<param name="flashvars" value="hostname=www.twitch.tv&channel=steamlug&auto_play=true&start_volume=25" />
				</object>
				<p><a href="http://www.twitch.tv/steamlug" class="btn btn-primary">Click for larger stream</a></p>
			</div>
		</div>
<?php include_once('includes/footer.php'); ?>
