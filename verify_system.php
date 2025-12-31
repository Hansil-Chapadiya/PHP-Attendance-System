<?php
/**
 * System Verification Script
 * Checks all database tables, fields, and API endpoints
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require __DIR__ . '/backend/config.php';

// Connect to database
$conn = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['name'],
    $config['database']['port']
);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "===========================================\n";
echo "   SYSTEM VERIFICATION REPORT\n";
echo "===========================================\n\n";

// 1. Check Database Tables
echo "1. DATABASE TABLES CHECK\n";
echo "-------------------------------------------\n";

$required_tables = [
    'user' => ['id', 'username', 'password', 'full_name', 'role', 'created_at'],
    'students' => ['id', 'user_id', 'branch', 'division', 'semester'],
    'faculty' => ['id', 'user_id', 'branch'],
    'classes' => ['id', 'class_id', 'faculty_id', 'branch', 'division', 'faculty_ip', 'created_at', 'expires_at'],
    'attendance' => ['id', 'user_id', 'class_id', 'date', 'status', 'student_ip', 'marked_time'],
    'rate_limit' => ['id', 'identifier', 'attempt_time']
];

$all_tables_ok = true;

foreach ($required_tables as $table => $expected_columns) {
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows === 0) {
        echo "❌ Table '$table' NOT FOUND\n";
        $all_tables_ok = false;
        continue;
    }
    
    echo "✓ Table '$table' exists\n";
    
    // Check columns
    $columns_result = $conn->query("DESCRIBE $table");
    $actual_columns = [];
    while ($row = $columns_result->fetch_assoc()) {
        $actual_columns[] = $row['Field'];
    }
    
    $missing_columns = array_diff($expected_columns, $actual_columns);
    if (!empty($missing_columns)) {
        echo "  ⚠ Missing columns: " . implode(', ', $missing_columns) . "\n";
        $all_tables_ok = false;
    } else {
        echo "  ✓ All required columns present (" . count($actual_columns) . " columns)\n";
    }
}

echo "\n";

// 2. Check API Files
echo "2. API FILES CHECK\n";
echo "-------------------------------------------\n";

$api_files = [
    'register_user.php' => 'User Registration',
    'stud_login.php' => 'Student Login',
    'faculty_login.php' => 'Faculty Login',
    'generate_id.php' => 'Generate Class ID',
    'mark_present.php' => 'Mark Attendance',
    'display_profile.php' => 'Display Profile',
    'show_attendance.php' => 'Show Attendance'
];

$all_files_ok = true;

foreach ($api_files as $file => $description) {
    $path = __DIR__ . '/api/' . $file;
    if (file_exists($path)) {
        echo "✓ $file ($description)\n";
    } else {
        echo "❌ $file NOT FOUND\n";
        $all_files_ok = false;
    }
}

echo "\n";

// 3. Test Database Operations
echo "3. DATABASE OPERATIONS TEST\n";
echo "-------------------------------------------\n";

// Test INSERT
$test_username = 'test_verify_' . time();
$test_password = password_hash('TestPass123', PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO user (username, password, full_name, role) VALUES (?, ?, ?, ?)");
$full_name = "Test User";
$role = "student";
$stmt->bind_param("ssss", $test_username, $test_password, $full_name, $role);

if ($stmt->execute()) {
    $test_user_id = $conn->insert_id;
    echo "✓ INSERT operation successful (user_id: $test_user_id)\n";
    
    // Test SELECT
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->bind_param("i", $test_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✓ SELECT operation successful\n";
    } else {
        echo "❌ SELECT operation failed\n";
    }
    
    // Test UPDATE
    $new_name = "Updated Test User";
    $stmt = $conn->prepare("UPDATE user SET full_name = ? WHERE id = ?");
    $stmt->bind_param("si", $new_name, $test_user_id);
    
    if ($stmt->execute()) {
        echo "✓ UPDATE operation successful\n";
    } else {
        echo "❌ UPDATE operation failed\n";
    }
    
    // Test DELETE (cleanup)
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $test_user_id);
    
    if ($stmt->execute()) {
        echo "✓ DELETE operation successful\n";
    } else {
        echo "❌ DELETE operation failed\n";
    }
} else {
    echo "❌ INSERT operation failed: " . $stmt->error . "\n";
}

echo "\n";

// 4. Check Table Relationships
echo "4. TABLE RELATIONSHIPS CHECK\n";
echo "-------------------------------------------\n";

// Check if we have any test data
$user_count = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
$student_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$faculty_count = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$class_count = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$attendance_count = $conn->query("SELECT COUNT(*) as count FROM attendance")->fetch_assoc()['count'];

echo "Users: $user_count\n";
echo "Students: $student_count\n";
echo "Faculty: $faculty_count\n";
echo "Classes: $class_count\n";
echo "Attendance Records: $attendance_count\n";

// Check foreign key integrity
if ($student_count > 0) {
    $orphaned = $conn->query("SELECT COUNT(*) as count FROM students s LEFT JOIN user u ON s.user_id = u.id WHERE u.id IS NULL")->fetch_assoc()['count'];
    if ($orphaned > 0) {
        echo "⚠ Warning: $orphaned orphaned student records found\n";
    } else {
        echo "✓ All student records properly linked\n";
    }
}

if ($faculty_count > 0) {
    $orphaned = $conn->query("SELECT COUNT(*) as count FROM faculty f LEFT JOIN user u ON f.user_id = u.id WHERE u.id IS NULL")->fetch_assoc()['count'];
    if ($orphaned > 0) {
        echo "⚠ Warning: $orphaned orphaned faculty records found\n";
    } else {
        echo "✓ All faculty records properly linked\n";
    }
}

echo "\n";

// 5. Check Indexes
echo "5. DATABASE INDEXES CHECK\n";
echo "-------------------------------------------\n";

$indexes_to_check = [
    'classes' => ['class_id'],
    'attendance' => ['user_id', 'class_id'],
    'rate_limit' => ['identifier']
];

foreach ($indexes_to_check as $table => $index_columns) {
    $result = $conn->query("SHOW INDEX FROM $table");
    $found_indexes = [];
    while ($row = $result->fetch_assoc()) {
        $found_indexes[] = $row['Column_name'];
    }
    
    foreach ($index_columns as $col) {
        if (in_array($col, $found_indexes)) {
            echo "✓ Index on $table.$col exists\n";
        } else {
            echo "⚠ Index on $table.$col missing (performance may be affected)\n";
        }
    }
}

echo "\n";

// 6. Configuration Check
echo "6. CONFIGURATION CHECK\n";
echo "-------------------------------------------\n";

echo "Database Host: " . $config['database']['host'] . "\n";
echo "Database Name: " . $config['database']['name'] . "\n";
echo "Database Port: " . $config['database']['port'] . "\n";
echo "JWT Secret: " . (isset($config['jwt']['secret']) && !empty($config['jwt']['secret']) ? "✓ Set" : "❌ Not set") . "\n";
echo "JWT Expiry: " . ($config['jwt']['expiry'] ?? 'Not set') . "\n";
echo "Rate Limit Max Attempts: " . ($config['rate_limit']['max_attempts'] ?? 'Not set') . "\n";
echo "Rate Limit Lockout Time: " . ($config['rate_limit']['lockout_time'] ?? 'Not set') . " seconds\n";

echo "\n";

// Summary
echo "===========================================\n";
echo "   SUMMARY\n";
echo "===========================================\n";

if ($all_tables_ok && $all_files_ok) {
    echo "✓ All database tables and API files present\n";
    echo "✓ System appears to be properly configured\n";
    echo "\nYou can now test the API endpoints!\n";
} else {
    echo "⚠ Some issues were found:\n";
    if (!$all_tables_ok) {
        echo "  - Database tables/columns missing\n";
    }
    if (!$all_files_ok) {
        echo "  - API files missing\n";
    }
    echo "\nPlease review the errors above.\n";
}

echo "===========================================\n";

$conn->close();
