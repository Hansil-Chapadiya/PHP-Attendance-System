<?php
$host = "HansilGreat.mysql.pythonanywhere-services.com"; // Your PythonAnywhere MySQL host
$db_name = "HansilGreat\$attendance_system_php_mini"; // Your database name
$username = "HansilGreat"; // Your PythonAnywhere username
$password = "alex@123"; // Your MySQL database password

$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";

// $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// include_once('login.php');
