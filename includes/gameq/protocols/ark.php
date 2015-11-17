<?php

class GameQ_Protocols_Ark extends GameQ_Protocols_Source
{
	protected $name = "ark";
	protected $name_long = "ARK: Survival Evolved";

	protected function process_status() {
		$result = parent::process_status();
		$result['steamappid'] = 376030;
		return $result;
	}
}
