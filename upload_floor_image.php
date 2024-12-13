<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $floorNumber = intval($_POST['floorNumber']);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp']; // Add more formats if necessary
    $targetDir = "uploads/";

    // Get the file extension
    $imageFileType = strtolower(pathinfo($_FILES['floorImage']['name'], PATHINFO_EXTENSION));

    // Check if the file extension is allowed
    if (in_array($imageFileType, $allowedTypes)) {
        // Construct the target file name with dynamic extension
        $targetFile = $targetDir . "floor_" . $floorNumber . "." . $imageFileType;

        // Move uploaded file to the target directory
        if (move_uploaded_file($_FILES['floorImage']['tmp_name'], $targetFile)) {
            // Insert the image path into the database
            $stmt = $conn->prepare("INSERT INTO floor_images (floor_number, image_path) VALUES (?, ?) ON DUPLICATE KEY UPDATE image_path = ?");
            $stmt->bind_param("isi", $floorNumber, $targetFile, $targetFile);

            if ($stmt->execute()) {
                echo "Image uploaded and saved to database.";
            } else {
                echo "Error saving to database.";
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Unsupported file format. Please upload an image (jpg, jpeg, png, gif, bmp).";
    }
}
?>
