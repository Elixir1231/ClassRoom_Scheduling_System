<?php
// Include the database connection file
include 'connect.php';

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the id is set
if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Schedule ID is required']);
    exit();
}

// Get the schedule ID
$scheduleId = $_POST['id'];

// Delete the schedule
$sql = "DELETE FROM schedules WHERE schedule_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $scheduleId);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => "Error executing statement: " . $stmt->error]);
    exit();
}

// Check if any rows were affected
if ($stmt->affected_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => "No schedule found with that ID."]);
    exit();
}

// Fetch the updated schedule data
$selected_semester_id = $_SESSION['selected_semester_id'] ?? $_POST['semester_id'] ?? null;
if ($selected_semester_id === null) {
    echo json_encode(['status' => 'error', 'message' => "No semester selected. Please try refreshing the page."]);
    exit();
}

$sql = "SELECT s.schedule_id, r.room_name, s.subject_name, s.professor_name, s.start_time, s.end_time, s.day_of_week
        FROM schedules s
        JOIN rooms r ON s.room_id = r.room_id
        WHERE s.semester_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $selected_semester_id);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => "Error executing statement: " . $stmt->error]);
    exit();
}

$result = $stmt->get_result();

// Array to hold schedule data
$schedule = [];
while ($row = $result->fetch_assoc()) {
    $schedule[$row['day_of_week']][] = $row;
}

// Close the statement
$stmt->close();

// Close the connection
$conn->close();

// Return a success message with the updated schedule data
echo json_encode(['status' => 'success', 'message' => 'Schedule deleted successfully', 'schedule' => $schedule]);
exit();
?>