<?php
session_start();
include '../db_connect.php';  // Database connection file
var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    echo $username;

    // Query to validate login credentials
    $query = "SELECT * FROM `user` WHERE `username` = '$username' AND `role` = 'student'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Check if the password matches (assuming it is hashed)
        // if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['status' => 'success', 'message' => 'Login successful']);
        // } else {
        // echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        // }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or not a student']);
    }
}
