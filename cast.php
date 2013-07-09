<?php
$season  = isset($_GET["s"]) ? $_GET["s"] : "0";
$episode = isset($_GET["e"]) ? $_GET["e"] : "0";
$d = explode("-", "2013-07-12");
$t = explode(":", "20:00");

$dateString = "var target = Math.round( Date.UTC (" . $d[0] . ", " . $d[1] . " -1, " . $d[2] . ", " . $t[0] . ", " . $t[1] . ", 0, 0) / 1000);";
$extraJS = $dateString;
$externalJS = array('scripts/events.js');
$pageTitle = "Cast";

$path = "/var/www/archive.steamlug.org/steamlugcast";
$url  = "http://archive.steamlug.org/steamlugcast";
include_once('includes/header.php');
?>
        <header>
                <h1>SteamLUG Cast</h1>
        </header>
<section>
<?
if ($season == "0" || $episode == "0" || !glob($path . "/s" . basename($season) . "e" . basename($episode) . "/*"))
{
        $aboutPage  = "<article>";
        $aboutPage .= "<div class='shadow'>";
        $aboutPage .= "<h1>About</h1>";
        $aboutPage .= "<p>SteamLUG Cast is a casual, fortnightly live audiocast held on the <a href = 'mumble'>SteamLUG Mumble server</a> which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</p>";
        $aboutPage .= "<p>Our current hosts are:</p>";
        $aboutPage .= "<ul>";
        $aboutPage .= "<li><a href='http://steamcommunity.com/id/cheeseness'>Cheeseness</a> - SteamLUG's benevolent leadery person</li>";
        $aboutPage .= "<li><a href='http://steamcommunity.com/id/johndrinkwater'>johndrinkwater</a> - SteamLUG admin and volunteer Valve github maintainer</li>";
        $aboutPage .= "<li><a href='http://steamcommunity.com/id/swordfischer'>swordfischer</a> - SteamLUG's chief event organiserer</li>";
        $aboutPage .= "</ul>";
        $aboutPage .= "<p>From time to time, we also have guests joining to share their insights on Linux, the gaming industry and the SteamLUG community. Check back for recording archives, shownotes and further announcements!</p>";
        $aboutPage .= "</div>";
        $aboutPage .= "</article>";
        $aboutPage .= "<article id = 'nextevent'>";
        $aboutPage .= "<div>";
        $aboutPage .= "\t<h1>Upcoming Episode:</h1>";
        $aboutPage .= "\t<h2>S01, E03</h2>";
        $aboutPage .= "\t<p>Cheese, john and sword talk about SteamLUG Casty things!</p>";
        $aboutPage .= "\t<div id='countdown'>";
        $aboutPage .= "\t<div>Days<br />";
        $aboutPage .= "\t<span id='d1' class = 'counterDigit'>0</span>";
        $aboutPage .= "\t<span id='d2' class = 'counterDigit'>0</span>";                                                                                                                                                                             $aboutPage .= "\t</div>";                                                                                                                                                                                                                    $aboutPage .= "\t<div>Hours<br />";                                                                                                                                                                                                          $aboutPage .= "\t<span id='h1' class = 'counterDigit'>0</span>";
        $aboutPage .= "\t<span id='h2' class = 'counterDigit'>0</span>";
        $aboutPage .= "\t</div>";                                                                                                                                                                                                                    $aboutPage .= "\t<div>Minutes<br />";
        $aboutPage .= "\t<span id='m1' class = 'counterDigit'>0</span>";                                                                                                                                                                             $aboutPage .= "\t<span id='m2' class = 'counterDigit'>0</span>";
        $aboutPage .= "\t</div>";                                                                                                                                                                                                                    $aboutPage .= "\t<div>Seconds<br />";
        $aboutPage .= "\t<span id='s1' class = 'counterDigit'>0</span>";
        $aboutPage .= "\t<span id='s2' class = 'counterDigit'>0</span>";                                                                                                                                                                             $aboutPage .= "\t</div>";
        $aboutPage .= "\t</div>";                                                                                                                                                                                                                    $aboutPage .= "<p>Feel free to join our <a href = 'mumble'>SteamLUG Mumble server</a> before, during and after the show!</p>";
        $aboutPage .= "</div>";
        $aboutPage .= "</article>";
        echo $aboutPage;
}
?>
        <article class='shownotes'>
                <div class="shadow">
<?php
if (!function_exists('glob_recursive'))
{
        function glob_recursive($pattern, $flags = 0)
        {
                $files = glob($pattern, $flags);
                foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
                {
                        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
                }
        return $files;
        }
}


if ($season > "0" && $episode > "0" && glob($path . "/s" . basename($season) . "e" . basename($episode) . "/*"))
{
        $showEpisode = glob($path . "/s" . basename($season) . "e" . basename($episode) . "/*");
        $showEpisode[4] = str_replace(".txt", "", $showEpisode[4]);
        $file = basename($showEpisode[4]);
        $regex = "/[sS]([0-9]+)[eE]([0-9]+)\.(\w+-?(\w+)?)/";
        preg_match($regex, $showEpisode[4], $matches);
        $archiveBase = $url . "/s" . $matches[1] . "e" . $matches[2] . "/" . $file;

        $listItem  = "\t\t\t<h1>" . htmlentities(str_replace('-', ' ', $matches[3]), ENT_QUOTES, "UTF-8") . "</h1>\n";                                                                                                                               $listItem .= "\t\t\t<h3>Season: $season, Episode: $episode</h3>\n";
        $listItem .= "\t\t\t<audio preload='none' src='$archiveBase.ogg' type='audio/ogg' controls>Your browser does not support the &lt;audio&gt; tag.</audio>\n";
        $listItem .= "\t\t\t<p>\n";
        $listItem .= "\t\t\t\t" . round(filesize($showEpisode[2])/1024/1024,2) . " MB <a href='$archiveBase.ogg'>OGG</a> | \n";
        $listItem .= "\t\t\t\t" . round(filesize($showEpisode[0])/1024/1024,2) . " MB <a href='$archiveBase.flac'>FLAC</a> | \n";
        $listItem .= "\t\t\t\t" . round(filesize($showEpisode[1])/1024/1024,2) . " MB <a href='$archiveBase.mp3'>MP3</a>\n";
        $listItem .= "\t\t\t\t<a href='http://creativecommons.org/licenses/by-sa/3.0/'><img class='license' src='http://mirrors.creativecommons.org/presskit/buttons/80x15/png/by-sa.png' alt='Licensed under CC-BY-SA'></a>\n";
        $listItem .= "\t\t\t</p>\n";
        $listItem .= "\t\t\t<h3>Shownotes</h3>\n";
        echo $listItem;

        $showNotes = file($showEpisode[4] . ".txt");
        foreach ($showNotes as $note)
        {
                $note = preg_replace_callback
                (
                '/\d+:\d+:\d+\s+\*(.*)\*/',
                function($matches){ return "<ul class='castsection'><li><span class='casttopic'>" . htmlentities($matches[1],ENT_QUOTES, "UTF-8") . "</span></li>\n"; },
                $note
                );                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        $note = preg_replace_callback                                                                                                                                                                                                                (
                '/(\d+:\d+:\d+)/',
                function($matches){ return "<time datetime='" . htmlentities($matches[1],ENT_QUOTES, "UTF-8") . "'>" . htmlentities($matches[1],ENT_QUOTES) . "</time>"; },                                                                                  $note
                );                                                                                                                                                                                                                           
                $note = preg_replace_callback                                                                                                                                                                                                                (
                '/^<time.*$/',
                function($matches){ return "<li>" . $matches[0] . "</li>\n"; },                                                                                                                                                                              $note
                );                                                                                                                                                                                                                           
                $note = preg_replace_callback
                (
                '/(?i)\b((?:(https?|irc):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
                function($matches){ return "[<a href='" . htmlentities($matches[0],ENT_QUOTES, "UTF-8") . "' class='castsource'>source</a>]"; },
                $note
                );

                $note = preg_replace_callback
                (
                '/(?<=^|\s)@([a-z0-9_]+)/i',
                function($matches){ return "<a href='http://twitter.com/" . htmlentities($matches[1],ENT_QUOTES, "UTF-8") . "'>" . htmlentities($matches[0],ENT_QUOTES) . "</a>"; },
                $note
                );

                $note = preg_replace_callback
                (
                '/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/',
                function($matches){ return "<a href='mailto:". htmlentities($matches[0],ENT_QUOTES, "UTF-8") . "'>" . htmlentities($matches[0],ENT_QUOTES) . "</a>"; },
                $note
                );

                $note = preg_replace_callback
                (
                '/^\n$/',
                function($matches){ return "</ul>\n"; },
                $note
                );

                $note = preg_replace_callback
                (
                '/\t\[(\w+)\](.*)/',
                function($matches){ return "<li class='nostamp'>&lt;<span class='nickname'>" . $matches[1] . "&gt;</span> " . $matches[2] . "</li>\n"; },                                                                                                    $note
                );

                $note = preg_replace_callback
                (
                '/\t(.*)/',
                function($matches){ return "<li class='nostamp'>" . $matches[1] . "</li>\n"; },
                $note
                );

                $note = preg_replace_callback
                (
                '/  (.*)/',
                function($matches){ return "\t\t\t<p class='castabout'>" . $matches[1] . "</p>\n"; },
                $note
                );

                echo $note . "\n";
        }
}                                                                                                                                                                                                                                            else                                                                                                                                                                                                                                         {                                                                                                                                                                                                                                                    $listItem = "\t\t<ul>\n";
	echo "<h1>Previous Casts</h1>";
        if (!glob_recursive($path . "*.txt")) { echo "<h3>No archives found</h3>"; }
	
        foreach(glob_recursive($path . "*.txt") as $filename)                                                                                                                                                                                        {
                $file = basename($filename, ".txt");                                                                                                                                                                                                         $regex = "/[sS]([0-9]+)[eE]([0-9]+)\.(\w+-?(\w+)?)/";
                preg_match($regex, $filename, $matches);                                                                                                                                                                                                     $archiveBase = $url . "/s" . htmlentities($matches[1],ENT_QUOTES, "UTF-8") . "e" . htmlentities($matches[2],ENT_QUOTES, "UTF-8") . "/" . $file;
                $episodeBase = $path . "/s" . htmlentities($matches[1],ENT_QUOTES, "UTF-8") . "e" . $matches[2] . "/" . $file;
                $listItem .= "\t\t\t<li>\n";                                                                                                                                                                                                                 $listItem .= "\t\t\t\t<h3>" .  htmlentities(str_replace('-', ' ', $matches[3]), ENT_QUOTES, "UTF-8") . "</h3>\n";
                $listItem .= "\t\t\t\t<p class='showthenotes'><a href='cast/s" . htmlentities($matches[1],ENT_QUOTES, "UTF-8") .  "e" . htmlentities($matches[2],ENT_QUOTES, "UTF-8") . "'>Click for shownotes</a></p>\n";                                   $listItem .= "\t\t\t\t<p>Season: " . htmlentities($matches[1],ENT_QUOTES, "UTF-8") . ", Episode: " .  htmlentities($matches[2],ENT_QUOTES, "UTF-8") . "</p>\n";
                $listItem .= "\t\t\t\t<audio preload='none' src='$archiveBase.ogg' type='audio/ogg' controls >Your browser does not support the &lt;audio&gt; tag.</audio>\n";
                $listItem .= "\t\t\t\t<p>" . round(filesize($episodeBase . ".ogg")/1024/1024,2) . " MB <a href='$archiveBase.ogg'>OGG</a> | \n";
                $listItem .= "\t\t\t\t" . round(filesize($episodeBase . ".flac")/1024/1024,2) . " MB <a href='$archiveBase.flac'>FLAC</a> | \n";
                $listItem .= "\t\t\t\t" . round(filesize($episodeBase . ".mp3")/1024/1024,2) . " MB <a href='$archiveBase.mp3'>MP3</a>\n";
                $listItem .= "\t\t\t\t<a href='http://creativecommons.org/licenses/by-sa/3.0/'><img class='license' src='http://mirrors.creativecommons.org/presskit/buttons/80x15/png/by-sa.png' alt='Licensed under CC-BY-SA'></a></p>\n";
                $listItem .= "\t\t\t</li>\n";
        }
        $listItem .= "\t\t</ul>\n";
        echo $listItem;
}
?>
        </div>
    </article>
</section>
<?php include_once("/var/www/steamlug.org/includes/footer.php"); ?>
