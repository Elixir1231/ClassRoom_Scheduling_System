<?php
include 'connect.php';

// Set the timezone to Philippine time
date_default_timezone_set('Asia/Manila');

// Get current day and time in the Philippine timezone
$current_day = date('l');
$current_time = date('H:i:s');

// Check if a semester_id is provided via GET request
$semester_id = isset($_GET['semester_id']) ? $_GET['semester_id'] : null;

// Prepare the SQL query to fetch room status based on the selected semester and current time
$sql = "SELECT r.room_id, r.room_name, 
               s.subject_name, s.professor_name, s.start_time, s.end_time, s.semester_id,
               CASE 
                 WHEN s.day_of_week = ? AND ? BETWEEN s.start_time AND s.end_time THEN 1
                 ELSE 0
               END AS is_occupied
        FROM rooms r
        LEFT JOIN schedules s ON r.room_id = s.room_id";

// If a semester is selected, filter by semester_id
if ($semester_id) {
    $sql .= " WHERE s.semester_id = ?";  // Add the semester condition to the query
}

$sql .= " ORDER BY r.room_name";

$stmt = $conn->prepare($sql);

// Bind parameters based on whether a semester_id is provided
if ($semester_id) {
    $stmt->bind_param("sss", $current_day, $current_time, $semester_id); // Bind current day, time, and semester
} else {
    $stmt->bind_param("ss", $current_day, $current_time); // Only bind current day and time
}

$stmt->execute();
$result = $stmt->get_result();

$rooms = array();
while ($row = $result->fetch_assoc()) {
    $room_id = $row['room_id'];
    if (!isset($rooms[$room_id])) {
        $rooms[$room_id] = array(
            'room_id' => $room_id,
            'room_name' => $row['room_name'],
            'is_occupied' => 0, // Assume not occupied by default
            'has_schedule' => false, // Track if the room has a schedule outside current time
            'current_class' => null,
            'future_schedule' => null // Store future or past schedules
        );
    }

    // Check if the room is currently occupied
    if ($row['is_occupied']) {
        $rooms[$room_id]['is_occupied'] = 1; // Mark as occupied
        $rooms[$room_id]['current_class'] = array(
            'subject_name' => $row['subject_name'],
            'professor_name' => $row['professor_name'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        );
    } 
    // If there's a schedule but it's not within the current time frame
    elseif ($row['subject_name'] && !$row['is_occupied']) {
        $rooms[$room_id]['has_schedule'] = true; // Mark the room as having a schedule
        $rooms[$room_id]['future_schedule'] = array(
            'subject_name' => $row['subject_name'],
            'professor_name' => $row['professor_name'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        );
    }
}

// Output the result as JSON
echo json_encode(array_values($rooms));

$stmt->close();
$conn->close();
?>
