<?php
// CREATE TEST USERS ON INFINITYFREE
// Upload this file to InfinityFree and run it ONCE, then DELETE it

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/backend/db_connect.php';

echo "<pre>";
echo "Creating Test Users on InfinityFree Database\n";
echo "===========================================\n\n";

// Create test student
$username = 'student1';
$password = password_hash('Pass@123', PASSWORD_DEFAULT);
$full_name = 'Test Student One';
$role = 'student';

// Delete if exists
$conn->query("DELETE FROM `user` WHERE username = '$username'");

// Insert user
$stmt = $conn->prepare("INSERT INTO `user` (username, password, full_name, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $password, $full_name, $role);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    echo "✅ Student created! ID: $user_id\n";
    
    // Insert into students table
    $conn->query("DELETE FROM students WHERE user_id = $user_id");
    $stmt2 = $conn->prepare("INSERT INTO students (user_id, branch, division, semester) VALUES (?, 'Computer Science', 'A', 3)");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    echo "   Added to students table\n\n";
} else {
    echo "❌ Failed to create student: " . $stmt->error . "\n\n";
}

// Create test faculty
$username = 'faculty1';
$password = password_hash('Pass@123', PASSWORD_DEFAULT);
$full_name = 'Test Faculty One';
$role = 'faculty';

$conn->query("DELETE FROM `user` WHERE username = '$username'");

$stmt = $conn->prepare("INSERT INTO `user` (username, password, full_name, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $password, $full_name, $role);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    echo "✅ Faculty created! ID: $user_id\n";
    
    // Insert into faculty table
    $conn->query("DELETE FROM faculty WHERE user_id = $user_id");
    $stmt2 = $conn->prepare("INSERT INTO faculty (user_id, department) VALUES (?, 'Computer Science')");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    echo "   Added to faculty table\n\n";
} else {
    echo "❌ Failed to create faculty: " . $stmt->error . "\n\n";
}

echo "===========================================\n";
echo "✅ Test users ready!\n\n";
echo "🔐 Login Credentials:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Student Login:\n";
echo "  Username: student1\n";
echo "  Password: Pass@123\n\n";
echo "Faculty Login:\n";
echo "  Username: faculty1\n";
echo "  Password: Pass@123\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "⚠️  DELETE THIS FILE NOW FOR SECURITY!\n";
echo "</pre>";
