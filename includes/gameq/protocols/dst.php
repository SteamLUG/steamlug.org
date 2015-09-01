<?php
/**
 * Don't Starve Together Protocol Class
 *
 * Note that the game port is the query port + 1
 */
class GameQ_Protocols_Dst extends GameQ_Protocols_Source
{
	protected $name = "dst";
	protected $name_long = "Don't Starve Together";

	protected function process_details() {
		$result = parent::process_details();
		$result['steamappid'] = 322330;
		return $result;
	}
}
