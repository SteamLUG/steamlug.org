<?php
include_once( 'functions_db.php' );

if ( !isset( $database ) )
	$database = connectDB( );

// getClanSummaryDB( clanid )
// findClanSummaryDB( name )
// getClanPlayersDB( clanid )
// getPlayerClansDB( steamid )
