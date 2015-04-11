<?php
	$pageTitle = "About Peeps";
	include_once('includes/header.php');

	echo <<<DOCUMENT
		<h1 class="text-center">SteamLUG Admins</h1>
		<section id="peeps">

			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title"><a href="http://twitter.com/johndrinkwater">johndrinkwater</a></h3>
				</header>
				<div class="panel-body">
					<img src="/avatars/johndrinkwater.png" />
					<p>Hey! I’m John.</p>
				</div>
			</article>

			<article class="panel panel-default person">
				<header class="panel-heading">
					<h3 class="panel-title">meklu</h3>
				</header>
				<div class="panel-body">
					<img src="/avatars/mnarikka.png" />
					<p>A consumer of copious quantities of ammonium chloride and a lazy perfectionist. All my projects are eternal.</p>
				</div>
			</article>

		<section>
DOCUMENT;

	include_once('includes/footer.php');


