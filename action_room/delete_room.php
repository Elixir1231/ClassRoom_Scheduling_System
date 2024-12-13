<?php
include '../connect.php'; // Correct the path to your connection file if needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomId = $_POST['room_id'];

    // Delete query
    $sql = "DELETE FROM rooms WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $roomId);

    if ($stmt->execute()) {
        echo "Room deleted successfully";
    } else {
        echo "Error deleting room: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
