<?php
// Quick Fix: Update existing user passwords to hashed format
// Run this ONCE to fix database passwords

require_once __DIR__ . '/backend/db_connect.php';

echo "🔧 Password Hash Fix Tool\n";
echo "========================\n\n";

// Get all users with plain text passwords
$stmt = $conn->prepare("SELECT user_id, username, password, role FROM `user`");
$stmt->execute();
$result = $stmt->get_result();

$updated_count = 0;
$skipped_count = 0;

while ($user = $result->fetch_assoc()) {
    // Check if password is already hashed (bcrypt hashes start with $2y$)
    if (substr($user['password'], 0, 4) === '$2y$' || substr($user['password'], 0, 4) === '$2a$') {
        echo "⏭️  Skipping {$user['username']} - already hashed\n";
        $skipped_count++;
        continue;
    }

    // Hash the plain text password
    $plain_password = $user['password'];
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // Update the password
    $update_stmt = $conn->prepare("UPDATE `user` SET password = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user['user_id']);
    
    if ($update_stmt->execute()) {
        echo "✅ Updated {$user['username']} ({$user['role']}) - Password: {$plain_password}\n";
        $updated_count++;
    } else {
        echo "❌ Failed to update {$user['username']}\n";
    }
    
    $update_stmt->close();
}

$stmt->close();
$conn->close();

echo "\n========================\n";
echo "✅ Updated: {$updated_count} users\n";
echo "⏭️  Skipped: {$skipped_count} users (already hashed)\n";
echo "\nNOTE: All plain text passwords have been hashed.\n";
echo "Users can now login with their original passwords.\n";
?>
