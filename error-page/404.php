<?php
	$pageTitle = "404 Not Found";
	include_once('../includes/header.php');

	echo <<<DOCUMENT
		<h1 class="text-center">404 Not Found</h1>
		<article class="panel panel-danger">
			<header class="panel-heading">
				<h3 class="panel-title">Unable to retrieve file</h3>
			</header>
			<div class="panel-body">
				<p>The file you have tried to access does not exist</p>
			</div>
		</article>

DOCUMENT;

	include_once('../includes/footer.php');

