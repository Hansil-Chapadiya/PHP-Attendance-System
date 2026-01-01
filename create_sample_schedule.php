<?php
/**
 * Create Sample Schedule Data
 * Run this to populate the schedule table with sample data for testing
 * 
 * Usage: php create_sample_schedule.php
 */

require_once __DIR__ . '/backend/db_connect.php';

echo "Creating sample schedule data...\n\n";

// Sample schedules for different divisions
$sampleSchedules = [
    // Division A - Semester 1
    ['Monday', 'Data Science', 'A', 1, '9:00 AM - 10:00 AM', 1],
    ['Monday', 'Python Programming', 'A', 1, '10:00 AM - 11:00 AM', 1],
    ['Monday', 'C++ Basics', 'A', 1, '11:00 AM - 12:00 PM', 2],
    
    ['Tuesday', 'C Programming', 'A', 1, '9:00 AM - 10:00 AM', 2],
    ['Tuesday', 'Algorithms', 'A', 1, '10:00 AM - 11:00 AM', 1],
    ['Tuesday', 'Data Structures', 'A', 1, '11:00 AM - 12:00 PM', 2],
    
    ['Wednesday', 'Python Programming', 'A', 1, '9:00 AM - 10:00 AM', 1],
    ['Wednesday', 'Database Management', 'A', 1, '10:00 AM - 11:00 AM', 2],
    
    // Division A - Semester 2
    ['Monday', 'Web Development', 'A', 2, '9:00 AM - 10:00 AM', 1],
    ['Monday', 'Machine Learning', 'A', 2, '10:00 AM - 11:00 AM', 2],
    
    ['Thursday', 'Data Science', 'A', 2, '9:00 AM - 10:00 AM', 1],
    ['Thursday', 'Computer Networks', 'A', 2, '10:00 AM - 11:00 AM', 1],
    ['Thursday', 'C++ Advanced', 'A', 2, '11:00 AM - 12:00 PM', 2],
    
    ['Friday', 'Algorithms', 'A', 2, '9:00 AM - 10:00 AM', 1],
    ['Friday', 'Operating Systems', 'A', 2, '10:00 AM - 11:00 AM', 2],
    
    // Division B - Semester 1
    ['Monday', 'C++ Basics', 'B', 1, '9:00 AM - 10:00 AM', 2],
    ['Monday', 'Algorithms', 'B', 1, '10:00 AM - 11:00 AM', 1],
    ['Monday', 'Data Structures', 'B', 1, '11:00 AM - 12:00 PM', 2],
    
    ['Tuesday', 'Python Programming', 'B', 1, '9:00 AM - 10:00 AM', 1],
    ['Tuesday', 'Data Science', 'B', 1, '10:00 AM - 11:00 AM', 1],
    
    // Division B - Semester 2
    ['Wednesday', 'C Programming', 'B', 2, '9:00 AM - 10:00 AM', 2],
    ['Wednesday', 'Database Management', 'B', 2, '11:00 AM - 12:00 PM', 2],
    
    ['Thursday', 'Machine Learning', 'B', 2, '9:00 AM - 10:00 AM', 2],
    ['Thursday', 'Python Advanced', 'B', 2, '10:00 AM - 11:00 AM', 1],
    ['Thursday', 'Computer Networks', 'B', 2, '11:00 AM - 12:00 PM', 1],
    
    ['Friday', 'Web Development', 'B', 2, '9:00 AM - 10:00 AM', 1],
    ['Friday', 'C++ Advanced', 'B', 2, '10:00 AM - 11:00 AM', 2],
    ['Friday', 'Operating Systems', 'B', 2, '11:00 AM - 12:00 PM', 2],
    
    // Division C - Semester 4 (PHP classes)
    ['Monday', 'PHP Web Development', 'C', 4, '9:00 AM - 10:00 AM', 1],
    ['Monday', 'Laravel Framework', 'C', 4, '10:00 AM - 11:00 AM', 1],
    
    ['Tuesday', 'PHP Advanced', 'C', 4, '9:00 AM - 10:00 AM', 1],
    ['Tuesday', 'MySQL Database', 'C', 4, '10:00 AM - 11:00 AM', 2],
    
    ['Wednesday', 'RESTful APIs', 'C', 4, '9:00 AM - 10:00 AM', 1],
    ['Wednesday', 'Full Stack Development', 'C', 4, '11:00 AM - 12:00 PM', 2],
];

// Clear existing schedule data
$conn->query("TRUNCATE TABLE schedule");
echo "Cleared existing schedule data\n\n";

// Insert sample schedules
$stmt = $conn->prepare("
    INSERT INTO schedule (day_of_week, subject, division, semester, time_slot, faculty_id) 
    VALUES (?, ?, ?, ?, ?, ?)
");

$successCount = 0;
$errorCount = 0;

foreach ($sampleSchedules as $schedule) {
    list($day, $subject, $division, $semester, $timeSlot, $facultyId) = $schedule;
    
    $stmt->bind_param("sssisi", $day, $subject, $division, $semester, $timeSlot, $facultyId);
    
    if ($stmt->execute()) {
        $successCount++;
        echo "✓ Added: $day - Sem $semester - $subject for Division $division\n";
    } else {
        $errorCount++;
        echo "✗ Error: " . $stmt->error . "\n";
    }
}

$stmt->close();

echo "\n========================================\n";
echo "Sample schedule creation complete!\n";
echo "Successful: $successCount\n";
echo "Errors: $errorCount\n";
echo "========================================\n\n";

// Show summary by division and semester
echo "Schedule Summary:\n\n";

$result = $conn->query("
    SELECT division, semester, COUNT(*) as class_count 
    FROM schedule 
    GROUP BY division, semester
    ORDER BY division, semester
");

while ($row = $result->fetch_assoc()) {
    echo "Division {$row['division']} - Semester {$row['semester']}: {$row['class_count']} classes\n";
}

echo "\n";

// Show faculty teaching load
echo "Faculty Teaching Load:\n\n";

$result = $conn->query("
    SELECT 
        f.name as faculty_name,
        COUNT(s.id) as class_count,
        GROUP_CONCAT(DISTINCT s.subject ORDER BY s.subject) as subjects
    FROM schedule s
    LEFT JOIN faculty f ON s.faculty_id = f.id
    GROUP BY s.faculty_id
    ORDER BY faculty_name
");

while ($row = $result->fetch_assoc()) {
    echo "{$row['faculty_name']}: {$row['class_count']} classes\n";
    echo "  Subjects: {$row['subjects']}\n\n";
}

$conn->close();

echo "You can now view the schedules in student and faculty dashboards!\n";
