<?php
	$pageTitle = "Streams";
	include_once('includes/header.php');
    include_once('includes/MurmurQuery.php');
	require_once('rbt_prs.php');
	require_once('steameventparser.php');

        header("Cache-Control: public, max-age=10");

        $settings               =       array
        (
                'host'          =>      '130.226.217.214',
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
        <header>
            <hgroup>
                <h1>Live Stream</h1>
            </hgroup>
        </header>
<section>
        <article class="streambox">
		<div class="shadow">
<?php

        $eventString = "\t\t\t\t<h2><a href='" . $data["events"][0]["url"] . "'>" .  $data["events"][0]["title"] . "</a></h2>";
        $eventString .= "\t\t\t\t\t<img src='http://cdn.steampowered.com/v/gfx/apps/" . $data["events"][0]["appid"] . "/header.jpg' alt='" . $data["events"][0]["title"] . "'/>\n";
        $eventString .= "\t\t\t\t</a>\n";
        $eventString .= "\t\t\t\t<h3 class = 'detailLink'><a href='" . $data["events"][0]["url"] . "'>Click for details</a></h3><p></p>\n";
        echo $eventString;
?>
	</article>
        <article>
            <div class="shadow">
		<h1 class='streambox'><a href="https://swordfischer.com">swordfischer</a> @ Twitch.TV</h1>
		<object type="application/x-shockwave-flash" width="640px" height="360px" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=swordfischer">
		<param name="allowFullScreen" value="true" />
		<param name="allowScriptAccess" value="always" />
		<param name="allowNetworking" value="all" />
		<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
		<param name="flashvars" value="hostname=www.twitch.tv&channel=swordfischer&auto_play=true&start_volume=25" />
		</object>
	    <h3 class='streambox'><a href="http://www.twitch.tv/swordfischer">Click for larger stream</a></h3><p></p>
            </div>
	</article>
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
        $statusString  = "\t\t\t<article>\n";
        $statusString .= "\t\t\t\t<div class='shadow'>\n";
        $statusString .= "\t\t\t\t\t<h1 class='streambox'><a href='http://steamlug.org/mumble'>Mumble Status</a></h1>\n";
        $statusString .= "\t\t\t\t\t<dl>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Server</dt><dd>Online</dd>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Version</dt><dd>" . $info['x_gtmurmur_server_version'] . "</dd>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Channels</dt><dd>" . $statusChannels ."</dd>\n";
        $statusString .= "\t\t\t\t\t\t<dt>Users</dt><dd>" . $statusUsers . " / " . $info['x_gtmurmur_max_users'] . "</dd>\n";
        $statusString .= "\t\t\t\t\t</dl>\n";
	$statusString .= "\t\t\t\t\t<h3 class = 'detailLink streambox'><a href='http://steamlug.org/mumble'>Click for details</a></h3><p></p>\n";
        $statusString .= "\t\t\t\t</div>\n";
        $statusString .= "\t\t\t</article>\n";
	echo $statusString;
?>
        </article>
        </section>
<?php include_once("/var/www/steamlug.org/includes/footer.php"); ?> 
