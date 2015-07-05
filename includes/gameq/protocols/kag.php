<?php
/**
 * King Arthur's Gold Protocol Class
 *
 * Note that the query port is the server connect port + 1
 */
class GameQ_Protocols_Kag extends GameQ_Protocols_Source
{
	protected $name = "kag";
	protected $name_long = "King Arthur's Gold";

	protected function process_details() {
		$result = parent::process_details();
		$result['steamappid'] = 219830;
		return $result;
	}
}
