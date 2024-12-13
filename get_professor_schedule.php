<?php
include 'connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function sendError($message) {
    echo json_encode(array('error' => $message));
    exit;
}

if (isset($_GET['professor_id']) && isset($_GET['semester_id'])) {
    $professor_id = $_GET['professor_id'];
    $semester_id = $_GET['semester_id'];

    // Get professor's name based on professor_id
    $professor_query = "SELECT CONCAT(first_name, ' ', last_name) AS professor_name FROM professor WHERE professor_id = ?";
    $prof_stmt = $conn->prepare($professor_query);
    $prof_stmt->bind_param("i", $professor_id);
    $prof_stmt->execute();
    $prof_result = $prof_stmt->get_result();
    if ($prof_result->num_rows === 0) {
        sendError("No professor found with the provided ID");
    }
    $professor_row = $prof_result->fetch_assoc();
    $professor_name = $professor_row['professor_name'];

    // Query to get the schedule, including room name instead of room ID
    $sql = "SELECT s.day_of_week, s.start_time, s.end_time, s.subject_name, r.room_name
            FROM schedules s
            JOIN rooms r ON s.room_id = r.room_id
            JOIN schedule_sem ss ON s.semester_id = ss.semester_id
            WHERE s.professor_name = ? AND s.semester_id = ?
            ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendError("Prepare failed: " . $conn->error);
    }

    // Bind the concatenated professor_name and semester_id
    $stmt->bind_param("si", $professor_name, $semester_id);
    if (!$stmt->execute()) {
        sendError("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if (!$result) {
        sendError("Failed to retrieve result: " . $conn->error);
    }

    $schedules = array();
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }

    if (empty($schedules)) {
        echo json_encode(array('message' => 'No schedules found for this professor in the selected semester'));
    } else {
        echo json_encode($schedules);
    }
} else {
    sendError('No professor ID or semester ID provided');
}

$conn->close();
?>
