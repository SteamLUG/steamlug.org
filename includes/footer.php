		</div>
		<div class="navbar-default navbar-bottom">
			<div class="container">
				<div class="row">
					<div class="list-group col-md-3">
						<h3>Feeds</h3>
						<a class="list-group-item" href = 'http://steamcommunity.com/groups/steamlug/rss/'>SteamLUG News Feed</a>
						<a class="list-group-item" href = '/feed/events'>SteamLUG Events Feed</a>
						<a class="list-group-item" href = 'https://twitter.com/steamlug'>SteamLUG Twitter</a>
					</div>
					<div class="list-group col-md-3">
						<h3>SteamLUG</h3>
						<a class="list-group-item" href = 'http://steamcommunity.com/groups/steamlug/'>SteamLUG Steam Group</a>
						<a class="list-group-item" href = '/irc'>SteamLUG IRC Chat</a>
						<a class="list-group-item" href = '/mumble'>SteamLUG Mumble Server</a>
						<a class="list-group-item" href = 'http://forums.steampowered.com/forums/showthread.php?t=1897204'>SPUF Thread</a>
					</div>
					<div class="list-group col-md-3">
						<h3>Valve</h3>
						<a class="list-group-item" href = 'http://store.steampowered.com/linux'>Steam For Linux Store Page</a>
						<a class="list-group-item" href = 'https://github.com/ValveSoftware/steam-for-linux/'>Steam For Linux on GitHub</a>
						<a class="list-group-item" href = 'http://steamcommunity.com/app/221410'>Steam For Linux Hub</a>
						<a class="list-group-item" href = 'http://blogs.valvesoftware.com/linux/'>Valve Linux Blog</a>
					</div>
					<div class="list-group col-md-3">
						<h3>Community</h3>
						<a class="list-group-item" href = 'http://www.reddit.com/r/linux_gaming/'>Linux_Gaming Subreddit</a>
						<a class="list-group-item" href = 'http://steamdb.info/'>SteamDB.info</a>
						<a class="list-group-item" href = 'http://steamdb.info/linux/'>The Big List of Steam Games on Linux</a>
						<a class="list-group-item" href = 'http://gamingonlinux.com/'>Gaming On Linux</a>
					</div>
				</div>
				<p class="muted credit text-center">This site is not affiliated with or endorsed by Valve, Steam, or any of their partners.<br />
All registered trademarks or copyrights are property of their respective owners.</p>
			</div>
		</div>
	</body>
<?php
if (isset($tailJS)) {
	foreach ($tailJS as $js) {
		echo "\t<script src='" . htmlspecialchars($js) . "' type='text/javascript'></script>\n";
	}
}
?>
</html>
