<?php
require_once("../db/db.php");

// start new or resume existing session
session_start();

// storing session
$username = $_SESSION["login_user"];

// SQL Query To Fetch Complete Information Of User
$result = $db->query("SELECT username FROM users WHERE username='{$username}';")
  or die("Failed to run query: (" . $db->errno . ") " . $db->error);

// associative array
$row = $result->fetch_assoc();

// in case not logged in
if (!isset($row["username"]))
  header("location: logout.php");
?>
