<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseID = $_POST['courseID'];
    
    $sql = "DELETE FROM course WHERE courseID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseID);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting course: ' . $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>