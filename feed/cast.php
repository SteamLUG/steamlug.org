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

	function slenc($u)
	{
        return htmlentities($u,ENT_QUOTES, "UTF-8");
	}

	/* gives us a list, like s02e03, s02e02, etc of all of our casts */
	$casts = scandir($path, 1);
	/* naïve as fook, but we know this. */
	$latestCast = date("D, d M Y H:i:s O", filemtime( $path . '/' . $casts[0] . '/episode.txt' ));

	// <atom:link href=\"http://steamlug.org/cast/rss\" rel=\"alternate\" title=\"SteamLUG Cast (". $type . ") Feed\" type=\"application/rss+xml\" />";

	/* for sake of reading/modification, use HEREDOC syntax */
	echo <<<CASTHEAD
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:media="http://search.yahoo.com/mrss/" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:cc="http://web.resource.org/cc/">
	<channel>
		<title>SteamLUG Cast</title>
		<atom:link href="http://steamlug.org/feed/cast/$type" rel="self" type="application/rss+xml" />
		<link>http://steamlug.org/cast</link>
		<description>SteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</description>
		<itunes:author>SteamLUG</itunes:author>
		<itunes:owner>
			<itunes:name>SteamLUG</itunes:name>
			<itunes:email>cast@steamlug.org</itunes:email>
		</itunes:owner>
		<language>en</language>
		<image>
			<url>http://steamlug.org/images/steamlugcast.png</url>
			<title>SteamLUG Cast</title>
			<link>http://steamlug.org/cast</link>
		</image>
		<itunes:image href="http://steamlug.org/images/steamlugcast.png" />
		<copyright>2013 © SteamLUG cast, CC-BY-SA http://creativecommons.org/licenses/by-sa/3.0/</copyright>
		<cc:license rdf:resource="http://creativecommons.org/licenses/by-sa/3.0/" />
		<pubDate>$latestCast</pubDate>
		<itunes:category text="Games &amp; Hobbies">
			<itunes:category text="Video Games" />
		</itunes:category>
		<itunes:keywords>Linux, Steam, SteamLUG, Gaming, FOSS</itunes:keywords>
		<media:keywords>Linux, Steam, SteamLUG, Gaming, FOSS</media:keywords>
		<itunes:explicit>no</itunes:explicit><media:rating scheme="urn:simple">nonadult</media:rating>
CASTHEAD;

	foreach( $casts as $castdir )
	{
		if ($castdir === '.' or $castdir === '..')
			break;

		$filename		= $path .'/'. $castdir . "/episode.txt";
		$shownotes		= file($filename);

		$head = array_slice( $shownotes, 0, 10 );
		$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
							'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
					'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL' ), '');
		foreach ( $head as $entry ) {
			list($k, $v) = explode( ':', $entry, 2 );
			$meta[$k] = trim($v); /* TODO remember to slenc() stuff! */
		}

		$epi = "s" . slenc($meta['SEASON']) . "e" . slenc($meta['EPISODE']);
		$archiveBase = $url . '/' . $epi . '/' . $meta['FILENAME'];
		$episodeBase = $path .'/' . $castdir . '/' . $meta['FILENAME'];

		/* if file missing, skip this entry */
		if (!file_exists( $episodeBase . "." . $type))
			continue;

		$itemContent = "<item>\n";
		$itemContent .= "\t<title>" . slenc($meta[ 'TITLE' ]) . "</title>\n";
		$itemContent .= "\t<pubDate>" . date(DATE_RFC2822, strtotime( $meta['PUBLISHED'] )) . "</pubDate>\n";
		$itemContent .= "\t<itunes:duration>" . $meta['DURATION'] . "</itunes:duration>\n";
		$itemContent .= "\t<link>https://steamlug.org/cast/" . $epi . "</link>\n";
		$itemContent .= "\t<guid>https://steamlug.org/cast/" . $epi . "</guid>\n";
		
		$itemContent .= "\t<enclosure url=\"" . $archiveBase . "." . $type . "\" length=\"" . filesize($episodeBase . "." . $type) . "\" type=\"audio/" . ($type == "ogg" ? "ogg" : "mpeg") . "\" />\n";
		$itemContent .= "\t<media:content url=\"" . $archiveBase . "." . $type . "\" fileSize=\"" . filesize($episodeBase . "." . $type) . "\" type=\"audio/" . ($type == "ogg" ? "ogg" : "mpeg") . "\" medium=\"audio\" expression=\"full\" />\n";
		$itemContent .= "\t<itunes:explicit>no</itunes:explicit>\n";
		$itemContent .="\t<media:rating scheme=\"urn:simple\">nonadult</media:rating>\n";
		$itemContent .= "\t<description><![CDATA[";
		foreach ( array_slice( $shownotes, 12 ) as $note)
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
		$itemContent .= "\t<itunes:subtitle>" . slenc(substr($meta['DESCRIPTION'],0,158)) . "…</itunes:subtitle>\n";
		$itemContent .= "</item>\n";
		echo $itemContent;
		$itemContent = "";
	}
	echo "</channel>\n";
	echo "</rss>\n";
?>
