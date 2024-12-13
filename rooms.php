<?php
include 'connect.php';
session_start(); 

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: loginpage.php'); 
    exit();
}

// Fetch rooms from the database
$sql = "SELECT * FROM rooms ORDER BY room_name";
$result = $conn->query($sql);

// Fetch semesters from the schedule_sem table
$semesters = [];
$selected_semester_id = isset($_GET['semester_id']) ? $_GET['semester_id'] : null;

$semester_sql = "SELECT * FROM schedule_sem ORDER BY semester, term";
$semester_result = $conn->query($semester_sql);
if ($semester_result->num_rows > 0) {
    while ($row = $semester_result->fetch_assoc()) {
        $semesters[] = $row;
    }
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room</title>
    
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border-bottom: solid 1px;
            border-color: #DEE2E6;
        }
        .mini-table {
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            border-bottom: solid 1px;
            border-color: #DEE2E6;
        }
        .container{
            margin-top:-5px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <h2 class="mb-4">ROOM</h2>
        
        <!-- Search Form -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button id="addRoomBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Room
            </button>
            <input id="searchInput" type="text" class="form-control w-50" placeholder="Search for rooms">
        </div>
        
        
        <!-- Table Container -->
        <div class="table-container">
            <table class="table table-striped table-bordered" id="roomTable">
                <thead class="thead-light">
                    <tr>
                        <th>Room ID</th>
                        <th>Room Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["room_id"] . "</td>";
                            echo "<td>" . $row["room_name"] . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-info mr-2 edit-room' data-id='" . $row["room_id"] . "' data-name='" . $row["room_name"] . "'><i class='fas fa-edit'></i></button>";
                            echo "<button class='btn btn-sm btn-danger ms-2 delete-room' data-id='" . $row["room_id"] . "'><i class='fas fa-trash'></i></button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>No rooms found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>



    <div class="container">
    <div class="row mb-4">
        <!-- Dropdown for semesters, aligned to the right -->
        <div class="col-md-4 offset-md-8 semester-selector">
            <form action="" method="get" class="text-right">
                <select name="semester_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Select Semester</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo $semester['semester_id']; ?>" <?php echo ($semester['semester_id'] == $selected_semester_id) ? 'selected' : ''; ?>>
                            <?php echo "Semester {$semester['semester']} / Term {$semester['term']}"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row justify-content-end">
        <!-- Mini-tables for Real-Time Room Status -->
        <div class="col-md-4 mini-table">
            <h4>Occupied Rooms</h4>
            <table class="table table-striped table-bordered" id="occupiedRoomTable">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Current Class</th>
                        <th>Professor</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <script>
                        var occupiedRow = '<tr>';
                        occupiedRow += '<td>' + room.room_name + '</td>';
                        occupiedRow += '<td>' + room.subject_name + '</td>';
                        occupiedRow += '<td>' + room.professor_name + '</td>';
                        occupiedRow += '</tr>';
                        occupiedTbody.append(occupiedRow);
                    </script>
                </tbody>
            </table>
        </div>

        <div class="col-md-4 mini-table">
            <h4>Available Rooms</h4>
            <table class="table table-striped table-bordered" id="availableRoomTable">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <script>
                        var availableRow = '<tr>';
                        availableRow += '<td>' + room.room_name + '</td>';
                        availableRow += '<td><span class="text-success">Available</span></td>';
                        availableRow += '</tr>';
                        availableTbody.append(availableRow);
                    </script>
                </tbody>
            </table>
        </div>
    </div>
</div>


    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm">
                        <div class="mb-3">
                            <label for="room_name" class="form-label">Room Name:</label>
                            <input type="text" class="form-control" id="room_name" name="room_name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="addRoomForm" class="btn btn-primary">Add Room</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm">
                        <input type="hidden" id="edit_room_id" name="room_id">
                        <div class="mb-3">
                            <label for="edit_room_name" class="form-label">Room Name:</label>
                            <input type="text" class="form-control" id="edit_room_name" name="room_name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="editRoomForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Filter Function for Search
    $(document).ready(function() {
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#roomTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Add Room
        $("#addRoomBtn").click(function() {
            $("#addRoomModal").modal("show");
        });

        $("#addRoomForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "add_room.php",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $("#addRoomModal").modal("hide");
                    location.reload();
                }
            });
        });

        // Edit Room
        $(document).on("click", ".edit-room", function() {
            var roomId = $(this).data("id");
            var roomName = $(this).data("name");
            $("#edit_room_id").val(roomId);
            $("#edit_room_name").val(roomName);
            $("#editRoomModal").modal("show");
        });

        $("#editRoomForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "action_room/edit_room.php",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    alert(response); // Optionally display a success message
                    $("#editRoomModal").modal("hide");
                    location.reload(); // Reload the page to reflect changes
                },
                error: function() {
                    alert("Error updating room.");
                }
            });
        });

        // Delete Room
        $(document).on("click", ".delete-room", function() {
            var roomId = $(this).data("id");
            if (confirm("Are you sure you want to delete this room?")) {
                $.ajax({
                    url: "action_room/delete_room.php",
                    method: "POST",
                    data: { room_id: roomId },
                    success: function(response) {
                        location.reload();
                    }
                });
            }
        });
    });

// Time conversion function from 24-hour to 12-hour format
function formatTimeTo12Hour(timeString) {
    const [hour, minute] = timeString.split(':');
    let period = 'AM';
    let hour12 = parseInt(hour);

    if (hour12 >= 12) {
        period = 'PM';
        if (hour12 > 12) hour12 -= 12;
    }

    if (hour12 === 0) {
        hour12 = 12;
    }

    return hour12 + ':' + minute + ' ' + period;
}

// Function to refresh room status with selected semester term
function refreshRoomStatus() {
    // Get the selected semester_id from the dropdown
    var selectedSemesterId = $('select[name="semester_id"]').val(); 

    // Make the AJAX request with the selected semester_id
    $.ajax({
        url: "get_room_status.php",
        method: "GET",
        data: { semester_id: selectedSemesterId }, // Pass semester_id to the server
        success: function(response) {
            var rooms = JSON.parse(response);

            // Clear the current table contents
            var occupiedTbody = $('#occupiedRoomTable tbody');
            var availableTbody = $('#availableRoomTable tbody');
            occupiedTbody.empty();
            availableTbody.empty();

            // Populate the tables
            rooms.forEach(function(room) {
                if (room.is_occupied) {
                    // For occupied rooms
                    var startTime = formatTimeTo12Hour(room.current_class.start_time);
                    var endTime = formatTimeTo12Hour(room.current_class.end_time);
                    var occupiedRow = '<tr>';
                    occupiedRow += '<td>' + room.room_name + '</td>';
                    occupiedRow += '<td>' + room.current_class.subject_name + '</td>';
                    occupiedRow += '<td>' + room.current_class.professor_name + '</td>';
                    occupiedRow += '<td>' + startTime + ' - ' + endTime + '</td>';
                    occupiedRow += '</tr>';
                    occupiedTbody.append(occupiedRow);
                } else {
                    // For available rooms
                    var availableRow = '<tr>';
                    availableRow += '<td>' + room.room_name + '</td>';
                    availableRow += '<td><span class="text-success">Available</span></td>';
                    availableTbody.append(availableRow);
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching room status:", error);
        }
    });
}

// Refresh room status every 60 seconds (adjust as needed)
setInterval(refreshRoomStatus, 60000);

// Initial refresh on page load
$(document).ready(function() {
    refreshRoomStatus();

    // Re-fetch room status when semester term is changed
    $('select[name="semester_id"]').change(function() {
        refreshRoomStatus();  // Refresh the room status when the semester is changed
    });
});


</script>

</body>
</html>