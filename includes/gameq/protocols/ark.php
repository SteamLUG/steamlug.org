<?php

class GameQ_Protocols_Steelstorm extends GameQ_Protocols_Quake3
{
	protected $name = "ark";
	protected $name_long = "ARK: Survival Evolved";

	protected function process_status() {
		$result = parent::process_status();
		$result['steamappid'] = 376030;
		return $result;
	}
}
