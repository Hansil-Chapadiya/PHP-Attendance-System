<?php
// $host = "HansilGreat.mysql.pythonanywhere-services.com"; // Your PythonAnywhere MySQL host
// $db_name = "HansilGreat$attendance_system_php_mini"; // Your database name
// $username = "HansilGreat"; // Your PythonAnywhere username
// $password = "hansil714"; // Your MySQL database password

// $host = "localhost:3307"; // Your PythonAnywhere MySQL host
// $db_name = "attendance_system_php_mini"; // Your database name
// $username = "root"; // Your PythonAnywhere username
// $password = ""; // Your MySQL database password

$host = "sql12.freesqldatabase.com"; // Your PythonAnywhere MySQL host
$db_name = "sql12736545"; // Your database name
$username = "sql12736545"; // Your PythonAnywhere username
$password = "Y9mLJm5bKB"; // Your MySQL database password
/*
Host: sql12.freesqldatabase.com
Database name: sql12735701
Database user: sql12735701
Database password: qKJr15MSCX
Port number: 3306
*/

$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// echo "Connected successfully";

// $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// include_once('login.php');
