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
			// We *always* enforce this
			$conn->exec( 'SET NAMES utf8mb4;' );

		} catch(PDOException $e) {

			echo $e->getMessage();
		}
		return $conn;
	}

	function closeDB( $connection ) {

		$connection = null;
	}

	function logDB( $message ) {

		global $database;
		try {
			/* should this be called inside a transaction? or outside to record failed ones… */
			/* TODO: safe-ify $id */
			$statement = $database->prepare( "INSERT INTO steamlug.happenings (what) VALUES (?)" );
			$statement->execute( array( $message ) );
			$logmsg = $statement->fetch( PDO::FETCH_ASSOC );
			return $logmsg;
		} catch ( Exception $e ) {

			return false;
		}
	}

	/* TODO add a logging function here for the table happenings */
