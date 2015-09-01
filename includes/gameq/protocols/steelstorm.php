<?php

class GameQ_Protocols_Steelstorm extends GameQ_Protocols_Quake3
{
	protected $name = "steelstorm";
	protected $name_long = "Steel Storm";

	protected function process_status() {
		$result = parent::process_status();
		$result['steamappid'] = 96200;
		return $result;
	}
}
