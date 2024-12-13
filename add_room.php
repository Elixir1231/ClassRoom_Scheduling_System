<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_name = $_POST['room_name'];

    // Prepare SQL query to insert the new room
    $sql = "INSERT INTO rooms (room_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $room_name);

    if ($stmt->execute()) {
        // Fetch the newly added room's ID and return it with the response
        $new_room_id = $stmt->insert_id;
        echo json_encode([
            'status' => 'success', 
            'message' => 'Room added successfully!',
            'room_id' => $new_room_id,  // Return the new room ID for UI update
            'room_name' => $room_name   // Return the new room name
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error adding room: ' . $conn->error
        ]);
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
