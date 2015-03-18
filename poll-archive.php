<?php
	$pageTitle = "Polls";
	include_once('includes/header.php');
?>
<h1 class="text-center">Community Polls</h1>
	<article class="panel panel-default">
		<header class="panel-heading">
			<h3 class="panel-title">About</h3>
		</header>
		<div class="panel-body">
					<p>Results for past SteamLUG community polls can be found here!</p>
		</div>
	</article>
					<?php showPastPolls(); ?>
<?php include_once('includes/footer.php'); ?>
