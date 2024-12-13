<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $professor_id = $_POST['professor_id'];

    $sql = "DELETE FROM professor WHERE professor_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $professor_id);
    
    if ($stmt->execute()) {
        $response = array("status" => "success", "message" => "Professor deleted successfully");
    } else {
        $response = array("status" => "error", "message" => "Error deleting professor: " . $conn->error);
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode($response);
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid request method"));
}
?>