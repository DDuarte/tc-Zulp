<?php
include_once 'config.php';

try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_db", $db_user, $db_pass);
    if (!isset($e)) $stat = "Connection to database was successfull.\n";
}
catch (PDOException $e)
{
    $stat = $e->getMessage();
}

?>