<?php

$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];
$name = $_ENV['DB_NAME'];
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];

date_default_timezone_set('America/Mexico_City');
define('DB_USERNAME', $user);
define('DB_PASSWORD', $pass);
define('DB_HOST', $host);
define('DB_PORT', $port);
define('DB_NAME', $name);

define('URL_AUTH', $_ENV['AUTH_HOST']);
define('URL_METADATA', $_ENV['METADATA_HOST']);
?>