<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schedule_id = $_POST['schedule_id'];
    $room_name = $_POST['room_id']; // Changed from room_id to room_name
    $subject_name = $_POST['subject_name'];
    $professor_name = $_POST['professor_name'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // First, get the room_id from the rooms table
    $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_name = ?");
    $stmt->bind_param("s", $room_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $room_id = $row['room_id'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Room not found']);
        exit;
    }
    $stmt->close();

    // Now update the schedule
    $stmt = $conn->prepare("UPDATE schedules SET room_id = ?, subject_name = ?, professor_name = ?, day_of_week = ?, start_time = ?, end_time = ? WHERE schedule_id = ?");
    $stmt->bind_param("isssssi", $room_id, $subject_name, $professor_name, $day_of_week, $start_time, $end_time, $schedule_id);
    
    if ($stmt->execute()) {
        // Fetch the updated schedule
        $semester_id = $_SESSION['selected_semester_id']; // Make sure this is set when selecting a semester
        $result = $conn->query("SELECT r.room_name, s.subject_name, s.professor_name, s.start_time, s.end_time, s.day_of_week, s.schedule_id
                                FROM schedules s
                                JOIN rooms r ON s.room_id = r.room_id
                                WHERE s.semester_id = $semester_id");
        
        $schedule = [];
        while ($row = $result->fetch_assoc()) {
            $schedule[$row['day_of_week']][] = $row;
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Schedule updated successfully', 'schedule' => $schedule]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating schedule: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>