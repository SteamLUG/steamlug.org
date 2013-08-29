<?php
	$pageTitle = "Mumble";
	include_once('includes/header.php');
	include_once('includes/MurmurQuery.php');

	// 10 second cache
	header("Cache-Control: public, max-age=10");

	$settings		=	array
	(
		'host'		=>	'130.226.217.214',
		'port'		=>	27800,
		'timeout'	=>	200,
		'format'	=>	'json'
	);

	$murmur = new MurmurQuery();
	$murmur->setup($settings);
	$murmur->query();
	
	$statusOnline = "Pending";
	$statusChannels = 0;
	$statusUsers = 0;

	$status = $murmur->get_status();
	$info = $status['original'];
?>
		<header>
				<h1>SteamLUG Mumble Server</h1>
		</header>
		<section>
			<article>
			<div class = 'shadow'>
				<h1>About</h1>
				<p>In place of in-game voice chat, we host a <a href = 'http://mumble.sourceforge.net/'>Mumble</a> voice chat server, allowing our community members to talk across servers and between games. We have configurable "channels" for events, team talk and general chat including the <a href = 'cast'>SteamLUG Cast</a>. If you have the Mumble client installed, you can join by clicking <a href="mumble://mumble.dk.steamlug.org">here</a>.</p>
				<p>You can join us on Mumble by connecting to:</p>
				<dl>
				<dt>Host</dt><dd>mumble.dk.steamlug.org</dd>
				<dt>Port</dt><dd><?=$info['x_gtmurmur_connectport'];?> (<em>default port</em>)</dd>
				<dt>Password</dt><dd><em>Ask in <a href = 'irc'>IRC</a> or see the description of one of our <a href = 'http://steamcommunity.com/groups/steamlug/events'>Steam group events</a></em></dd>
				</dl>
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
	$statusString .= "\t\t\t\t\t<h1>Status</h1>\n";
	$statusString .= "\t\t\t\t\t<dl>\n";
	$statusString .= "\t\t\t\t\t\t<dt>Server</dt><dd>Online</dd>\n";
	$statusString .= "\t\t\t\t\t\t<dt>Version</dt><dd>" . $info['x_gtmurmur_server_version'] . "</dd>\n";
	$statusString .= "\t\t\t\t\t\t<dt>Channels</dt><dd>" . $statusChannels ."</dd>\n";
	$statusString .= "\t\t\t\t\t\t<dt>Users</dt><dd>" . $statusUsers . " / " . $info['x_gtmurmur_max_users'] . "</dd>\n";
	$statusString .= "\t\t\t\t\t</dl>\n";
	$statusString .= "\t\t\t\t</div>\n";
	$statusString .= "\t\t\t</article>\n";
	$statusString .= "\t\t\t<article>\n";
	$statusString .= "\t\t\t\t<div class='shadow'>\n";
	$statusString .= "\t\t\t\t\t<h1>Channels</h1>\n";
	if(count($channels) > 0)
	{
		$statusString .= "\t\t\t\t\t<ul>\n";
		foreach($channels as $channel)
		{
			$statusString .= "\t\t\t\t\t\t<li>" . $channel['name'] . "</li>\n";
		}
		$statusString .= "\t\t\t\t\t</ul>\n";
		}
		else
		{
			$statusString .= "\t\t\t\t\t<p>There are currently no active channels.</p>\n";
		}
			$statusString .= "\t\t\t\t</div>\n";
			$statusString .= "\t\t\t</article>\n";

			$statusString .= "\t\t\t<article>\n";
			$statusString .= "\t\t\t\t<div class='shadow'>\n";
			$statusString .= "\t\t\t\t\t<h1>Online Users</h1>\n";
			if(count($users) > 0)
			{
				$statusString .= "\t\t\t\t\t<ul>\n";

				foreach($users as $user)
				{
					$statusString .= "\t\t\t\t\t\t<li>" . $user['name'] . "</li>\n";
				}
			$statusString .= "\t\t\t\t\t</ul>\n";
			}
			else
			{
				$statusString .= "<p>No users are currently online.</p>\n";
			}
			$statusString .= "\t\t\t\t</div>\n";
			$statusString .= "\t\t\t</article>\n";

		// Display the original response data
		//echo '<h1>Response</h1>';
		// echo '<pre>';
		// I AM PLAYING AROUND LOL: foreach( $info[ 'root' ][ 'channels' ] as $Channel ) { echo $Channel[ 'name' ]; }
		// print_r($status['original']);
		// echo '</pre>';
	echo $statusString;
?>
		</section>
<?php	include_once('includes/footer.php'); ?>
