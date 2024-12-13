<?php
include 'connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve POST data and sanitize input
    $professor_id = $_POST['professor_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $courseID = $_POST['course']; 
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $hire_date = $_POST['hire_date'];

    // Update professor details
    $sql = "UPDATE professor SET first_name = ?, last_name = ?, courseID = ?, email = ?, phone_number = ?, hire_date = ? WHERE professor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $courseID, $email, $phone_number, $hire_date, $professor_id);
   
    if ($stmt->execute()) {
        $response = array("status" => "success", "message" => "Professor updated successfully");
    } else {
        $response = array("status" => "error", "message" => "Error updating professor: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();
   
    echo json_encode($response);
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid request method"));
}
?>