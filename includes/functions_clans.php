<?php
include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

	function getClanSummaryDB( $clanid ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: safe-ify $clanid */
			$statement = $database->prepare( "SELECT * FROM steamlug.clans WHERE clans.clanid = ? LIMIT 1;" );
			$statement->execute( array( $clanid ) );
			$clan = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $clan;
		} catch ( Exception $e ) {

			return false;
		}
	}

	function findClanSummaryDB( $slug ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: safe-ify $slug */
			$statement = $database->prepare( "SELECT * FROM steamlug.clans WHERE clans.slug = ? LIMIT 1;" );
			$statement->execute( array( $slug ) );
			$clan = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $clan;
		} catch ( Exception $e ) {

			return false;
		}
	}

	function getClanPlayersDB( $clanid ) {

		global $database;
		try {
			// $database->beginTransaction( );
			/* TODO: safe-ify $clanid */
			$statement = $database->prepare( "SELECT steamid, role, clanroles.name AS clanrole FROM steamlug.memberclans
				LEFT JOIN clans ON clans.clanid = memberclans.clanid
				LEFT JOIN clanroles ON memberclans.role = clanroles.roleid
				WHERE memberclans.clanid = ? ORDER BY role;" );
			$statement->execute( array( $clanid ) );
			$clanmembers = $statement->fetchAll( PDO::FETCH_ASSOC );
			// $database->commit( );
			return $clanmembers;
		} catch ( Exception $e ) {

			return false;
		}
	}
