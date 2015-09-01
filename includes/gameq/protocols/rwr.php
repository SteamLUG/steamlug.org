<?php
/**
 * Running with Rifles Protocol Class
 *
 * Note that the query port is the server connect port + 1
 */
class GameQ_Protocols_Rwr extends GameQ_Protocols_Source
{
	protected $name = "rwr";
	protected $name_long = "Running with Rifles";

	protected function process_details() {
		$result = parent::process_details();
		$result['steamappid'] = 270150;
		return $result;
	}
}
