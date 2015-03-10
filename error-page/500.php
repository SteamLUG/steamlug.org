<?php
	$pageTitle = "500 Server Error";
	include_once('../includes/header.php');

	echo <<<DOCUMENT
		<h1 class="text-center">500 Server Error</h1>
		<article class="panel panel-danger">
			<header class="panel-heading">
				<h3 class="panel-title">Our poor, poor server</h3>
			</header>
			<div class="panel-body">
				<p>Something broke. We’re sorry, it cannot do what you asked of it at this moment. Please return &lt;3</p>
			</div>
		</article>

DOCUMENT;

	include_once('../includes/footer.php');

