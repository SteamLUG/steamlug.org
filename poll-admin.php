<?php
	$pageTitle = "Polls";
	include_once("includes/header.php");
	include_once("includes/lastRSS.php");
?>
		<header>
				<h1>SteamLUG Poll Admin</h1>
		</header>
		<section>
			<article>
				<div class = 'shadow'>
					<h1>Poll Admin!</h1>
					<p>You need to log in to view this page.</p>
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
							echo "<form action = '' method = 'get'>\n";
							echo "<!-- " . $_SESSION['u'] . "-->";
							//include_once("creds.php");
							
							
							if (isset($_POST['poll_title']))
							{
								savePoll();
							}
							
							//TODO: We should probably sort out $_GET and $_POST stuff so that it's handled more consistently/nicely
							// Would be nice to have the site send everything via POST, but still allow for navigation to an admin page via GET parameters
							if (isset($_GET['poll']) && isset($_GET['deletePoll']))
							{
								deletePoll($_GET['poll']);
							}
							
							
							showPollSelector('poll', (isset($_GET['poll']) ? $_GET['poll'] : -1), True, 20);
							echo "\t<div class = 'formPair'>\n";
							echo "\t<label for = 'deletePoll'>Delete</label>\n";
							echo "\t<input type = 'checkbox' id = 'deletePoll' name = 'deletePoll'>\n";
							echo "\t</div>\n";
							echo "<input type = 'submit' value = 'Go'/>";
							echo "</form>\n";

							showPollAdmin();
						}
					?>

				</div>
			</article>
		</section>
<?php include_once("includes/footer.php"); ?>
