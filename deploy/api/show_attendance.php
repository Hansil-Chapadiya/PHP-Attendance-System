<?php
// CORS headers FIRST (before any other code)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Prevent output buffering and force JSON
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Require authentication
    $user = Auth::requireAuth();
    $authenticated_user_id = $user['user_id'];

    // Allow users to view their own attendance, or specify user_id if faculty
    $requested_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $authenticated_user_id;

    // Only allow users to view their own attendance unless they're faculty
    if ($user['role'] !== 'faculty' && $requested_user_id !== $authenticated_user_id) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Access forbidden']);
        exit;
    }

    // Use prepared statement to get attendance records
    $stmt = $conn->prepare("
        SELECT 
            a.attendance_id, 
            a.class_id, 
            a.date, 
            a.status, 
            a.marked_time, 
            c.branch, 
            c.division,
            c.subject,
            s.semester
        FROM `attendance` a 
        LEFT JOIN `classes` c ON a.class_id = c.class_id 
        LEFT JOIN `students` s ON a.user_id = s.user_id
        WHERE a.user_id = ? 
        ORDER BY a.date DESC, a.marked_time DESC
    ");
    $stmt->bind_param("i", $requested_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attendance_records = [];
        while ($row = $result->fetch_assoc()) {
            $attendance_records[] = $row;
        }
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $attendance_records, 'count' => count($attendance_records)]);
    } else {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => [], 'count' => 0, 'message' => 'No attendance records found']);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
