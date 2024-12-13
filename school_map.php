<?php
include 'connect.php';
session_start(); // Start the session at the top of your PHP file

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: loginpage.php'); // Redirect to login page if not logged in
    exit();
}

// Fetch available and occupied rooms from the database
$availableRooms = $conn->query("SELECT * FROM rooms WHERE status = 'available' ORDER BY room_name");
$occupiedRooms = $conn->query("SELECT * FROM rooms WHERE status = 'occupied' ORDER BY room_name");

// Fetch floor images for the dropdown from the database
$floorImages = [];
$result = $conn->query("SELECT * FROM floor_images ORDER BY floor_number");

while ($row = $result->fetch_assoc()) {
    $floorImages[$row['floor_number']] = $row['image_path'];
}

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
    <title>School Map</title>
    
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .map-container {
            position: relative;
            max-width: 100%;
            height: 400px;
            margin-bottom: 20px;
        }
        #buildingImage {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .position-absolute.top-0.end-0.p-3 {
            z-index: 10; /* Ensure the dropdown is above other elements */
        }
        .mini-tables-container {
            max-width: 250px; /* Adjusted width */
            width: 100%; /* Allow full width on smaller screens */
            position: absolute;
            top: 90px; /* Adjusted value */
            right: 20px; /* Adjust the right position */
            z-index: 5; /* Lower z-index than the dropdown */
        }
        .mini-table {
            width: 100%;
            max-height: 120px; /* Adjusted height */
            overflow-y: auto;
            border: solid 1px #DEE2E6;
            background-color: rgba(255, 255, 255, 0.9); /* Improved readability */
            padding: 5px; /* Reduced padding */
            border-radius: 5px; /* Rounded corners */
            font-size: 0.8rem; /* Smaller font size */
        }
        .mini-table th, .mini-table td {
            font-size: 0.8rem; /* Consistent font size for cells */
        }
        #toggleMiniTables {
            transition: background-color 0.3s, transform 0.3s;
            z-index: 15;
        }
        #toggleMiniTables:hover {
            background-color: rgba(0, 123, 255, 0.1); /* Light blue background on hover */
            transform: scale(1.1); /* Slightly enlarge on hover */
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="content">
    <h2 class="mb-4">School Map</h2>

    <!-- Button and Dropdown Container -->
    <div class="d-flex justify-content-between mb-3">
        <!-- Button to Open Upload Modal -->
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadFloorModal">
            Upload Floor Image
        </button>

        <!-- Dropdown for semesters -->
    <div class="semester-selector" id="semesterDropdown">
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

        <div class="map-container">
            <img src="<?php echo !empty($floorImages) ? reset($floorImages) : ''; ?>" alt="School Building" class="img-fluid" id="buildingImage">

            <!-- Dropdown for floor selection -->
            <div class="position-absolute top-0 end-0 p-3">
                <select id="floorSelect" class="form-select">
                    <option value="">Select Floor</option>
                    <?php foreach ($floorImages as $floor => $image): ?>
                        <option value="<?php echo $image; ?>" <?php echo $floor === 1 ? 'selected' : ''; ?>>Floor <?php echo $floor; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Mini Tables Container -->
<div class="mini-tables-container position-absolute top-50 end-0 p-3" style="transform: translateY(-50%); display: block;">
    <div class="mini-table" id="occupiedRoomTable">
        <h4>Occupied Rooms</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Room Name</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="mini-table" id="availableRoomTable">
        <h4>Available Rooms</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Room Name</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Toggle Button -->
<button id="toggleMiniTables" class="btn btn-light position-absolute" style="bottom: 15px; right: 15px; width: 40px; height: 40px; border-radius: 50%; border: none; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); display: flex; align-items: center; justify-content: center;">
    <i class="fas fa-eye" id="eyeIcon" style="font-size: 20px; color: #007bff;"></i>
</button>


        <!-- Upload Floor Image Modal -->
        <div class="modal fade" id="uploadFloorModal" tabindex="-1" aria-labelledby="uploadFloorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadFloorModalLabel">Upload Floor Image</h5>
                    </div>
                    <div class="modal-body">
                    <form id="uploadFloorForm" method="POST" action="upload_floor_image.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="floorImage" class="form-label">Select Floor Image:</label>
                            <input type="file" class="form-control" id="floorImage" name="floorImage" required>
                        </div>
                        <div class="mb-3">
                            <label for="floorNumber" class="form-label">Floor Number:</label>
                            <input type="number" class="form-control" id="floorNumber" name="floorNumber" min="1" max="4" required>
                        </div>
                    </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="uploadFloorForm" class="btn btn-primary">Upload Image</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script>
    // Pass the selected semester ID to fetch room statuses
    let selectedSemesterId = "<?php echo $selected_semester_id; ?>";

    function refreshRoomStatus() {
        $.ajax({
            url: "get_room_status.php",
            method: "GET",
            data: { semester_id: selectedSemesterId }, // Pass the selected semester ID
            success: function(response) {
                var rooms = JSON.parse(response);
                var occupiedTbody = $('#occupiedRoomTable tbody');
                var availableTbody = $('#availableRoomTable tbody');
                occupiedTbody.empty();
                availableTbody.empty();

                rooms.forEach(function(room) {
                    if (room.is_occupied) {
                        var occupiedRow = '<tr>';
                        occupiedRow += '<td>' + room.room_name + '</td>';
                        occupiedRow += '</tr>';
                        occupiedTbody.append(occupiedRow);
                    } else {
                        var availableRow = '<tr>';
                        availableRow += '<td>' + room.room_name + '</td>';
                        availableRow += '</tr>';
                        availableTbody.append(availableRow);
                    }
                });
            }
        });
    }

    $(document).ready(function() {
    // Initial load
    refreshRoomStatus();

    // Refresh every 10 seconds
    setInterval(refreshRoomStatus, 10000);

    // Toggle mini tables and semester dropdown
    $('#toggleMiniTables').on('click', function() {
        $('.mini-tables-container').toggle();
        $('#semesterDropdown').toggle(); // Toggle the dropdown visibility
        var icon = $('#eyeIcon');
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    // Refresh room status when semester is changed
    $('select[name="semester_id"]').change(function() {
        selectedSemesterId = $(this).val();
        refreshRoomStatus(); // Refresh status based on the new semester
    });

    // Change building image when floor is selected
    $('#floorSelect').change(function() {
        const selectedImage = $(this).val();
        $('#buildingImage').attr('src', selectedImage); // Update the image source
    });
});

</script>
    </div>
</body>
</html>
