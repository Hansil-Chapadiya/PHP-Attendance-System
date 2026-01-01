<?php
// Clean any output buffers
while (ob_get_level()) ob_end_clean();

/**
 * Get Student Schedule API
 * Returns weekly timetable for a student's division
 * 
 * Method: GET
 * Auth: Required (Bearer token)
 * Returns: Weekly schedule with subjects per day
 */

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Force JSON content type
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../backend/helpers.php';
require_once __DIR__ . '/../backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Require authentication
$user = Auth::requireAuth();

// Verify user is a student
if ($user['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Only students can access this endpoint']);
    exit;
}

// Get student's division and semester
try {
    $stmt = $conn->prepare("SELECT division, semester FROM students WHERE user_id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();

    if (!$student) {
        http_response_code(404);
        echo json_encode(['error' => 'Student record not found', 'user_id' => $user['user_id']]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    exit;
}

$division = $student['division'];
$semester = $student['semester'] ?? 1; // Default to semester 1 if null

// Get weekly schedule for student's division and semester
try {
    $stmt = $conn->prepare("
        SELECT 
            s.day_of_week,
            s.subject,
            s.semester,
            s.time_slot,
            u.full_name as faculty_name
        FROM schedule s
        LEFT JOIN faculty f ON s.faculty_id = f.faculty_id
        LEFT JOIN user u ON f.user_id = u.user_id
        WHERE s.division = ? AND (s.semester = ? OR s.semester IS NULL)
        ORDER BY 
            FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            s.time_slot
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Schedule query error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("si", $division, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Schedule exception: ' . $e->getMessage()]);
    exit;
}

// Organize schedule by day
$schedule = [
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => [],
    'Saturday' => []
];

while ($row = $result->fetch_assoc()) {
    $schedule[$row['day_of_week']][] = [
        'subject' => $row['subject'],
        'semester' => $row['semester'],
        'time_slot' => $row['time_slot'],
        'faculty_name' => $row['faculty_name']
    ];
}

$stmt->close();

// Send response
http_response_code(200);
echo json_encode([
    'success' => true,
    'division' => $division,
    'semester' => $semester,
    'schedule' => $schedule
]);
