<?php
include '../connect.php'; // Correct the path to your connection file if needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomId = $_POST['room_id'];
    $roomName = $_POST['room_name'];

    // Update query
    $sql = "UPDATE rooms SET room_name = ? WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $roomName, $roomId);

    if ($stmt->execute()) {
        echo "Room updated successfully";
    } else {
        echo "Error updating room: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
