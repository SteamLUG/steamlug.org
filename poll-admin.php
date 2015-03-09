<?php
	$pageTitle = "Polls";
	include_once('includes/header.php');
	include_once('includes/lastRSS.php');
?>
<h1 class="text-center">Poll Admin</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Polls</h3>
			</header>
			<div class="panel-body">
					<?php
						if(!login_check())
						{
							if (empty($steam_login_verify))
							{
								$steam_sign_in_url = SteamSignIn::genUrl();
								echo "<p>You need to log in to view this page.</p>";
								echo "<a class = 'steamLogin' href=\"$steam_sign_in_url\"><img src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png' alt = 'Log into Steam' /></a>";
							}
						}
						else
						{
							echo "<p>You are currently logged in. Click to <a href = 'logout.php'>log out</a></p>";
							echo "<form class=\"form-horizontal\" method = 'get'>\n";
							echo "<!-- " . $_SESSION['u'] . " !-->\n";
							//include_once('creds.php');
							
							
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
							echo <<<FORMGROUP
								<div class="form-group">
									<label for="deletePoll" class="col-lg-2 control-label">Delete</label>
									<div class="col-lg-10">
										<input type="checkbox" id="deletePoll" name="deletePoll">
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12">
										<button type="submit" class="btn btn-default">Go</button>
									</div>
								</div>
							</form>
FORMGROUP;

							showPollAdmin();
						}
					?>

				</div>
			</article>
<?php include_once('includes/footer.php'); ?>
