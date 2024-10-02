<?php
$host = "localhost:3307";
$db_name = "attendance_system_php_mini";
$username = "root";
$password = "";

// $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$conn = mysqli_connect($host, $username, $password, $db_name);
// include_once('login.php');
?>