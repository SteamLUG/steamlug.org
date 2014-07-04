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
	$url  = "https://archive.steamlug.org/steamlugcast";

	function slenc($u)
	{
		return htmlspecialchars($u, ENT_NOQUOTES, "UTF-8");
	}

	/* gives us a list, like s02e03, s02e02, etc of all of our casts */
	$casts = scandir($path, 1);
	/* naïve as fook, but we know this. */
	$latestCast = date("D, d M Y H:i:s O", filemtime( $path . '/' . $casts[0] ));

	// <atom:link href=\"https://steamlug.org/cast/rss\" rel=\"alternate\" title=\"SteamLUG Cast (". $type . ") Feed\" type=\"application/rss+xml\" />";

	/* for sake of reading/modification, use HEREDOC syntax */
	echo <<<CASTHEAD
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:media="http://search.yahoo.com/mrss/" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:cc="http://web.resource.org/cc/">
	<channel>
		<title>SteamLUG Cast</title>
		<atom:link href="https://steamlug.org/feed/cast/$type" rel="self" type="application/rss+xml" />
		<link>https://steamlug.org/cast</link>
		<description>SteamLUG Cast is a casual, fortnightly audiocast which aims to provide interesting news and discussion for the SteamLUG and broader Linux gaming communities.</description>
		<itunes:author>SteamLUG</itunes:author>
		<itunes:owner>
			<itunes:name>SteamLUG</itunes:name>
			<itunes:email>cast@steamlug.org</itunes:email>
		</itunes:owner>
		<language>en</language>
		<image>
			<url>https://steamlug.org/images/steamlugcast.png</url>
			<title>SteamLUG Cast</title>
			<link>https://steamlug.org/cast</link>
		</image>
		<itunes:image href="https://steamlug.org/images/steamlugcast.png" />
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
			continue;

		$filename		= $path .'/'. $castdir . "/episode.txt";
		if (!file_exists($filename))
			continue;

		$shownotes		= file($filename);

		$head = array_slice( $shownotes, 0, 10 );
		$meta = array_fill_keys( array('RECORDED', 'PUBLISHED', 'TITLE',
							'SEASON', 'EPISODE', 'DURATION', 'FILENAME',
					'DESCRIPTION','HOSTS','GUESTS','ADDITIONAL' ), '');
		foreach ( $head as $entry ) {
			list($k, $v) = explode( ':', $entry, 2 );
			$meta[$k] = trim($v); /* TODO remember to slenc() stuff! */
		}

		/* if published unset, skip this entry */
		if ( $meta['PUBLISHED'] === '' )
			continue;

		$epi = "s" . slenc($meta['SEASON']) . "e" . slenc($meta['EPISODE']);
		$archiveBase = $url . '/' . $epi . '/' . $meta['FILENAME'];
		$episodeBase = $path .'/' . $castdir . '/' . $meta['FILENAME'];

		/* if file missing, skip this entry */
		if (!file_exists( $episodeBase . "." . $type))
			continue;

		$meta['PUBLISHED'] = date(DATE_RFC2822, strtotime( $meta['PUBLISHED'] ));
		$meta['TITLE'] = slenc($meta['TITLE']);
		$meta['SHORTDESCRIPTION'] = slenc(substr($meta['DESCRIPTION'],0,158));
		$meta['DESCRIPTION'] = slenc($meta['DESCRIPTION']);

		$episodeSize	= filesize($episodeBase . '.' . $type );
		$episodeMime	= $type == "ogg" ? "audio/ogg" : "audio/mpeg";

		echo <<<CASTENTRY

		<item>
			<title>{$meta[ 'TITLE' ]}</title>
			<pubDate>{$meta['PUBLISHED']}</pubDate>
			<itunes:duration>{$meta['DURATION']}</itunes:duration>
			<link>https://steamlug.org/cast/{$epi}</link>
			<guid>https://steamlug.org/cast/{$epi}</guid>
			<enclosure url="{$archiveBase}.{$type}" length="{$episodeSize}" type="{$episodeMime}" />
			<media:content url="{$archiveBase}.{$type}" fileSize="{$episodeSize}" type="{$episodeMime}" medium="audio" expression="full" />
			<itunes:explicit>no</itunes:explicit>
			<media:rating scheme="urn:simple">nonadult</media:rating>
			<description><![CDATA[<p>{$meta['DESCRIPTION']}</p>

CASTENTRY;
		foreach ( array_slice( $shownotes, 12 ) as $note)
		{
			$note = preg_replace_callback(
				'/\d+:\d+:\d+\s+\*(.*)\*/',
				function($matches){ return "<p>" . slenc($matches[1]) . "</p>\n<ul>"; },
				$note);
			$note = preg_replace_callback(
				'/(\d+:\d+:\d+)/',
				function($matches){ return "<time datetime='" . slenc($matches[1]) . "'>" . slenc($matches[1]) . "</time>"; },
				$note);
			$note = preg_replace_callback(
				'/^<time.*$/',
				function($matches){ return "<li>" . $matches[0] . "</li>"; },
				$note);
			/* TODO: recent episode included www.nordicgames.at which parses OK for us, but feed validators
				say no to relative URLs */
			$note = preg_replace_callback(
				'/(?i)\b((?:(https?|irc):\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«]))/',
				function($matches){ return "[<a href='" . slenc($matches[0]) . "'>" . slenc($matches[0]) . "</a>]"; },
				$note);
			$note = preg_replace_callback(
				'/(?<=^|\s)@([a-z0-9_]+)/i',
				function($matches){ return "<a href='http://twitter.com/" . slenc($matches[1]) . "'>" . slenc($matches[0]) . "</a>"; },
				$note);
			$note = preg_replace_callback(
				'/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/',
				function($matches){ return "<a href='mailto:". slenc($matches[0]) . "'>" . slenc($matches[0]) . "</a>"; },
				$note);
			$note = preg_replace_callback(
				'/^\n$/',
				function($matches){ return "</ul>\n"; },
				$note);
			$note = preg_replace_callback(
				'/\t\[(\w+)\](.*)/',
				function($matches){ return "<li>&lt;" . $matches[1] . "&gt; " . $matches[2] . "</li>"; },
				$note);
			$note = preg_replace_callback(
				'/\t(.*)/',
				function($matches){ return "<li>" . $matches[1] . "</li>"; },
				$note);
			$note = preg_replace_callback(
				'/  (.*)/',
				function($matches){ return "\t\t\t<p>" . $matches[1] . "</p>"; },
				$note);
			$note = preg_replace_callback(
				'/\[(\w\d+\w\d+)\]/',
				function($matches){ return "\t\t\t<a href='https://steamlug.org/cast/" . $matches[1] . "'>" . $matches[1] . "</a>\n"; },
				$note);
			echo $note;
		}
		echo <<<CASTENTRY
			]]></description>
			<itunes:subtitle>{$meta['SHORTDESCRIPTION']}…</itunes:subtitle>
		</item>
CASTENTRY;
	}
	echo <<<CASTFOOT
	</channel>
</rss>
CASTFOOT;
?>
