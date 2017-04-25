		</div>
		<footer class="navbar-default navbar-bottom">
			<div class="container">
				<div class="row">
					<div class="col-xs-6 col-sm-3">
						<h3>Feeds</h3>
							<ul class="list-unstyled">
								<li><a href="https://steamcommunity.com/groups/steamlug/rss/">News Feed</a></li>
								<li><a href="/feed/events">Events Feed</a></li>
								<li><a href="/feed/calendar">Events iCal</a></li>
								<li><a href="https://twitter.com/steamlug">Twitter</a></li>
							</ul>
					</div>
					<div class="col-xs-6 col-sm-3">
						<h3>SteamLUG</h3>
							<ul class="list-unstyled">
								<li><a href="https://steamcommunity.com/groups/steamlug/">Steam Group</a></li>
								<li><a href="/irc">IRC Chat</a></li>
								<li><a href="/mumble">Mumble Server</a></li>
								<li><a href="https://github.com/SteamLUG">GitHub Organization</a></li>
							</ul>
					</div>
					<div class="col-xs-6 col-sm-3">
						<h3>Valve</h3>
							<ul class="list-unstyled">
								<li><a href="http://store.steampowered.com/linux">Steam Linux Store Page</a></li>
								<li><a href="https://github.com/ValveSoftware/steam-for-linux/">Steam Linux GitHub</a></li>
								<li><a href="https://steamcommunity.com/app/221410">Steam Linux Community</a></li>
								<li><a href="http://blogs.valvesoftware.com/linux/">Valve Linux Blog</a></li>
							</ul>
					</div>
					<div class="col-xs-6 col-sm-3">
						<h3>Community</h3>
							<ul class="list-unstyled">
								<li><a href="https://www.reddit.com/r/linux_gaming/">Linux_Gaming Subreddit</a></li>
								<li><a href="https://steamdb.info/">SteamDB.info</a></li>
								<li><a href="https://steamdb.info/linux/">SteamDB Linux</a></li>
								<li><a href="https://gamingonlinux.com/">Gaming On Linux</a></li>
							</ul>
					</div>
				</div>
				<p class="text-muted credit text-center">This site is not affiliated with or endorsed by Valve, Steam, or any of their partners.<br />
All registered trademarks or copyrights are property of their respective owners.</p>
<?php
if ( $weareadmin ) {
	print '<p class="text-muted credit text-center">Memory: ' . memory_get_usage( ) . '</p>';
}
?>
			</div>
		</footer>
		<script src="/scripts/jquery-2.1.3.min.js" type="text/javascript"></script>
		<script src="/scripts/bootstrap.min.js" type="text/javascript"></script>
<?php
if (isset($tailJS)) {
	foreach ($tailJS as $js) {
		echo "\t\t<script src=\"" . $js . "\" type=\"text/javascript\"></script>\n";
	}
}

if (isset($tailScripts)) {
	foreach ($tailScripts as $js) {
		echo "\t\t<script type=\"text/javascript\">\n" . $js . "\n\t\t</script>\n";
	}
}
?>
	</body>
</html>
