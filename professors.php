<?php
include 'connect.php';
session_start(); // Start the session at the top of your PHP file

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: loginpage.php'); // Redirect to login page if not logged in
    exit();
}
// Fetch professors from the database with their associated courses
$sql = "SELECT professor.*, course.course_name 
        FROM professor 
        LEFT JOIN course ON professor.courseID = course.courseID 
        ORDER BY professor.last_name";
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
    // Set default semester if none is selected
    if (!$selected_semester_id && !empty($semesters)) {
        $selected_semester_id = $semesters[0]['semester_id'];
    }
}


// Fetch courses
$sql_courses = "SELECT courseID, course_name FROM course ORDER BY course_name";
$result_courses = $conn->query($sql_courses);
$courses = $result_courses->fetch_all(MYSQLI_ASSOC);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor</title>
    
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
        .email-column {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .profile-schedule-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 20px;
        }
        .profile-box {
            flex: 0 0 300px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        #profileImage {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
        }
        .schedule-table {
            flex: 1;
            max-height: 400px;
            overflow-y: auto;
        }
        .small-dropdown {
    margin-bottom: 10px; /* Adds 1px margin at the bottom */
}
.professor-row.selected {
    background-color: #e0e0e0;
}

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <h2 class="mb-4">PROFESSORS</h2>
        
        <!-- Search Form -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button id="addProfessorBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Professor
            </button>
            <input id="searchInput" type="text" class="form-control w-50" placeholder="Search for professors">
        </div>
        
        <!-- Table Container -->
        <div class="table-container">
            <table class="table table-striped table-bordered" id="professorTable">
                <thead class="thead-light">
                    <tr>
                        <th>Professor ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Course</th> 
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr class='professor-row' data-id='" . $row["professor_id"] . "' data-first='" . $row["first_name"] . "' data-last='" . $row["last_name"] . "' data-course='" . ($row["course_name"] ?? 'N/A') . "' data-email='" . $row["email"] . "' data-phone='" . $row["phone_number"] . "' data-hire='" . $row["hire_date"] . "' data-picture='" . $row["picture"] . "'>";
                        echo "<td>" . $row["professor_id"] . "</td>";
                        echo "<td class='first-name'>" . $row["first_name"] . "</td>";
                        echo "<td class='last-name'>" . $row["last_name"] . "</td>";                        
                        echo "<td class='course'>" . ($row["course_name"] ?? 'N/A') . "</td>"; // Make sure to access the right field
                        echo "<td class='email-column'>" . $row["email"] . "</td>";
                        echo "<td>";
                        echo "<button class='btn btn-sm btn-info mr-2 edit-professor' data-id='" . $row["professor_id"] . "'><i class='fas fa-edit'></i></button>";
                        echo "<button class='btn btn-sm btn-danger ms-2 delete-professor' data-id='" . $row["professor_id"] . "'><i class='fas fa-trash'></i></button>";
                        echo "</td>";
                        echo "</tr>";
                    }                    
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No professors found</td></tr>";
                }                
                ?>
            </tbody>
            </table>
        </div>
        

        <!-- Profile and Schedule Container -->
        <div class="profile-schedule-container">
            <!-- Profile Box -->
            <div id="profileBox" class="card text-center profile-box">
                <img id="profileImage" src="" alt="Profile Picture" class="card-img-top rounded-circle mx-auto" style="width: 100px; height: 100px; display:none;">
                <div class="card-body">
                    <h5 class="card-title" id="profileFullName">Select a professor to see details</h5>
                    <p class="card-text" id="profileCourse"></p> 
                    <p class="card-text" id="profileEmail"></p>
                    <p class="card-text" id="profilePhone"></p>
                    <p class="card-text" id="profileHireDate"></p>
                </div>
            </div>

            <!-- Professor Schedules Table -->
            <div class="schedule-table">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Professor Schedules</h4>
        <!-- Dropdown for semesters, aligned to the right -->
        <form action="" method="get" class="semester-selector">
            <select name="semester_id" class="form-control small-dropdown" onchange="this.form.submit()">
                <option value="">Select Semester</option>
                <?php foreach ($semesters as $semester): ?>
                    <option value="<?php echo $semester['semester_id']; ?>" <?php echo ($semester['semester_id'] == $selected_semester_id) ? 'selected' : ''; ?>>
                        <?php echo "Semester {$semester['semester']} / Term {$semester['term']}"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>          
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleTableBody">
                        <!-- Schedule data will be populated here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
<!-- Modal for adding professor -->
<div id="professorModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Professor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="professorForm" enctype="multipart/form-data" method="POST">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name:</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name:</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="course">Course:</label>
                        <select class="form-control" id="course" name="course" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number:</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="hire_date" class="form-label">Hire Date:</label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="picture" class="form-label">Picture:</label>
                        <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Professor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Professor Modal -->
<div class="modal fade" id="editProfessorModal" tabindex="-1" aria-labelledby="editProfessorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfessorModalLabel">Edit Professor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfessorForm">
                    <input type="hidden" id="edit_professor_id">
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name:</label>
                        <input type="text" class="form-control" id="edit_first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name:</label>
                        <input type="text" class="form-control" id="edit_last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_course" class="form-label">Course:</label>
                        <select class="form-control" id="edit_course" name="edit_course" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone_number" class="form-label">Phone Number:</label>
                        <input type="text" class="form-control" id="edit_phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_hire_date" class="form-label">Hire Date:</label>
                        <input type="date" class="form-control" id="edit_hire_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Professor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {

// Search Filter for Professors Table
$("#searchInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#professorTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

// Click Event for Table Rows
$(document).on("click", ".professor-row", function() {
    $(".professor-row").removeClass("selected");
    $(this).addClass("selected");

    updateProfessorProfile($(this));
    fetchProfessorSchedule();  // Call function to get the professor's schedule
});

// Function to Update Professor's Profile
function updateProfessorProfile($row) {
    var professorId = $row.data("id");
    var firstName = $row.data("first");
    var lastName = $row.data("last");
    var course = $row.data("course");
    var email = $row.data("email");
    var phone = $row.data("phone");
    var hireDate = $row.data("hire");
    var picture = $row.data("picture");

    $("#profileFullName").text(firstName + " " + lastName);
    $("#profileCourse").text("Course: " + course);
    $("#profileEmail").text("Email: " + email);
    $("#profilePhone").text("Phone: " + phone);
    $("#profileHireDate").text("Hire Date: " + hireDate);
    $("#profileImage").attr("src", picture).show();
    $("#profileBox").show();
}

// Function to Fetch Professor's Schedule
function fetchProfessorSchedule() {
    var $selectedProfessor = $(".professor-row.selected");
    var professorId = $selectedProfessor.data("id");
    var semesterId = $("select[name='semester_id']").val();

    if (professorId && semesterId) {
        console.log("Fetching schedule for professor ID:", professorId, "and semester ID:", semesterId);
        $.ajax({
            url: 'get_professor_schedule.php',
            type: 'GET',
            data: { professor_id: professorId, semester_id: semesterId },
            dataType: 'json ',
            success: function(response) {
                if (response.error) {
                    alert("Error: " + response.error);
                    return;
                }

                $("#scheduleTableBody").empty();

                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(schedule) {
                        $("#scheduleTableBody").append(`
                            <tr>
                                <td>${schedule.day_of_week}</td>
                                <td>${schedule.start_time} - ${schedule.end_time}</td>
                                <td>${schedule.subject_name}</td>
                                <td>${schedule.room_name}</td>
                            </tr>
                        `);
                    });
                } else if (response.message) {
                    $("#scheduleTableBody").append(`<tr><td colspan="4" class="text-center">${response.message}</td></tr>`);
                } else {
                    $("#scheduleTableBody").append('<tr><td colspan="4" class="text-center">No schedules found for this professor in the selected semester</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                alert("An error occurred: " + error);
                console.log("Response text:", xhr.responseText);
                $("#scheduleTableBody").append('<tr><td colspan="4" class="text-center">Error loading schedules</td></tr>');
            }
        });
    } else {
        $("#scheduleTableBody").empty();
        $("#scheduleTableBody").append('<tr><td colspan="4" class="text-center">Please select a professor and semester to view schedules</td></tr>');
    }
}

// Semester term dropdown change event
$("select[name='semester_id']").on("change", function() {
    fetchProfessorSchedule();  // Fetch schedule when semester changes
});

// Show Add Professor Modal
$("#addProfessorBtn").on("click", function() {
    $("#professorModal").modal('show');
});

// Edit Professor Modal Handling
$(document).on("click", ".edit-professor", function(e) {
    e.stopPropagation();  // Prevent row click event
    var professorId = $(this).data("id");
    var row = $(this).closest("tr");

    // Fill the edit form fields
    $("#edit_professor_id").val(professorId);
    $("#edit_first_name").val(row.find(".first-name").text());
    $("#edit_last_name").val(row.find(".last-name").text());
    $("#edit_course").val(row.find(".course").text());
    $("#edit_email").val(row.data("email"));
    $("#edit_phone_number").val(row.data("phone"));
    $("#edit_hire_date").val(row.data("hire"));

    // Show the edit modal
    $("#editProfessorModal").modal('show');
});

// Handle Edit Professor Form Submission
$("#editProfessorForm").on("submit", function(e) {
    e.preventDefault();  // Prevent default form submission

    var formData = {
        professor_id: $("#edit_professor_id").val(),
        first_name: $("#edit_first_name").val(),
        last_name: $("#edit_last_name").val(),
        course: $("#edit_course").val(),
        email: $("#edit_email").val(),
        phone_number: $("#edit_phone_number").val(),
        hire_date: $("#edit_hire_date").val()
    };

    $.ajax({
        url: 'edit_professor.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            var res = JSON.parse(response);
            alert(res.message);
            if (res.status === 'success') {
                location.reload();  // Reload to show updated data
            }
            $("#editProfessorModal").modal('hide');  // Hide modal
        },
        error: function(xhr, status, error) {
            alert("An error occurred: " + error);
        }
    });
});

// Delete Professor
$(document).on("click", ".delete-professor", function(e) {
    e.stopPropagation();  // Prevent row click event
    var professorId = $(this).data("id");
    if (confirm("Are you sure you want to delete this professor?")) {
        $.ajax({
            url: 'delete_professor.php',
            type: 'POST',
            data: { professor_id: professorId },
            success: function(response) {
                var res = JSON.parse(response);
                alert(res.message);
                if (res.status === 'success') {
                    location.reload();  // Reload to reflect changes
                }
            },
            error: function(xhr, status, error) {
                alert("An error occurred: " + error);
            }
        });
    }
});

// Select the first professor row on page load and trigger the click event
$(".professor-row:first").trigger("click");
});

</script>
</body>
</html>
