<?php
	$pageTitle = "Polls";
	include_once("includes/header.php");
	include_once("includes/lastRSS.php");
?>
		<header>
				<h1>Community Polls</h1>
		</header>
		<section>
			<article>
				<div class = 'shadow'>
					<h1>About</h1>
					<p>We're launching a new polling feature for SteamLUG.org, which will allow us to get better community input on things like events, SteamLUG Cast topics, future projects and more!</p>
					<p>To vote, you need to be signed in via Steam using the button below, and you mustm be a member of our Steam group.</p>
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
			</article>
			<article>
				<div class = 'shadow'>
					<h1>Current Polls</h1>
					<?php 	showCurrentPolls(); ?>
				</div>
			</article>
		</section>
<?php include_once("includes/footer.php"); ?>
