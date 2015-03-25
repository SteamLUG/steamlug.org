<?php
	$pageTitle = "403 Forbidden";
	$skipAuth = "please";
	include_once('../includes/header.php');

	echo <<<DOCUMENT
		<h1 class="text-center">403 Forbidden</h1>
		<article class="panel panel-danger">
			<header class="panel-heading">
				<h3 class="panel-title"><em>You</em> are unable to view this.</h3>
			</header>
			<div class="panel-body">
				<p>The file or directory you have tried to access does not exist. Or it might. Maybe.</p>
			</div>
		</article>

DOCUMENT;

	include_once('../includes/footer.php');

