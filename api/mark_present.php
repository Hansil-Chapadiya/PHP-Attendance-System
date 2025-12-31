<?php
// Prevent output buffering and force JSON
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

// Handle CORS
CORSHelper::handleCORS();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Require student authentication
    $user = Auth::requireRole('student');
    $authenticated_user_id = $user['user_id'];

    // Get raw POST data and decode JSON input
    $postData = file_get_contents("php://input");
    $data = json_decode($postData, true);

    // Check if required fields are set
    if (!isset($data['class_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'class_id is required']);
        exit;
    }

    $class_id = $data['class_id'];

    // Get student details to verify branch/division match
    $stmt = $conn->prepare("SELECT s.branch, s.division FROM students s WHERE s.user_id = ?");
    $stmt->bind_param("i", $authenticated_user_id);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Student record not found']);
        exit;
    }

    $student_data = $student_result->fetch_assoc();
    $student_branch = $student_data['branch'];
    $student_division = $student_data['division'];
    $stmt->close();

    // Retrieve class details including faculty IP and expiry
    $stmt = $conn->prepare("SELECT branch, division, faculty_ip, faculty_in_charge, expires_at FROM `classes` WHERE class_id = ? LIMIT 1");
    $stmt->bind_param("s", $class_id);
    $stmt->execute();
    $class_result = $stmt->get_result();

    if ($class_result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Class session not found']);
        exit;
    }

    $class_data = $class_result->fetch_assoc();
    $faculty_ip = $class_data['faculty_ip'];
    $class_branch = $class_data['branch'];
    $class_division = $class_data['division'];
    $expires_at = $class_data['expires_at'];
    $stmt->close();

    // Check if class session has expired
    if (strtotime($expires_at) < time()) {
        http_response_code(410);
        echo json_encode(['status' => 'error', 'message' => 'Class session has expired']);
        exit;
    }

    // Verify student's branch and division match the class
    if ($student_branch !== $class_branch || $student_division !== $class_division) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You are not enrolled in this class']);
        exit;
    }

    // Get student's IP address
    $student_ip = NetworkHelper::getClientIP();

    // Validate if the student and faculty are on the same network
    if (!NetworkHelper::isSameSubnet($student_ip, $faculty_ip)) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'You must be connected to the same network as the faculty'
        ]);
        exit;
    }

    $current_date = date('Y-m-d');

    // Check if the student has already marked attendance for this class today
    $stmt = $conn->prepare("SELECT attendance_id FROM `attendance` WHERE user_id = ? AND class_id = ? AND date = ?");
    $stmt->bind_param("iss", $authenticated_user_id, $class_id, $current_date);
    $stmt->execute();
    $attendance_check = $stmt->get_result();

    if ($attendance_check->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'info', 'message' => 'Attendance already marked for this class today']);
        exit;
    }
    $stmt->close();

    // Mark attendance using prepared statement
    $marked_time = date('Y-m-d H:i:s');
    $status = 'present';
    
    $stmt = $conn->prepare("INSERT INTO `attendance` (user_id, class_id, date, status, marked_by, marked_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $authenticated_user_id, $class_id, $current_date, $status, $authenticated_user_id, $marked_time);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance marked successfully',
            'marked_time' => $marked_time,
            'class_id' => $class_id
        ]);
    } else {
        error_log("Attendance marking failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark attendance']);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
