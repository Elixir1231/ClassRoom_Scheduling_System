<?php
// Include the database connection file
include 'connect.php';

// Check if the semester ID is set
if (!isset($_GET['semester_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Semester ID is not set']));
}

// Get the semester ID
$semester_id = $_GET['semester_id'];

// Prepare the query
$sql = "SELECT s.schedule_id, r.room_name, s.subject_name, s.professor_name, s.start_time, s.end_time, s.day_of_week, c.course_name
        FROM schedules s
        JOIN rooms r ON s.room_id = r.room_id
        JOIN course c ON s.courseID = c.courseID
        WHERE s.semester_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => 'Error preparing statement: ' . $conn->error]));
}

// Bind the semester ID parameter
$stmt->bind_param("i", $semester_id);
if (!$stmt->execute()) {
    die(json_encode(['status' => 'error', 'message' => 'Error executing statement: ' . $stmt->error]));
}

// Get the result
$result = $stmt->get_result();
if (!$result) {
    die(json_encode(['status' => 'error', 'message' => 'Error getting result: ' . $stmt->error]));
}

// Fetch the data
$schedule = [];
while ($row = $result->fetch_assoc()) {
    $schedule[$row['day_of_week']][] = $row;
}

// Close the statement
$stmt->close();

// Return the data as JSON
echo json_encode([
    'status' => 'success',
    'schedule' => $schedule
]);
?> 