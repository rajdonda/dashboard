<?php

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location:login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = new mysqli("localhost", "root", "", "pages", 3306);

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}
