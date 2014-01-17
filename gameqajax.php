<?php
	$host = (isset($_POST["host"])) ? $_POST["host"] : null;
	$port = (isset($_POST["port"])) ? $_POST["port"] : null;
	$type = (isset($_POST["type"])) ? $_POST["type"] : null;

	if ($host != null && $port != null && $type != null) {
		include_once("includes/GameQ.php");

		$gq = new GameQ();
		$gq->addServer(array(
			"type" => $type,
			"host" => $host . ":" . $port,
		));
		$gq->setOption("timeout", 1);
		$gq->setFilter("normalise");
		echo json_encode($gq->requestData());
	} else {
		die();
	}
?>
