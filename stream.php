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
?>
		<h1>Live Stream</h1>
		        <div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Watch us as we play</h3>
					</div>
					<div class="panel-body">
						<p>You can live follow our event as they're streamed by a SteamLUG administrator.</p>
					</div>
				</div>
		<div class="row">
			<div class="col-md-6">
		        <div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Mumble Status</h3>
					</div>
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
        $statusString = "\t\t\t\t\t<dl class=\"dl-horizontal\">\n";
        $statusString .= "\t\t\t\t\t\t<dt>Server</dt><dd>Online</dd>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Version</dt><dd>" . $info['x_gtmurmur_server_version'] . "</dd>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Channels</dt><dd>" . $statusChannels ."</dd>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Users</dt><dd>" . $statusUsers . " / " . $info['x_gtmurmur_max_users'] . "</dd>\n";
        $statusString .= "\t\t\t\t\t</dl>\n";
	echo $statusString;
?>
					<p><a href="/mumble" class="btn btn-primary">Click for details</a></p>
					</div>
				</div>
			</div>
			<div class="col-md-6">
        <div class="panel panel-default">
			<div class="panel-heading">
        		<h3 class="panel-title"><a href="<?=$data["events"][0]["url"];?>"><?=$data["events"][0]["title"];?></a></h3>
			</div>
			<div class="panel-body">
				<p class="text-center">
					<img src="//steamcdn-a.akamaihd.net/steam/apps/<?=$data["events"][1]["appid"];?>/header.jpg" alt="<?=$data["events"][0]["title"];?>" />
				</p>
				<p>
					<a href="<?=$data["events"][0]["url"];?>" class="btn btn-primary">Click for details</a>
				</p>
			</div>
			</div>
		</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><a href="https://twitch.tv/steamlug">SteamLUG @ Twitch.TV</a></h3>
			</div>
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
<?php include_once("includes/footer.php"); ?> 
