<?php
include_once __DIR__."/config.php";
$connect = "mysql:host=" . DBHOST . ";dbname=" . DBNAME;

try {
    $pdo = new PDO($connect, DBUSER, DBPASSWD);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage();
    die();
}


$pdo->query("SET NAMES utf8_general_ci");