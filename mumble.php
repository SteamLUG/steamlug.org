<?php
	$pageTitle = "Mumble";
	include_once('includes/header.php');
	include_once('includes/MurmurQuery.php');

	// 10 second cache
	header("Cache-Control: public, max-age=10");

	$settings		=	array
	(
		'host'		=>	'mumble.dk.steamlug.org',
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
	$statusString = "";
	$status = $murmur->get_status();
	$info = $status['original'];

	function Users($array)
	{
	global $statusString;
		foreach ($array as $value)
		{
		$statusString .= "\n<ul>\n";
			if (is_array($value) && count($value) > 0 && isset($value['name']))
			{
					if (isset($value['users']))
					{
						$statusString .= "<li class=\"channel\"><i class=\"fa fa-group text-warning\"></i> " . $value['name'];
					}
					else
					{
						$statusString .= "<li class=\"user\"><i class=\"fa fa-user text-info\"></i> " . $value['name'];
					}

					if ( isset($value['users']) && is_array($value['users']) && count($value['users']) > 0 && isset($value['name']))
					{
						Users($value['users']);
					}
					if (isset($value['channels']))
					{
						Users($value['channels']);
					}
						$statusString .= "</li>\n";
			}
		$statusString .= "</ul>\n";
		}
	}
?>
	<h1 class="text-center">SteamLUG Mumble Server</h1>
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">About</h3>
		</header>
		<div class="panel-body">
			<div class="col-md-7">
				<p>In place of in-game voice chat, we host a <a href = 'http://mumble.sourceforge.net/'>Mumble</a> voice chat server, allowing our community members to talk across servers and between games. We have configurable "channels" for events, team talk and general chat including the <a href = 'cast'>SteamLUG Cast</a>. If you have the Mumble client installed, you can join by clicking <a href="mumble://mumble.dk.steamlug.org">here</a>.</p>
				<p>You can join us on Mumble by connecting to:</p>
				<dl class="dl-horizontal">
				<dt>Host</dt><dd>mumble.dk.steamlug.org</dd>
				<dt>Port</dt><dd><?=$info['x_gtmurmur_connectport'];?> (<em>default port</em>)</dd>
				</dl>
			</div>
			<div class="col-md-5">
<?php
	$users = $murmur->get_users();
	$rootChannels = $info['root']['channels'];

	if($murmur->is_online())
	{
		$statusOnline = "Online";
		$statusChannels = count($murmur->get_channels());
		$statusUsers = count($users);
	}
	else
	{
		$statusOnline = "Offline";
		$statusChannels = "N/A";
		$statusUsers = "N/A";
	}
	Users($rootChannels);
?>
			<dl class="dl-horizontal">
				<dt>Server</dt><dd>Online</dd>
				<dt>Version</dt><dd><?=$info['x_gtmurmur_server_version'];?></dd>
				<dt>Channels</dt><dd><?=$statusChannels;?></dd>
				<dt>Users</dt><dd><?=$statusUsers;?> / <?=$info['x_gtmurmur_max_users'];?></dd>
			</dl>
			</div>
		</div>
	</article>
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">Status</h3>
		</header>
		<div class="panel-body" id="mumble-list">
<?php
	echo $statusString;

?>
		</div>
	</article>
<?php	include_once('includes/footer.php'); ?>
