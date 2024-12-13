<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Escaping input data to prevent SQL injection
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $courseID = $conn->real_escape_string($_POST['course']); // Updated to match 'courseID' in DB
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $hire_date = $conn->real_escape_string($_POST['hire_date']);
    $picture_path = null;

    // Handle file upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $allowed_file_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['picture']['tmp_name']);
        if (in_array($file_type, $allowed_file_types)) {
            $upload_dir = "uploads/";
            $picture_name = uniqid() . '_' . basename($_FILES['picture']['name']);
            $picture_tmp_name = $_FILES['picture']['tmp_name'];
            $picture_path = $upload_dir . $picture_name;

            if (!move_uploaded_file($picture_tmp_name, $picture_path)) {
                echo json_encode(['status' => 'error', 'message' => 'Error uploading picture.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
            exit;
        }
    }

    // Insert the professor data into the database
    $stmt = $conn->prepare("INSERT INTO professor (first_name, last_name, courseID, email, phone_number, hire_date, picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssissss', $first_name, $last_name, $courseID, $email, $phone_number, $hire_date, $picture_path);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'New professor added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
