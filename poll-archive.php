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
					<p>Results for past SteamLUG community polls can be found here!</p>
		</div>
	</div>
					<?php showPastPolls(); ?>
<?php include_once("includes/footer.php"); ?>
