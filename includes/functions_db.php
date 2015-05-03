<?php
	include_once('creds.php');

	function getPDODrv( ) {
		return "mysql";
	}

	function getPDODrvList( ) {

		$drivers = array( );
		foreach( PDO::getAvailableDrivers( ) as $d ) {

			$drivers[] = $d;
		}
		return $drivers;
	}

	function connectDB( ) {

		$conn = null;
		try {

			$conn = new PDO( getPDODrv() . ":dbname=" . getDBName() . ";host=" . getDBHost(), getDBUser(), getDBPass());
			$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//echo "PDO connection object created";

		} catch(PDOException $e) {

			echo $e->getMessage();
		}
		return $conn;
	}

	function closeDB( $connection ) {

		// er?
		$connection = null;
	}
