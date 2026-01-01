<?php
// Clean any output buffers
while (ob_get_level()) ob_end_clean();

/**
 * Get Faculty Schedule API
 * Returns weekly teaching schedule for a faculty member
 * 
 * Method: GET
 * Auth: Required (Bearer token)
 * Returns: Weekly schedule with subjects and divisions
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
        echo json_encode(['error' => 'Faculty record not found', 'user_id' => $user['user_id']]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    exit;
}

$faculty_id = $faculty['faculty_id'];

// Get weekly schedule for this faculty
try {
    $stmt = $conn->prepare("
        SELECT 
            day_of_week,
            subject,
            division,
            semester,
            time_slot
        FROM schedule
        WHERE faculty_id = ?
        ORDER BY 
            semester,
            FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            time_slot
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Schedule query error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $faculty_id);
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
        'division' => $row['division'],
        'semester' => $row['semester'],
        'time_slot' => $row['time_slot']
    ];
}

$stmt->close();

// Send response
http_response_code(200);
echo json_encode([
    'success' => true,
    'schedule' => $schedule
]);
