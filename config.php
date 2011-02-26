<?php
$cache_check_time = 2592000; // (1 month) it will check wowhead if cache is older than this time
$hostname = 'localhost';
$username = 'xxx';
$password = 'xxx';
$database = 'world';
try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    if (!isset($e)) $stat = "Connection to <i>$hostname</i>, <i>$database</i> database with username <i>$username</i> successfull.\n";
}
catch (PDOException $e)
{
    $stat = $e->getMessage();
}

$walkBase = 2.5; // Walk speed base value
$runBase = 8.0; // Run speed base value
?>