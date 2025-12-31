<?php
// Quick Test User Creator - Known Password
require_once __DIR__ . '/backend/db_connect.php';

echo "🧪 Test User Creator\n";
echo "===================\n\n";

// Test credentials (CHANGE THESE if needed)
$test_users = [
    [
        'username' => 'student1',
        'password' => 'Pass@123',  // Plain password
        'full_name' => 'Test Student One',
        'role' => 'student',
        'branch' => 'Computer Science',
        'division' => 'A',
        'semester' => 5
    ],
    [
        'username' => 'faculty1',
        'password' => 'Pass@123',  // Plain password
        'full_name' => 'Prof. Test Faculty',
        'role' => 'faculty',
        'branch' => 'Computer Science'
    ]
];

foreach ($test_users as $user) {
    echo "Creating {$user['role']}: {$user['username']}\n";
    
    // Check if user exists
    $check = $conn->prepare("SELECT user_id FROM `user` WHERE username = ?");
    $check->bind_param("s", $user['username']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // User exists - update password
        $existing_user = $result->fetch_assoc();
        $user_id = $existing_user['user_id'];
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE `user` SET password = ?, full_name = ? WHERE user_id = ?");
        $update->bind_param("ssi", $hashed, $user['full_name'], $user_id);
        $update->execute();
        
        echo "  ✅ Updated existing user\n";
        echo "  📧 Username: {$user['username']}\n";
        echo "  🔑 Password: {$user['password']}\n\n";
        
        $update->close();
    } else {
        // Create new user
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        
        $insert = $conn->prepare("INSERT INTO `user` (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $user['username'], $hashed, $user['full_name'], $user['role']);
        
        if ($insert->execute()) {
            $user_id = $conn->insert_id;
            echo "  ✅ Created new user (ID: {$user_id})\n";
            
            // Add to student/faculty table
            if ($user['role'] === 'student') {
                $student_insert = $conn->prepare("INSERT INTO `students` (user_id, branch, division, semester) VALUES (?, ?, ?, ?)");
                $student_insert->bind_param("issi", $user_id, $user['branch'], $user['division'], $user['semester']);
                $student_insert->execute();
                $student_insert->close();
                echo "  📚 Added to students table\n";
            } else {
                $faculty_insert = $conn->prepare("INSERT INTO `faculty` (user_id, branch) VALUES (?, ?)");
                $faculty_insert->bind_param("is", $user_id, $user['branch']);
                $faculty_insert->execute();
                $faculty_insert->close();
                echo "  👨‍🏫 Added to faculty table\n";
            }
            
            echo "  📧 Username: {$user['username']}\n";
            echo "  🔑 Password: {$user['password']}\n\n";
        } else {
            echo "  ❌ Failed to create user\n\n";
        }
        
        $insert->close();
    }
    
    $check->close();
}

echo "===================\n";
echo "✅ Test users ready!\n\n";
echo "🔐 Login Credentials:\n";
echo "━━━━━━━━━━━━━━━━━━━━\n";
echo "Student Login:\n";
echo "  Username: student1\n";
echo "  Password: Pass@123\n\n";
echo "Faculty Login:\n";
echo "  Username: faculty1\n";
echo "  Password: Pass@123\n";
echo "━━━━━━━━━━━━━━━━━━━━\n";

$conn->close();
?>
