<?php
# needed locally only
// require '../vendor/autoload.php';

$Loader = (new josegonzalez\Dotenv\Loader('../.env'))
  ->parse()
  ->toEnv();

$db = new mysqli(
  $_ENV['DB_HOST'],
  $_ENV['DB_USER'],
  $_ENV['DB_PASSWORD'],
  $_ENV['DB_NAME'],
  $_ENV['DB_PORT']
)
  or die("Failed to connect to MySQL server: " . $db->connect_error);

#echo "Connected successfully";
#$db->close();
?>
