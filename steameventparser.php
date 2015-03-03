<?php
/* copyleft 2013 (CC-0) Melker "meklu" Narikka
 * copyleft 2013 (CC-0) Josh "Cheeseness" Bush
 *
 * This is a library that parses events for a Steam group
 * into a neat array. We pull dates but not times, since
 * those are really dodgy.
 *
 * You'll need DOM support for your copy of PHP. (See
 * http://www.php.net/manual/en/book.dom.php for more on this.)
 *
 * Basically we're pulling some XML from Steam, and then
 * doing awful hacky processing on it.
 *
 * The commented out parts might be useful for debugging
 * things in case the API breaks at some point, so I left
 * them there.
 */

/**
 * Parses Steam group events into arrays
 */
class SteamEventParser {
	/**
	 * Parses an event into an array
	 *
	 * @param string $str The HTML string from which to parse the event
	 * @param int $month The numeric month
	 * @param int $year The numeric year
	 * @param string $tzSrc The timezone in which the data's times are stored
	 * @param string $tzDest The timezone to convert to
	 * @return array An array of awesome stuff.
	 */
	private function parseEvent($str, $month, $year, $tzSrc, $tzDest) {
		$html = new DOMDocument();
		$html->loadHTML("<?xml encoding='UTF-8' ?>" . $str);
		$event = array();
		$node = $html->getElementsByTagName("body");
		foreach ($node as $body) {
			foreach ($body->childNodes as $node) {
				$_id = explode("_", $node->getAttribute("id"));
				$_id = $_id[0];
				foreach ($node->getElementsByTagName("div") as $subnode) {
					$class = $subnode->getAttribute("class");
					if ($class === "eventDateBlock") {
						// date
						$_date = explode(" ", $subnode->firstChild->textContent);
						$_date = (strlen($_date[1]) === 1) ? "0" . $_date[1] : (string) $_date[1];
						$_date = "$year-$month-" . $_date;
						$_time = $subnode->childNodes->item(2)->textContent;
					} elseif ($class === "playerAvatar") {
						// url, images
						$a = $subnode->firstChild;
						$_url = $a->getAttribute("href");
						$img = $a->firstChild;
						$_img_icon = $img->getAttribute("src");
						// relocate onto a // aware domain, cdn.akamai.steamstatic.com → steamcdn-a.akamaihd.net
						$_img_icon = str_replace( "http://cdn.akamai.steamstatic.com", "//steamcdn-a.akamaihd.net", $_img_icon);
						$_appid = explode("/", $_img_icon);
						$_appid = intval($_appid[count($_appid) - 2]);
						if ($_appid === 0) {
							$_img_header = "";
							$_img_header_small = "";
							$_img_capsule = "";
						} else {
							$_img_header = "//steamcdn-a.akamaihd.net/steam/apps/" . $_appid . "/header.jpg";
							$_img_header_small = "//steamcdn-a.akamaihd.net/steam/apps/" . $_appid . "/header_292x136.jpg";
							$_img_capsule = "//steamcdn-a.akamaihd.net/steam/apps/" . $_appid . "/capsule_sm_120.jpg";
						}
					} elseif ($class === "eventBlockTitle") {
						$l = $subnode->childNodes;
						foreach ($l as $a) {
							if ($a->nodeType === XML_ELEMENT_NODE) {
								if ($a->tagName === "a") {
									if ($a->getAttribute("class") === "headlineLink") {
										// title
										$a = $subnode->firstChild;
										$_title = $a->textContent;
									} else {
										// comment count
										$_comments = explode(" ", $a->textContent);
										$_comments = intval($_comments[0]);
									}
								}
							}
						}
					}
				}
			}
		}
		
		$tempDate = new DateTime($_date . " " . $_time, $tzSrc);
		$tempDate->setTimeZone($tzDest);

		$event["id"] = $_id;
		$event["url"] = $_url;
		$event["title"] = $_title;
		$event["comments"] = $_comments;
		$event["date"] = $tempDate->format("Y-m-d");
		$event["time"] = $tempDate->format("H:i");
		$event["tz"] = $tempDate->format("e");
		$event["appid"] = $_appid;
		$event["img_icon"] = $_img_icon;
		$event["img_capsule"] = $_img_capsule;
		$event["img_header"] = $_img_header;
		$event["img_header_small"] = $_img_header_small;
		return $event;
	}

	/**
	 * Generates the event data for a given month
	 *
	 * @param string $group The Steam group to get the data for
	 * @param int $month The numeric month
	 * @param int $year The numeric year
	 * @param bool $ssl Whether to use HTTPS for grabbing and displaying the data
	 * @param int $tries The amount of tries used for grabbing the data from Steam
	 * @param string $tz The timezone to convert the returned times to
	 * @return array An array of events
	 */
	public function genData($url, $group, $month = "", $year = "", $ssl = false, $tries = 3, $tz = "UTC") {
		//This is the time zone that events seem to be stored in
		$pst = new DateTimeZone("America/Los_Angeles");
		$tzDest = new DateTimeZone($tz);
		$month = (empty($month)) ? intval(gmstrftime("%m")) : $month;
		//$month = (strlen($month) === 1) ? "0" . $month : (string) $month;
		$year = (empty($year)) ? gmstrftime("%Y") : $year;
		// erk
		$url = ($ssl ? str_replace( "http:", "https:", $url ) : $url );
		$url.= $group . "/events_" . $month . "_" . $year . ".xml";
		// Setting the (upcoming) file handle to true for ultimate hackiness
		$f = true;
		// Checking robots.txt with rbt_prs (https://github.com/meklu/rbt_prs) if it's been included
		if (function_exists("isUrlBotSafe")) {
			if (!isUrlBotSafe($url)) {
				$f = false;
			}
		}
		if ($f) {
			do {
				$tries -= 1;
				$f = @fopen($url, "r");
				if ($f !== false) {
					break;
				}
			} while ($tries > 0);
		}
		if ($f === false) {
			return array("status" => false, "events" => array(), "pastevents" => array(),);
		}
		$str = stream_get_contents($f);
		fclose($f);
		$xml = new DOMDocument();
		$xml->loadXML($str);
		$events = array();
		$pastevents = array();
		foreach ($xml->getElementsByTagName("event") as $e) {
			$events[] = $this->parseEvent($e->nodeValue, $month, $year, $pst, $tzDest);
		}
		foreach ($xml->getElementsByTagName("expiredEvent") as $e) {
			$pastevents[] = $this->parseEvent($e->nodeValue, $month, $year, $pst, $tzDest);
		}
		return array("status" => true, "events" => $events, "pastevents" => $pastevents);
	}
}
?>
