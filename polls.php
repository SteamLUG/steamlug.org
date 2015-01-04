<?php
	$pageTitle = "Polls";
	include_once("includes/header.php");
	include_once("includes/lastRSS.php");
?>
<h1 class="text-center">Community Polls</h1>
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">About</h3>
			</div>
			<div class="panel-body">
					<p>We're launching a new polling feature for SteamLUG.org, which will allow us to get better community input on things like events, SteamLUG Cast topics, future projects and more!</p>
					<p>To vote, you need to be signed in via Steam using the button below, and you must be a member of our <a href = 'http://steamcommunity.com/groups/steamlug/'>Steam group</a>.</p>
					<?php
						if(!login_check())
						{
							if (empty($steam_login_verify))
							{
								$steam_sign_in_url = SteamSignIn::genUrl();
								echo "<a class = 'steamLogin' href=\"$steam_sign_in_url\"><img src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png' alt = 'Log into Steam' /></a>";
							}
						}
						else
						{
							echo "<p>You are currently logged in. Click to <a href = 'logout.php'>log out</a></p>";
							echo "<!-- " . $_SESSION['u'] . "-->";
						}
					?>

				</div>
		</div>
					<?php 	showCurrentPolls(); ?>
<?php include_once("includes/footer.php"); ?>
