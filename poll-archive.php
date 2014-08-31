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
					<p>Results for past SteamLUG community polls can be found here!</p>
				</div>
			</article>
			<article>
				<div class = 'shadow'>
					<h1>Past Polls</h1>
					<?php 	showPastPolls(); ?>
				</div>
			</article>
		</section>
<?php include_once("includes/footer.php"); ?>
