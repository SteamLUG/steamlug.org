<?php
	header("Content-Type: application/rss+xml");
	header("Access-Control-Allow-Origin: *");
	if (!isset($_GET['t'])|| $_GET['t'] == "ogg")
	{
		$type = "ogg";
	} else 
	{
		$type = "mp3";
	}
	$path = "/var/www/archive.steamlug.org/steamlugcast";
	$url  = "http://archive.steamlug.org/steamlugcast";

	function getlength($u)
	{
		$avconv = "/usr/bin/avconv";
		$time =  exec("$avconv -i $u 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");   
		$duration = explode(".",$time);   
#		$duration_in_seconds = $duration[0]*3600 + $duration[1]*60+ round($duration[2]);   

		return $duration[0];   
	}
	function gettitle($u)
	{
		$avconv = "/usr/bin/avconv";
		$title  = exec("$avconv -i $u 2>&1 | grep 'TITLE' | cut -d ':' -f 2");
		$title  = substr($title,1);

		return $title;
	}
	function slenc($u)
	{    
        	return htmlentities($u,ENT_QUOTES, "UTF-8");
	}    
	if (!function_exists('glob_recursive'))
	{    
        	function glob_recursive($pattern, $flags = 0)
		{      
			$files = glob($pattern, $flags);
			foreach (array_reverse(glob(dirname($pattern).'/*', GLOB_ONLYDIR)) as $dir)
      			{
				$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));         
      			}
        	return $files;   
        	}      
	}

	if (!function_exists('glob_reversive'))
	{    
        	function glob_reversive($pattern, $flags = 0)
		{      
			$files = glob($pattern, $flags);
			foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR) as $dir)
      			{
				$files = array_merge($files, glob_reversive($dir.'/'.basename($pattern), $flags));         
      			}
        	return $files;   
        	}      
	}
	function latestCast()
	{
		global $path;
		foreach(glob_reversive($path . "*.txt") as $filename)
		{
	                $file = basename($filename, ".txt");
        	        $regex = "/[sS]([0-9]+)[eE]([0-9]+)\.(\w+(-\w+)*)/";
               		preg_match($regex, $filename, $matches);
	                $episodeBase = $path . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
		}
		return date("D, d M Y H:i:s O", filemtime($episodeBase . ".txt"));
	}	
	$items = "";
	foreach(glob_recursive($path . "*.txt") as $filename)
	{
		$file = basename($filename, ".txt");
		$regex = "/[sS]([0-9]+)[eE]([0-9]+)\.(\w+(-\w+)*)/";
		preg_match($regex, $filename, $matches);
		$archiveBase = $url . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
		$episodeBase = $path . "/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "/" . $file;
		$description = file($episodeBase . ".txt");
		$itemContent = "<item>\n";
		$itemContent .= "\t<title>" . gettitle($archiveBase . ".ogg") . "</title>\n";
		$itemContent .= "\t<pubDate>" . date("D, d M Y H:i:s O", filemtime($episodeBase . ".txt")) . "</pubDate>\n";
		$itemContent .= "\t<itunes:duration>" . getlength($archiveBase . ".ogg") . "</itunes:duration>\n";
		$itemContent .= "\t<link>https://steamlug.org/cast/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "</link>\n";
		$itemContent .= "\t<guid>https://steamlug.org/cast/s" . slenc($matches[1]) . "e" . slenc($matches[2]) . "</guid>\n";
		$itemContent .= "\t<enclosure url=\"" . $archiveBase . "." . $type . "\" length=\"" . filesize($episodeBase . "." . $type) . "\" type=\"audio/" . ($type == "ogg" ? "ogg" : "mpeg") . "\" />\n";
		$itemContent .= "\t<media:content url=\"" . $archiveBase . "." . $type . "\" fileSize=\"" . filesize($episodeBase . "." . $type) . "\" type=\"audio/" . ($type == "ogg" ? "ogg" : "mpeg") . "\" medium=\"audio\" expression=\"full\" />\n";
		$itemContent .= "\t<itunes:explicit>no</itunes:explicit>\n";
		$itemContent .="\t<media:rating scheme=\"urn:simple\">nonadult</media:rating>\n";
		$itemContent .= "\t<description><![CDATA[";
		foreach ($description as $note)
		{
			$note = preg_replace_callback
			(
			'/\d+:\d+:\d+\s+\*(.*)\*/',
			function($matches){ return "<p>" . slenc($matches[1]) . "</p>\n<ul>\n"; }, $note
			);
			$note = preg_replace_callback(
			'/(\d+:\d+:\d+)/',
			function($matches){ return "<time datetime='" . slenc($matches[1]) . "'>" . slenc($matches[1]) . "</time>"; },
			$note
			);
			$note = preg_replace_callback(
			'/^<time.*$/',
			function($matches){ return "<li>" . $matches[0] . "</li>\n"; },
			$note
			);
			$note = preg_replace_callback
			(
			'/(?i)\b((?:(https?|irc):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
			function($matches){ return "[<a href='" . slenc($matches[0]) . "'>" . slenc($matches[0]) . "</a>]"; },
			$note
			);
			$note = preg_replace_callback
			(
			'/(?<=^|\s)@([a-z0-9_]+)/i',
			function($matches){ return "<a href='http://twitter.com/" . slenc($matches[1]) . "'>" . slenc($matches[0]) . "</a>"; },
			$note
			);
			$note = preg_replace_callback
			(
			'/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/',
			function($matches){ return "<a href='mailto:". slenc($matches[0]) . "'>" . slenc($matches[0]) . "</a>"; },
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
			function($matches){ return "<li>&lt;" . $matches[1] . "&gt; " . $matches[2] . "</li>\n"; },
			$note
			);
			$note = preg_replace_callback
			(
			'/\t(.*)/',
			function($matches){ return "<li>" . $matches[1] . "</li>\n"; },
			$note
			);
			$note = preg_replace_callback
			(
			'/  (.*)/',
			function($matches){ return "\t\t\t<p>" . $matches[1] . "</p>\n"; },
			$note
			);
			$note = preg_replace_callback
			(
			'/\[(\w\d+\w\d+)\]/',
			function($matches){ return "\t\t\t<a href='http://steamlug.org/cast/" . $matches[1] . "'>" . $matches[1] . "</a>\n"; },
			$note
			);
			$itemContent .= $note;
			}			
			$itemContent .= "\t]]></description>\n";
			$itemContent .= "\t<itunes:subtitle>" . substr($description[0],2,161) . "…</itunes:subtitle>\n";
			$itemContent .= "</item>\n";
			$items	.= $itemContent;
		}
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<rss xmlns:media=\"http://search.yahoo.com/mrss/\" xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\" xmlns:atom=\"http://www.w3.org/2005/Atom\" version=\"2.0\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:cc=\"http://web.resource.org/cc/\">\n";
	echo "\t<channel>\n";
	echo "\t\t<title>SteamLUG Cast</title>\n";
	echo "\t\t<atom:link href=\"http://steamlug.org/feed/cast/" . $type . "\" rel=\"self\" type=\"application/rss+xml\" />\n";
#	echo "\t\t<atom:link href=\"http://steamlug.org/cast/rss\" rel=\"alternate\" title=\"SteamLUG Cast (". $type . ") Feed\" type=\"application/rss+xml\" />";
	echo "\t\t<link>http://steamlug.org/cast</link>\n";
	echo "\t\t<description>SteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</description>\n";
	echo "\t\t<itunes:author>SteamLUG</itunes:author>\n";
	echo "\t\t<itunes:owner>\n";
	echo "\t\t\t<itunes:name>SteamLUG</itunes:name>\n";
	echo "\t\t\t<itunes:email>cast@steamlug.org</itunes:email>\n";
	echo "\t\t</itunes:owner>\n";
	echo "\t\t<language>en</language>\n";
	echo "\t\t<image>\n";
	echo "\t\t\t<url>http://steamlug.org/images/steamlugcast.png</url>\n";
	echo "\t\t\t<title>SteamLUG Cast</title>\n";
	echo "\t\t\t<link>http://steamlug.org/cast</link>\n";
	echo "\t\t</image>\n";
	echo "\t\t<itunes:image href=\"http://steamlug.org/images/steamlugcast.png\" />\n";
	echo "\t\t<copyright>2013 © SteamLUG cast, CC-BY-SA http://creativecommons.org/licenses/by-sa/3.0/</copyright>\n";
	echo "\t\t<cc:license rdf:resource=\"http://creativecommons.org/licenses/by-sa/3.0/\" />\n";
	echo "\t\t<pubDate>" . latestCast() . "</pubDate>\n";
	echo "\t\t<itunes:category text=\"Games &amp; Hobbies\">\n";
	echo "\t\t\t<itunes:category text=\"Video Games\" />\n";
	echo "\t\t</itunes:category>\n";
	echo "\t\t<itunes:keywords>Linux, Steam, SteamLUG, Gaming, FOSS</itunes:keywords>\n";
	echo "\t\t<media:keywords>Linux, Steam, SteamLUG, Gaming, FOSS</media:keywords>\n";
	echo "\t\t<itunes:explicit>no</itunes:explicit><media:rating scheme=\"urn:simple\">nonadult</media:rating>\n";
	echo $items;
	echo "</channel>\n";
	echo "</rss>\n";
?>
