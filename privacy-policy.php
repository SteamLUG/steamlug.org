<?php
	$pageTitle = "Privacy Policy";
	include_once('includes/header.php');

	echo <<<DOCUMENT
		<h1 class="text-center">Privacy Policy</h1>
		<section id="pp">

			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">Data from group members</h3>
				</header>
				<div class="panel-body">
					<p>We are keenly aware of the importance of your data in our hands. We take a considered approach to any use or storage of it, such that we err on the side of caution before doing things. If at any point you wish your data to no longer be used, you can leave the Steam group or set your profile privacy to Private.</p>
					<p>With being a member of our Steam group, your game ownership and playtime is collected fortnightly to produce Anonymised statistics for our use. No details are kept that relate playtime, or games, to you. We produce aggregated graphs from this data to inform our choices for event selection each month, and for understanding trends in our community. This is public data that we host on the site. Historical data is used for trends only.</p>
					<p>Attending events with the community will register your attendance automatically (well, that is the aim!), storing just your SteamID.</p>
				</div>
			</article>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">What if you sign into the site?</h3>
				</header>
				<div class="panel-body">
					<p>Using Steam's OAuth login, the only data we get back from sign-in is your SteamID. We then query Steam for some further data (your avatar and persona name), throw away the rest, and put it temporarily in session data. We set a cookie for the session, and set a timeout of 2 days. We keep none of that data on the server after that point.</p>
				</div>
			</article>
			<article class="panel panel-default">
				<header class="panel-heading">
					<h3 class="panel-title">What if you store your account with us?</h3>
				</header>
				<div class="panel-body">
					<p>With the new account system on the site -- intended for use with our Clan system -- if you choose to opt-in, we persistently store a few details from Steam, your SteamID, coupled with your personaname, vanity URL, and avatar. This creates a public URL on our site for your profile, linking you to your clans and attended events.</p>
					<p>At any time, you can delete your account.</p>
				</div>
			</article>
		<section>
DOCUMENT;

	include_once('includes/footer.php');


