<?php
/**
 * This file is part of GameQ.
 *
 * GameQ is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GameQ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Windward Protocol Class
 *
 * Result from this call should be a text over HTTP
 *
 * @author Jason Rivers <jason@jasonrivers.co.uk>
 */
class GameQ_Protocols_Windward extends GameQ_Protocols_Http
{
    /**
     * Array of packets we want to look up.
     * Each key should correspond to a defined method in this or a parent class
     *
     * @var array
     */
    protected $packets = array(
            self::PACKET_STATUS => "GET / HTTP/1.0\r\nAccept: */*\r\n\r\n",
    );

    /**
     * Methods to be run when processing the response(s)
     *
     * @var array
     */
    protected $process_methods = array(
            "process_status",
    );

    /**
     * The protocol being used
     *
     * @var string
     */
    protected $protocol = 'tnet';

    /**
     * String name of this protocol class
     *
     * @var string
     */
    protected $name = 'windward';

    /**
     * Longer string name of this protocol class
     *
     * @var string
     */
    protected $name_long = "Windward";

    /*
     * Internal methods
     */
    protected function preProcess_status($packets=array())
    {
	// Split on newline
	$m = explode("\n", $packets[0]);

        return $m;
    }

    protected function process_status()
    {
        // Make sure we have a valid response
        if(!$this->hasValidResponse(self::PACKET_STATUS))
        {
            return array();
        }
        $res = ($this->preProcess_status($this->packets_response[self::PACKET_STATUS]));


	// Loop through the array to get the server name and the number of players.
	$playerCount=0;
	foreach ($res as $i => $v) {

		if (strstr($v, "Name")) {
			$server_name_index=$i;
			$server_name=str_replace("Name: ", "", $v);
		}

		// Server always shows clients even when there's no player connected.
		// So we only do this after we've found the "Clients:" line
		if (isset($hasClients) && $hasClients > 0) {
			if ($v != "") {
				$playerCount++;
			}
		}

		if (strstr($v, "Clients:")) {
			$hasClients = str_replace("Clients: ", "", $v);
		}

	}



        // Set the result to a new result instance
        $result = new GameQ_Result();

        // Server is always dedicated
        $result->add('dedicated', TRUE);

        // No mods, as of yet
        $result->add('mod', FALSE);

        $result->add('hostname', $server_name);
        $result->add('numplayers', $playerCount);
	// Server doesn't give use this maxplayers, Here is the Dev's wording on the matter:
	// Challenge 99. Max loot is 100, max challenge is 99. I've had my dev server up to ~25 players at one point.
	// 0.2% CPU usage (of a single core out of 12), 96 MB RAM usage.
        $result->add('maxplayers', 99);
	$result->add('steamappid', 326410);
	// Server doesn't report the map that it has generated, so we don't have a blank on the page we're going for "world"
        $result->add('map', "world");

        return $result->fetch();
    }
}
