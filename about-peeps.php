<?php
	$pageTitle = "About Peeps";
	include_once('includes/header.php');
	include_once('includes/paths.php');

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
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vulputate tellus non eleifend sollicitudin. In et est tincidunt, ornare tortor eget, aliquam justo. Pellentesque fermentum mollis euismod. Pellentesque libero eros, sollicitudin in laoreet a, ultrices quis odio. Fusce ipsum nulla, ullamcorper a odio ac, blandit mollis ligula. Suspendisse massa nisi, molestie at nisl non, pellentesque dapibus nulla. Vestibulum iaculis felis id ligula posuere lacinia.</p>
				</div>
			</article>

		<section>
DOCUMENT;

	include_once('includes/footer.php');


