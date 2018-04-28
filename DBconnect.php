<?php

$servername = 'localhost';
$username = '[USERNAME]';
$password = '[PASSWORD]';
$dbname = '[DB_NAME]';

// Create connection
$dbConn = new mysqli($servername, $username, $password, $dbname, 3306);
// Check connection
if ($dbConn->connect_error) {
    die('Connection DB failed');
}
