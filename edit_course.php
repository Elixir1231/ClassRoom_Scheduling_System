<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseID = $_POST['courseID'];
    $course_name = $_POST['course_name'];
    
    $sql = "UPDATE course SET course_name = ? WHERE courseID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $course_name, $courseID);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating course: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>