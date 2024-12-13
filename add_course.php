<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = $_POST['course_name'];
    
    $sql = "INSERT INTO course (course_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_name);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Course added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding course: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>