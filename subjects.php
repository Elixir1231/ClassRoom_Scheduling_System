<?php
include 'connect.php';
session_start(); // Start the session at the top of your PHP file

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: loginpage.php'); // Redirect to login page if not logged in
    exit();
}
// Fetch subjects along with semester information from the database
$sql = "SELECT s.*, sem.semester, sem.term, c.course_name FROM subjects s 
        LEFT JOIN schedule_sem sem ON s.semester_id = sem.semester_id 
        LEFT JOIN course c ON s.courseID = c.courseID 
        ORDER BY s.subject_name";
$result = $conn->query($sql);

// Fetch courses 
$sql_courses = "SELECT courseID, course_name FROM course ORDER BY course_name";
$result_courses = $conn->query($sql_courses);
$courses = $result_courses->fetch_all(MYSQLI_ASSOC);

// Fetch semesters
$sql_semesters = "SELECT semester_id, semester, term FROM schedule_sem ORDER BY semester, term";
$result_semesters = $conn->query($sql_semesters);
if (!$result_semesters) {
    die(json_encode(['status' => 'error', 'message' => "Error fetching semesters: " . $conn->error]));
}
$semesters = $result_semesters->fetch_all(MYSQLI_ASSOC);

// Get the selected semester
$selected_semester_id = isset($_GET['semester_id']) ? $_GET['semester_id'] : ($semesters[0]['semester_id'] ?? null);

if ($selected_semester_id === null) {
    die(json_encode(['status' => 'error', 'message' => "No semesters available"]));
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject & Courses</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    .table-container {
        max-height: 400px;
        height: auto;
        overflow-y: auto;
        border-bottom: solid 1px;
        border-color: #DEE2E6;
    }
        .course-table-container {
        max-height: 400px;
        width: 50%; /* Adjust this value to make the table wider or narrower */
         /* Adds some margin to the left */
        overflow-y: auto;
    }

    #courseTable {
        width: 100%; /* Makes the table fill its container */
    }
</style>
<body>

<?php include 'navbar.php'; ?>

<!-- Main Content -->
<div class="content">
    <h2 class="mb-4">SUBJECT & COURSES</h2>

    <!-- Subject Section -->
    <h3>Subjects</h3>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button id="addSubjectBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Subject
        </button>
        <input id="searchInput" type="text" class="form-control w-50" placeholder="Search for subjects">
    </div>

    <!-- Table Container -->
    <div class="table-container mb-5">
        <table class="table table-striped table-bordered" id="subjectTable">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Course</th>
                    <th>Semester</th>
                    <th>Term</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["subject_code"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["subject_name"]) . "</td>";
                        $course_name = htmlspecialchars($row["course_name"]) ?: "N/A";
                        echo "<td>" . $course_name . "</td>";
                        echo "<td>" . htmlspecialchars($row["semester"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["term"]) . "</td>";
                        echo "<td>";
                        echo "<button class='btn btn-sm btn-info mr-2 edit-subject' 
                            data-id='" . htmlspecialchars($row["subject_code"]) . "' 
                            data-name='" . htmlspecialchars($row["subject_name"]) . "' 
                            data-semester-id='" . htmlspecialchars($row["semester_id"]) . "' 
                            data-course-id='" . htmlspecialchars($row["courseID"]) . "'>
                            <i class='fas fa-edit'></i></button>";           
                        echo "<button class='btn btn-sm btn-danger ms-2 delete-subject' 
                            data-id='" . htmlspecialchars($row["subject_code"]) . "'>
                            <i class='fas fa-trash'></i></button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No subjects found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Course Section -->
    <h3>Courses</h3>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button id="addCourseBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Course
        </button>
        <input id="searchCourseInput" type="text" class="form-control w-50" placeholder="Search for courses">
    </div>

    <div class="table-container">
        <table class="table table-striped table-bordered" id="courseTable">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info edit-course" data-id="<?php echo htmlspecialchars($course['courseID']); ?>" data-name="<?php echo htmlspecialchars($course['course_name']); ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger ms-2 delete-course" data-id="<?php echo htmlspecialchars($course['courseID']); ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSubjectForm">
                    <div class="mb-3">
                        <label for="subject_code" class="form-label">Subject Code:</label>
                        <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject_name" class="form-label">Subject Name:</label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="semester">Semester:</label>
                        <select class="form-control" id="semester" name="semester" required>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo htmlspecialchars($semester['semester_id']); ?>">
                                    Semester <?php echo htmlspecialchars($semester['semester']); ?> / Term <?php echo htmlspecialchars($semester['term']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="addSubjectForm" class="btn btn-primary">Add Subject</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm">
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Name:</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="addCourseForm" class="btn btn-primary">Add Course</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
            </div>
            <div class="modal-body">
                <form id="editSubjectForm">
                    <input type="hidden" id="edit_subject_code_hidden" name="subject_code_hidden" required>
                    <div class="mb-3">
                        <label for="edit_subject_code" class="form-label">Subject Code:</label>
                        <input type="text" class="form-control" id="edit_subject_code" name="subject_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_name" class="form-label">Subject Name:</label>
                        <input type="text" class="form-control" id="edit_subject_name" name="subject_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_semester">Semester:</label>
                        <select class="form-control" id="edit_semester" name="semester" required>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo htmlspecialchars($semester['semester_id']); ?>">
                                    Semester <?php echo htmlspecialchars($semester['semester']); ?> / Term <?php echo htmlspecialchars($semester['term']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_course">Course:</label>
                        <select class="form-control" id="edit_course" name="course" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="editSubjectForm" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm">
                    <input type="hidden" id="edit_course_id" name="courseID">
                    <div class="mb-3">
                        <label for="edit_course_name" class="form-label">Course Name:</label>
                        <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="editCourseForm" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function clearMessage() {
        $('#message').removeClass('alert-success alert-danger').addClass('d-none').text('');
    }

    $(document).ready(function() {
        // Show the Add Subject Modal
        $('#addSubjectBtn').on('click', function() {
            $('#addSubjectModal').modal('show');
            clearMessage(); // Clear previous messages
        });
        
        // Add Subject Form Submission
        $('#addSubjectForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'add_subject.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    clearMessage(); // Clear any previous messages
                    $('#message').removeClass('d-none').addClass('alert-success').text(response.message);
                    $('#addSubjectModal').modal('hide');
                    location.reload(); // Refresh the page
                },
                error: function(xhr) {
                    clearMessage(); // Clear previous messages
                    $('#message').removeClass('d-none').addClass('alert-danger').text(xhr.responseJSON.message || 'An error occurred.');
                }
            });
        });

        // Edit Subject Modal Trigger
        $(document).on('click', '.edit-subject', function() {
            const subject_code = $(this).data('id');
            const subject_name = $(this).data('name');
            const semester_id = $(this).data('semester-id');
            const course_id = $(this).data('course-id');

            // Set the data into the modal fields
            $('#edit_subject_code_hidden').val(subject_code);
            $('#edit_subject_code').val(subject_code);
            $('#edit_subject_name').val(subject_name);
            $('#edit_semester').val(semester_id);
            $('#edit_course').val(course_id);

            // Show the modal
            $('#editSubjectModal').modal('show');
        });

        // Edit Subject Form Submission
        $('#editSubjectForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: 'edit_subject.php',  // Update this URL to match your file structure
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    clearMessage();
                    $('#message').removeClass('d-none alert-danger').addClass('alert-success').text(response.message);
                    $('#editSubjectModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    clearMessage();
                    $('#message').removeClass('d-none alert-success').addClass('alert-danger').text(xhr.responseJSON.message || 'An error occurred.');
                }
            });
        });

        // Delete Subject
        $(document).on('click', '.delete-subject', function() {
            const subject_code = $(this).data('id');
            if (confirm('Are you sure you want to delete this subject?')) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_subject.php',  // Update this URL to match your file structure
                    data: { subject_code: subject_code },
                    dataType: 'json',
                    success: function(response) {
                        clearMessage();
                        $('#message').removeClass('d-none').addClass('alert-success').text(response.message);
                        location.reload();
                    },
                    error: function(xhr) {
                        clearMessage();
                        $('#message').removeClass('d-none').addClass('alert-danger').text(xhr.responseJSON.message || 'An error occurred.');
                    }
                });
            }
        });

        // Handle search functionality
        $('#searchInput').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#subjectTable tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

            // Add this new code for course search functionality
    $('#searchCourseInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#courseTable tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    }); 
        // Show Add Course Modal
        $('#addCourseBtn').on('click', function() {
            $('#addCourseModal').modal('show');
        });

        // Add Course Form Submission
        $('#addCourseForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'add_course.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#addCourseModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    alert('An error occurred while adding the course.');
                }
            });
        });

        // Edit Course
        $(document).on('click', '.edit-course', function() {
            const courseId = $(this).data('id');
            const courseName = $(this).data('name');
            $('#edit_course_id').val(courseId);
            $('#edit_course_name').val(courseName);
            $('#editCourseModal').modal('show');
        });

        // Edit Course Form Submission
        $('#editCourseForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'edit_course.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#editCourseModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    alert('An error occurred while editing the course.');
                }
            });
        });

        // Delete Course
        $(document).on('click', '.delete-course', function() {
            const courseId = $(this).data('id');
            if (confirm('Are you sure you want to delete this course?')) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_course.php',
                    data: { courseID: courseId },
                    dataType: 'json',
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('An error occurred while deleting the course.');
                    }
                });
            }
        });
    });
</script>


</body>
</html>
