<?php
	$pageTitle = 'Polls';
	include_once('includes/header.php');
	include_once('includes/functions_poll.php');
?>
<h1 class="text-center">Community Polls</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">About</h3>
			</header>
			<div class="panel-body">
					<p>We're launching a new polling feature for SteamLUG.org, which will allow us to get better community input on things like events, SteamLUG Cast topics, future projects and more!</p>
					<?php
						if( !isset($_SESSION['u']) ) {
							echo "<p>To vote, you need to be signed in via Steam using the button in the menu, and you must be a member of our <a href=\"https://steamcommunity.com/groups/steamlug/\">Steam group</a>.</p>";
						}
					?>

				</div>
		</article>
					<?php	showCurrentPolls(); ?>
<?php include_once('includes/footer.php');
