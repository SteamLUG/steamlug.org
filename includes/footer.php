		</div>
		<footer class="navbar-default navbar-bottom">
			<div class="container">
				<div class="row">
					<div class="col-xs-3">
						<h3>Feeds</h3>
							<ul class="list-unstyled">
								<li><a href="http://steamcommunity.com/groups/steamlug/rss/">SteamLUG News Feed</a></li>
								<li><a href="/feed/events">SteamLUG Events Feed</a></li>
								<li><a href="https://twitter.com/steamlug">SteamLUG Twitter</a></li>
							</ul>
					</div>
					<div class="col-xs-3">
						<h3>SteamLUG</h3>
							<ul class="list-unstyled">
								<li><a href="http://steamcommunity.com/groups/steamlug/">SteamLUG Steam Group</a></li>
								<li><a href="/irc">SteamLUG IRC Chat</a></li>
								<li><a href="/mumble">SteamLUG Mumble Server</a></li>
								<li><a href="http://forums.steampowered.com/forums/showthread.php?t=1897204">SPUF Thread</a></li>
							</ul>
					</div>
					<div class="col-xs-3">
						<h3>Valve</h3>
							<ul class="list-unstyled">
								<li><a href="http://store.steampowered.com/linux">Steam For Linux Store Page</a></li>
								<li><a href="https://github.com/ValveSoftware/steam-for-linux/">Steam For Linux on GitHub</a></li>
								<li><a href="http://steamcommunity.com/app/221410">Steam For Linux Hub</a></li>
								<li><a href="http://blogs.valvesoftware.com/linux/">Valve Linux Blog</a></li>
							</ul>
					</div>
					<div class="col-xs-3">
						<h3>Community</h3>
							<ul class="list-unstyled">
								<li><a href="http://www.reddit.com/r/linux_gaming/">Linux_Gaming Subreddit</a></li>
								<li><a href="http://steamdb.info/">SteamDB.info</a></li>
								<li><a href="http://steamdb.info/linux/">The Big List of Steam Games on Linux</a></li>
								<li><a href="http://gamingonlinux.com/">Gaming On Linux</a></li>
							</ul>
					</div>
				</div>
				<p class="muted credit text-center">This site is not affiliated with or endorsed by Valve, Steam, or any of their partners.<br />
All registered trademarks or copyrights are property of their respective owners.</p>
			</div>
		</footer>
	</body>
<?php
if (isset($tailJS)) {
	foreach ($tailJS as $js) {
		echo "\t<script src='" . htmlspecialchars($js) . "' type='text/javascript'></script>\n";
	}
}
?>
</html>
