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
 * Serious Sam 3: BFE Protocol Class
 *
 * Note that the game port is the query port + 1
 */
class GameQ_Protocols_Sam3 extends GameQ_Protocols_Source
{
	protected $name = "sam3";
	protected $name_long = "Serious Sam 3: BFE";
	protected function process_details() {
		$result = parent::process_details();
		$result['steamappid'] = 41070;
		return $result;
	}

	/**
	 * Default port for this server type
	 *
	 * @var int
	 */
	protected $port = 27015;
}
