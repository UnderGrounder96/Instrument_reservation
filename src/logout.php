<?php
  require("../db/db.php");
  
  // start new or resume existing session
  session_start();

	// closes database connection
	$db->close();

	// remove all session variables
	session_unset();

	// destroy the session
  if(session_destroy())
    header("location: login.php");
?>
