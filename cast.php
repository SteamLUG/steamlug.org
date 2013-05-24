<?php
	//
	$d = explode("-", "2013-05-24");
	$t = explode(":", "20:00");

	$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";

	$pageTitle = "Events";
	$extraJS = $dateString;
	$externalJS = array('scripts/events.js');
    $pageTitle = "Cast";
    include_once('includes/header.php');
?>
        <header>
            <hgroup>
                <h1>SteamLUG Cast</h1>
            </hgroup>
        </header>
<section>
        <article>
        <div class="shadow">
	<h1>About</h1>
	<p>SteamLUG Cast is a casual, fortnightly* live audiocast held on the <a href = 'mumble'>SteamLUG Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</p>
	<p>Our current hosts are:</p>
	<ul>
		<li><a href="http://steamcommunity.com/id/cheeseness">Cheeseness</a> - SteamLUG's benevolent leadery person</li>
		<li><a href="http://steamcommunity.com/id/johndrinkwater">johndrinkwater</a> - SteamLUG admin and volunteer Valve github maintainer</li>
		<li><a href="http://steamcommunity.com/id/swordfischer">swordfischer</a> - SteamLUG's chief event organiserer</li>
	</ul>
	<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>
	<p class = 'footnote'>* Schedule subject to change ^_^</p>
	</div>
	</article>


	<article id = 'nextevent'>
	<div>
		<h1>Upcoming Episode:</h1>
		<h2>S01, E01 - Introduction</h2>
		<p>Cheese, john and sword talk about SteamLUG Cast.</p>
		<div id="countdown">
			<div>Days<br />
				<span id="d1" class = "counterDigit">0</span>
				<span id="d2" class = "counterDigit">0</span>
			</div>
			<div>Hours<br />
				<span id="h1" class = "counterDigit">0</span>
				<span id="h2" class = "counterDigit">0</span>
			</div>
			<div>Minutes<br />
				<span id="m1" class = "counterDigit">0</span>
				<span id="m2" class = "counterDigit">0</span>
			</div>
			<div>Seconds<br />
				<span id="s1" class = "counterDigit">0</span>
				<span id="s2" class = "counterDigit">0</span>
			</div>
		</div>
		<p>Feel free to join our <a href = 'mumble'>SteamLUG Mumble server</a> before, during and after the show!</p>
	</div>
	</article>

        <article>
        <div class="shadow">
            
        <ul id="archive">
        <?php
            $path = "/var/www/archive.steamlug.org/";
            $url  = "http://archive.steamlug.org/";
            if (!glob($path . "*.flac")) { echo "<h3>No archives found</h3>"; } 
            foreach(glob($path . "*.flac") as $filename) {
                $file = basename($filename, ".flac");
                $regex = "/S([0-9]+)E([0-9]+)-(\w+)/";
                preg_match($regex, $filename, $matches);

                $listItem = "<li>";
                $listItem .= "<h3>" . str_replace('_', ' ', $matches[3]) . "</h3>";
                $listItem .= "<p>Season: $matches[1], Episode: $matches[2]</p>";
                $listItem .= "<audio preload='none' src='$url$file.ogg' type='audio/ogg' controls>Your browser does not support the &ltaudio&gt tag.</audio>";
                $listItem .= "<span class='right'>";
                $listItem .= round(filesize($filename)/1024/1024,2) . " MB <a href='$url$file.flac'>FLAC</a> | ";
                $listItem .= round(filesize(str_replace('flac', 'ogg', $filename))/1024/1024,2) . " MB <a href='$url$file.ogg'>OGG</a> | ";
                $listItem .= round(filesize(str_replace('flac', 'mp3', $filename))/1024/1024,2) . " MB <a href='$url$file.mp3'>MP3</a> ";
                $listItem .= "</span>";
                $listItem .= "</li>";
                echo $listItem;
            } 
        ?>
        </ul>
        </div>
    </article>
<?php include_once("/var/www/steamlug.org/includes/footer.php"); ?> 
