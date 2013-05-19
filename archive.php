<?php
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
            
        <ul id="archive">
        <?php
            $path = "/var/www/archive.steamlug.org/";
            $url  = "http://archive.steamlug.org/";
            if (!glob($path . "*.flac")) { echo "<h3>No files found</h3>"; } 
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
