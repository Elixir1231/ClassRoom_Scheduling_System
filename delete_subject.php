<?php
include 'connect.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the subject code to be deleted
    $subject_code = $_POST['subject_code'];

    // Prepare the SQL statement
    $sql = "DELETE FROM subjects WHERE subject_code = ?";

    // Prepare and bind the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject_code);

    // Execute the statement
    if ($stmt->execute()) {
        $response = array("status" => "success", "message" => "Subject deleted successfully");
    } else {
        $response = array("status" => "error", "message" => "Error deleting subject: " . $conn->error);
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