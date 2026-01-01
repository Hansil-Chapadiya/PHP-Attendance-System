<?php
// Clean any output buffers
while (ob_get_level()) ob_end_clean();

/**
 * Get Faculty Attendance Records API
 * Returns attendance records for faculty's classes
 * 
 * Method: GET
 * Auth: Required (Bearer token)
 * Returns: Attendance records grouped by date, division, subject
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

// Verify user is a faculty
if ($user['role'] !== 'faculty') {
    http_response_code(403);
    echo json_encode(['error' => 'Only faculty can access this endpoint']);
    exit;
}

// Get faculty ID
try {
    $stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE user_id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();

    if (!$faculty) {
        http_response_code(404);
        echo json_encode(['error' => 'Faculty record not found']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    exit;
}

$faculty_id = $faculty['faculty_id'];

// Get attendance records for this faculty's classes
try {
    $stmt = $conn->prepare("
        SELECT 
            a.date,
            a.marked_time,
            c.branch,
            c.division,
            c.subject,
            s.semester,
            u.full_name as student_name,
            u.username as student_username,
            DAYNAME(a.date) as day_of_week
        FROM attendance a
        JOIN classes c ON a.class_id = c.class_id
        JOIN students s ON a.user_id = s.user_id
        JOIN user u ON a.user_id = u.user_id
        WHERE c.faculty_in_charge = ?
        ORDER BY a.date DESC, c.division, a.marked_time DESC
        LIMIT 100
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Query error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    exit;
}

// Organize attendance records
$records = [];
while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    $division = $row['division'];
    
    if (!isset($records[$date])) {
        $records[$date] = [
            'date' => $date,
            'day_of_week' => $row['day_of_week'],
            'divisions' => []
        ];
    }
    
    if (!isset($records[$date]['divisions'][$division])) {
        $records[$date]['divisions'][$division] = [
            'division' => $division,
            'branch' => $row['branch'],
            'subject' => $row['subject'],
            'students' => []
        ];
    }
    
    $records[$date]['divisions'][$division]['students'][] = [
        'name' => $row['student_name'],
        'username' => $row['student_username'],
        'semester' => $row['semester'],
        'marked_time' => $row['marked_time']
    ];
}

// Convert to indexed array
$records = array_values($records);
foreach ($records as &$record) {
    $record['divisions'] = array_values($record['divisions']);
}

$stmt->close();

// Send response
http_response_code(200);
echo json_encode([
    'success' => true,
    'records' => $records
]);
