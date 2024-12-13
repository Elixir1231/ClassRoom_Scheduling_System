<?php
include 'connect.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $original_subject_code = $_POST['subject_code_hidden'];
    $new_subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $semester_id = $_POST['semester'];
    $course_id = $_POST['course'];

    // Prepare the SQL statement
    $sql = "UPDATE subjects SET 
            subject_code = ?, 
            subject_name = ?, 
            semester_id = ?, 
            courseID = ? 
            WHERE subject_code = ?";

    // Prepare and bind the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $new_subject_code, $subject_name, $semester_id, $course_id, $original_subject_code);

    // Execute the statement
    if ($stmt->execute()) {
        $response = array("status" => "success", "message" => "Subject updated successfully");
    } else {
        $response = array("status" => "error", "message" => "Error updating subject: " . $conn->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // If not a POST request, return an error
    $response = array("status" => "error", "message" => "Invalid request method");
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>