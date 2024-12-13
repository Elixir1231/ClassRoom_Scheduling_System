<?php
include 'connect.php';

// Function to get room_id from room_name
function getRoomId($conn, $room_name) {
    $sql = "SELECT room_id FROM rooms WHERE room_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $room_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['room_id'];
    }
    return null;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $semester_id = $_POST['semester_id'];
    $room_name = $_POST['room_id']; // This is actually the room_name from the form
    $subject_name = $_POST['subject_name'];
    $professor_name = $_POST['professor_name'];
    $courseID = $_POST['courseID'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Get room_id from room_name
    $room_id = getRoomId($conn, $room_name);

    if (!$room_id) {
        echo json_encode(['status' => 'error', 'message' => 'Room does not exist']);
        $conn->close();
        exit;
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO schedules (semester_id, room_id, subject_name, professor_name, courseID, day_of_week, start_time, end_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("iissssss", $semester_id, $room_id, $subject_name, $professor_name, $courseID, $day_of_week, $start_time, $end_time);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding schedule: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error preparing statement: ' . $conn->error]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>