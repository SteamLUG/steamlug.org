<?php
include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

	function getClanSummaryDB( $clanid ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: safe-ify $clanid */
			$statement = $database->prepare( "SELECT * FROM clans WHERE clans.clanid = :clanid LIMIT 1;" );
			$statement->execute( array( 'clanid' => $clanid ) );
			$clan = $statement->fetch( PDO::FETCH_ASSOC );
			$database->commit( );
			return $clan;
		} catch ( Exception $e ) {

			return false;
		}
	}

	function findClanSummaryDB( $clanslug ) {

		global $database;
		try {
			$database->beginTransaction( );
			/* TODO: safe-ify $clanslug */
			$statement = $database->prepare( "SELECT * FROM clans WHERE clans.slug = :clanslug LIMIT 1;" );
			$statement->execute( array( 'clanslug' => $clanslug ) );
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
			$statement = $database->prepare( "SELECT steamid, role, clanroles.name AS clanrole FROM memberclans
				LEFT JOIN clans ON clans.clanid = memberclans.clanid
				LEFT JOIN clanroles ON memberclans.role = clanroles.roleid
				WHERE memberclans.clanid = :clanid ORDER BY role;" );
			$statement->execute( array( 'clanid' => $clanid ) );
			$clanmembers = $statement->fetchAll( PDO::FETCH_ASSOC );
			// $database->commit( );
			return $clanmembers;
		} catch ( Exception $e ) {

			return false;
		}
	}

	// add member to clan (default to member)
	/*
	XXX role default currently _hard coded_, needs to match db roleid for ‘Member’
	*/
	function addClanPlayerDB( $clanid, $steamid, $role = 8 ) {
	}
	// set member role in clan
	function setClanPlayerRoleDB( $clanid, $steamid, $role ) {
	}
	// remove member from clan
	function removeClanPlayerDB( $clanid, $steamid ) {
	}
	// invite to clan (subs to add member?)
	function inviteClanPlayerDB( $clanid, $steamid ) {
	}
	// request invite to clan (subs to add member?)
	function requestInviteClanPlayerDB( $clanid, $steamid ) {
	}

	// delete clan
	function removeClanDB( $clanid ) {
		/* XXX hardcode clanids to avoid removing (1-10) */
		/* should this just control visibility, in case of accidents? clan is autoassigned, so… repairing is harder */
		/* needs to remove all player ←→ clan relationships too */
	}
