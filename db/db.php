<?php
# needed locally only
if(!getenv('APP_NAME')){
  require '../vendor/autoload.php';

  $Loader = (new josegonzalez\Dotenv\Loader('../.env'))
    ->parse()
    ->skipExisting()
    ->putenv();
}

$db = new mysqli(
  getenv('DB_HOST'),
  getenv('DB_USER'),
  getenv('DB_PASSWORD'),
  getenv('DB_NAME'),
  getenv('DB_PORT')
)
  or die("Failed to connect to MySQL server: " . $db->connect_error);

#echo "Connected successfully";
#$db->close();
?>