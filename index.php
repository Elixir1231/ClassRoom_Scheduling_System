<?php
session_start(); // Start the session at the top of your PHP file

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: loginpage.php'); // Redirect to login page if not logged in
    exit();
}

include 'connect.php';

date_default_timezone_set('Asia/Manila');

// Get current day and time
$current_day = date('l'); 
$current_time = date('H:i:s');

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

// Store the selected semester ID in the session
$_SESSION['selected_semester_id'] = $selected_semester_id;

// Fetch schedule for the selected semester
$sql = "SELECT r.room_name, s.subject_name, s.professor_name, s.start_time, s.end_time, s.day_of_week
        FROM schedules s
        JOIN rooms r ON s.room_id = r.room_id
        WHERE s.semester_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => "Error preparing statement: " . $conn->error]));
}
$stmt->bind_param("i", $selected_semester_id);
if (!$stmt->execute()) {
    die(json_encode(['status' => 'error', 'message' => "Error executing statement: " . $stmt->error]));
}
$result = $stmt->get_result();

// Array to hold schedule data
$schedule = [];
while ($row = $result->fetch_assoc()) {
    // Use day_of_week, start_time, and end_time as keys to store multiple entries for the same time slot
    $schedule[$row['day_of_week']][] = $row;
}

// Close the statement
$stmt->close();

// If this is an AJAX request, return the schedule data in JSON format
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo json_encode([
        'status' => 'success',
        'semesters' => $semesters,
        'selected_semester_id' => $selected_semester_id,
        'schedule' => $schedule
    ]);
    exit;
}


// Fetch rooms
$sql_rooms = "SELECT room_name FROM rooms";
$result_rooms = $conn->query($sql_rooms);

$rooms = [];
while ($row = $result_rooms->fetch_assoc()) {
    $rooms[] = $row['room_name'];
}

// Fetch professors
$sql_professors = "SELECT DISTINCT first_name, last_name FROM professor";
$result_professors = $conn->query($sql_professors);

$professors = [];
while ($row = $result_professors->fetch_assoc()) {
    $full_name = $row['first_name'] . ' ' . $row['last_name'];
    $professors[] = $full_name;
}

// Fetch subjects
$sql_subjects = "SELECT subject_code, subject_name FROM subjects";
$result_subjects = $conn->query($sql_subjects);

$subjects = [];
while ($row = $result_subjects->fetch_assoc()) {
    $fullsubject = $row['subject_code'] . ' ' . $row['subject_name'];
    $subjects[] = $fullsubject;
}

// Fetch semesters from the schedule_sem table
$sql_semesters = "SELECT semester_id, semester, term FROM schedule_sem";
$result_semesters = $conn->query($sql_semesters);

if ($result_semesters === FALSE) {
    die("Error executing query: " . $conn->error);
}

$semesters = [];
while ($row = $result_semesters->fetch_assoc()) {
    $semesters[] = $row;
}

// Fetch years
$sql_years = "SELECT yearID, year FROM year ORDER BY year";
$result_years = $conn->query($sql_years);
$years = $result_years->fetch_all(MYSQLI_ASSOC);

// Fetch courses
$sql_courses = "SELECT courseID, course_name FROM course ORDER BY course_name";
$result_courses = $conn->query($sql_courses);
$courses = $result_courses->fetch_all(MYSQLI_ASSOC);

// Close the connection
$conn->close();
?>


<!-- ------------------------------------------------------------------------------------------------- -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom Schedule</title>
    
    <link rel="stylesheet" type="text/css" href="styles.css" />

     <!-- Bootstrap CSS -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .highlight-today {
            background-color: #ffff99;
            font-weight: bold;
        }
        .schedule-cell {
            cursor: pointer;
        }
        .schedule-cell:hover {
            background-color: #f0f0f0;
        }
        .occupied-cell {
            background-color: #e6f3ff;
        }
        .schedule-detail {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.schedule-detail p {
    margin-bottom: 10px;
}

.schedule-detail strong {
    font-weight: bold;
}
#scheduleSearchInput {
    margin-top: 10px;
    width: 100%;
}

.schedule-detail {
    transition: display 0.3s ease;
}
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="content">

<!-- droplist for semesters-->
<div class="semester-selector d-flex align-items-center">
    <form action="" method="get" class="mr-2">
        <select name="semester_id" class="form-control" onchange="this.form.submit()">
            <?php foreach ($semesters as $semester): ?>
                <option value="<?php echo $semester['semester_id']; ?>" <?php echo ($semester['semester_id'] == $selected_semester_id) ? 'selected' : ''; ?>>
                    <?php echo "Semester {$semester['semester']} / Term {$semester['term']}"; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <button id="addScheduleBtn" class="btn btn-primary mr-2"><i class="fas fa-plus"></i> Add Schedule</button>
</div>

<!-- ------------------------------------------------------------------------------------------------- -->

<!-- Modal for adding schedule -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="scheduleModalLabel">Add New Schedule</h5>
                    </div>
                    <div class="modal-body">
                        <form id="scheduleForm">
                            <div class="mb-3">
                                <label for="room_id" class="form-label">Room:</label>
                                <select class="form-select" id="room_id" name="room_id" required>
                                    <?php foreach ($rooms as $room): ?>
                                        <option value="<?php echo htmlspecialchars($room); ?>">
                                            <?php echo htmlspecialchars($room); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subject_name" class="form-label">Subject:</label>
                                <select class="form-select" id="subject_name" name="subject_name" required>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo htmlspecialchars($subject); ?>">
                                            <?php echo htmlspecialchars($subject); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="professor_name" class="form-label">Professor:</label>
                                <select class="form-select" id="professor_name" name="professor_name" required>
                                    <?php foreach ($professors as $professor): ?>
                                        <option value="<?php echo htmlspecialchars($professor); ?>">
                                            <?php echo htmlspecialchars($professor); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="courseID" class="form-label">Course:</label>
                                <select class="form-select" id="courseID" name="courseID" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="day_of_week" class="form-label">Day of the Week:</label>
                                <select class="form-select" id="day_of_week" name="day_of_week" required>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time:</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time:</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="scheduleForm" class="btn btn-primary">Add Schedule</button>
                    </div>
                </div>
            </div>
        </div>

      <!-- Modal for adding room -->
      <div id="roomModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Room</h5>
                    </div>
                    <div class="modal-body">
                        <form id="roomForm">
                            <div class="form-group">
                                <label for="room_name">Room Name:</label>
                                <input type="text" class="form-control" id="room_name" name="room_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Room</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding subject -->
        <div id="subjectModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Subject</h5>
                    </div>
                    <div class="modal-body">
                        <form id="subjectForm" enctype="multipart/form-data" method="POST">
                            <div class="form-group">
                                <label for="subject_code">Subject Code:</label>
                                <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                            </div>
                            <div class="form-group">
                                <label for="subject_name">Subject Name:</label>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                            </div>
                            <div class="form-group">
                                <label for="semester">Semester:</label>
                                <select class="form-control" id="semester" name="semester" required>
                                    <?php foreach ($semesters as $semester): ?>
                                        <option value="<?php echo htmlspecialchars($semester['semester_id']); ?>">
                                            Semester <?php echo htmlspecialchars($semester['semester']); ?> / Term <?php echo htmlspecialchars($semester['term']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                            <label for="course">Course:</label>
                                <select class="form-control" id="course" name="course" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal for adding professor -->
<div id="professorModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Professor</h5>
            </div>
            <div class="modal-body">
                <form id="professorForm" enctype="multipart/form-data" method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="course">Course:</label>
                        <select class="form-control" id="course" name="course" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="form-group">
                        <label for="hire_date">Hire Date:</label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                    </div>
                    <div class="form-group">
                        <label for="picture">Picture:</label>
                        <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Professor</button>
                </form>
            </div>
        </div>
    </div>
</div>

        <!-- Modal for adding course (unchanged) -->
        <div id="courseModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                </div>
                <div class="modal-body">
                    <form id="courseForm">
                        <div class="form-group">
                            <label for="course_name">Course Name:</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal Structure -->
        <div class="modal fade" id="noScheduleModal" tabindex="-1" aria-labelledby="noScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noScheduleModalLabel">No Schedule Available</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                No schedule has been set for this semester. Please create a schedule before saving.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
        </div>

<!-- ------------------------------------------------------------------------------------------------- -->

<div class="schedule-container">
        <table id="schedule" class="table table-bordered">
            <thead>
                <tr>
                    <th>Time</th>
                    <?php
                    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
                    foreach ($days as $day) {
                        $highlightClass = ($current_day === $day) ? 'highlight-today' : '';
                        echo "<th class='{$highlightClass}'>{$day}</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $timeSlots = [];
                $start = new DateTime('07:00');
                $end = new DateTime('21:00');
                $interval = new DateInterval('PT30M');
                $current = clone $start;

                while ($current < $end) {
                    $next = clone $current;
                    $next->add($interval);
                    $timeSlots[] = $current->format('g:i A') . '-' . $next->format('g:i A');
                    $current = $next;
                }

                foreach ($timeSlots as $timeSlot) {
                    echo "<tr><td>{$timeSlot}</td>";
                    list($slotStart, $slotEnd) = explode('-', $timeSlot);
                    
                    foreach ($days as $day) {
                        $entries = array_filter($schedule[$day] ?? [], function($d) use ($slotStart, $slotEnd) {
                            $startTime = new DateTime($d['start_time']);
                            $endTime = new DateTime($d['end_time']);
                            $slotStartTime = DateTime::createFromFormat('g:i A', trim($slotStart));
                            $slotEndTime = DateTime::createFromFormat('g:i A', trim($slotEnd));
                            
                            // Check if the schedule overlaps with the time slot
                            return ($startTime < $slotEndTime && $endTime > $slotStartTime);
                        });
                
                        if (count($entries) > 0) {
                            $cellContent = implode(', ', array_column($entries, 'room_name'));
                            $cellData = htmlspecialchars(json_encode($entries));
                        } else {
                            $cellContent = '-';
                            $cellData = '';
                        }
                
                        $highlightClass = ($current_day === $day) ? 'highlight-today' : '';
                        echo "<td class='{$highlightClass} schedule-cell' data-entry='{$cellData}'>{$cellContent}</td>";
                    }
                    echo "</tr>";
                }
                
                ?>
            </tbody>
        </table>
    </div>

<!-- Modal for displaying schedule details -->
<div class="modal fade" id="scheduleDetailModal" tabindex="-1" aria-labelledby="scheduleDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleDetailModalLabel">Schedule Details</h5>
            </div>
            <div class="modal-body">
                <div id="schedule-details-container"></div>
            </div>
        </div>
    </div>
</div>

<!-- ------------------------------------------------------------------------------------------------- -->

        <!-- Mini table for rooms -->
        <div class="mini-table">
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Room Name
                                <button id="addRoomBtn" class="btn btn-secondary btn-sm float-right" title="Add Room">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php foreach ($rooms as $room) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mini table for subjects -->
        <div class="mini-table">
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject Information
                                <button id="addSubjectBtn" class="btn btn-secondary btn-sm float-right" title="Add Subject">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php foreach ($subjects as $subject) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mini table for professors -->
        <div class="mini-table">
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Professor Name
                                <button id="addProfessorBtn" class="btn btn-secondary btn-sm float-right" title="Add Professor">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php foreach ($professors as $professor) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($professor); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

<!-- Modified mini table for courses -->
<div class="mini-table">
    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Course Name
                        <button id="addCourseBtn" class="btn btn-secondary btn-sm float-right" title="Add Course">
                            <i class="fas fa-plus"></i>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody id="courseTableBody" class="table-body">
                <?php foreach ($courses as $course) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

 <!-- Add this new modal for editing schedules -->
 <div class="modal fade edit-modal" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editScheduleForm">
                        <input type="hidden" id="edit_schedule_id" name="schedule_id">
                        <div class="form-group">
    <label for="edit_room_id">Room:</label>
    <select class="form-control" id="edit_room_id" name="room_id" required>
        <?php foreach ($rooms as $room): ?>
            <option value="<?php echo htmlspecialchars($room); ?>">
                <?php echo htmlspecialchars($room); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                        <div class="form-group">
                            <label for="edit_subject_name">Subject:</label>
                            <select class="form-control" id="edit_subject_name" name="subject_name" required>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo htmlspecialchars($subject); ?>">
                                        <?php echo htmlspecialchars($subject); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_professor_name">Professor:</label>
                            <select class="form-control" id="edit_professor_name" name="professor_name" required>
                                <?php foreach ($professors as $professor): ?>
                                    <option value="<?php echo htmlspecialchars($professor); ?>">
                                        <?php echo htmlspecialchars($professor); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_day_of_week">Day of the Week:</label>
                            <select class="form-control" id="edit_day_of_week" name="day_of_week" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_start_time">Start Time:</label>
                            <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_end_time">End Time:</label>
                            <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEditSchedule">Save changes</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Bootstrap JS and dependencies -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ------------------------------------------------------------------------------------------------- -->
<script>
// Wait for the DOM to be fully loaded before executing the script
document.addEventListener('DOMContentLoaded', () => {
    // Set up modal objects for easy access
    const modals = {
        schedule: new bootstrap.Modal(document.getElementById('scheduleModal')),
        professor: new bootstrap.Modal(document.getElementById('professorModal')),
        room: new bootstrap.Modal(document.getElementById('roomModal')),
        subject: new bootstrap.Modal(document.getElementById('subjectModal')),
        noSchedule: new bootstrap.Modal(document.getElementById('noScheduleModal')),
        scheduleDetail: new bootstrap.Modal(document.getElementById('scheduleDetailModal')),
        course: new bootstrap.Modal(document.getElementById('courseModal'))
    };

    // Generic form submission handler
    const handleFormSubmit = (formId, addPhpFile, modalKey) => {
        document.getElementById(formId).addEventListener("submit", function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            // Add the selected semester_id to the form data
            const semesterSelect = document.querySelector('select[name="semester_id"]');
            formData.append('semester_id', semesterSelect.value);

            fetch(addPhpFile, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    alert(result.message);
                    modals[modalKey].hide();
                    updateMiniTable(modalKey, formData);
                    this.reset();
                    location.reload(); // Refresh the page after successful submission
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error(`Error adding ${modalKey}:`, error);
                alert(`An error occurred while adding the ${modalKey}`);
            });
        });
    };

    // Add click event listeners to all schedule cells
    document.querySelectorAll('.schedule-cell').forEach(cell => {
        cell.addEventListener('click', () => handleCellClick(cell));
    });

// Add search functionality to the schedule detail modal
const searchInput = document.createElement('input');
searchInput.type = 'text';
searchInput.placeholder = 'Search schedules...';
searchInput.classList.add('form-control', 'mb-3');

const modalBody = document.querySelector('#scheduleDetailModal .modal-body');
modalBody.insertBefore(searchInput, modalBody.firstChild);

searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const scheduleDetails = document.querySelectorAll('.schedule-detail');

    scheduleDetails.forEach(detail => {
        const text = detail.innerText.toLowerCase();
        // If the search term is found, show the detail, otherwise hide it
        if (text.includes(searchTerm)) {
            detail.style.display = ''; // Show the element
        } else {
            detail.style.display = 'none'; // Hide the element
        }
    });
});


    // Update mini tables with new data
    function updateMiniTable(tableType, formData) {
        let tableBody;
        let newRowContent;

        // Find the correct table body based on the type
        switch (tableType) {
            case 'room':
                tableBody = document.querySelector('.mini-table[data-type="room"] .table-body');
                newRowContent = formData.get('room_name');
                break;
            case 'subject':
                tableBody = document.querySelector('.mini-table[data-type="subject"] .table-body');
                newRowContent = `${formData.get('subject_code')} ${formData.get('subject_name')}`;
                break;
            case 'professor':
                tableBody = document.querySelector('.mini-table[data-type="professor"] .table-body');
                newRowContent = `${formData.get('first_name')} ${formData.get('last_name')}`;
                break;
            case 'course':
                tableBody = document.getElementById('courseTableBody');
                newRowContent = formData.get('course_name');
                break;
            default:
                return;
        }

        // Add the new row if table body is found
        if (tableBody) {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `<td>${newRowContent}</td>`;
            tableBody.appendChild(newRow);
        }
    }

    // Initialize buttons and form submissions
    ['schedule', 'professor', 'room', 'subject', 'course'].forEach(key => {
        const button = document.getElementById(`add${key.charAt(0).toUpperCase() + key.slice(1)}Btn`);
        if (button) {
            button.addEventListener('click', () => modals[key].show());
        }

        handleFormSubmit(`${key}Form`, `add_${key}.php`, key);
    });

// Fetch schedule data from the server
function fetchSchedule(semesterId) {
        fetch(`fetch_schedule.php?semester_id=${semesterId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateScheduleUI(data.schedule);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching the schedule');
        });
    }
    function updateCourseTable(formData) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `<td>${formData.get('course_name')}</td>`;
        courseTableBody.appendChild(newRow);
    }

        // Initialize the schedule when the page loads
    const semesterSelect = document.querySelector('select[name="semester_id"]');
    if (semesterSelect) {
        fetchSchedule(semesterSelect.value);
        semesterSelect.addEventListener('change', () => fetchSchedule(semesterSelect.value));
    }


    // Update the schedule UI with new data
    function updateScheduleUI(scheduleData) {
        console.log('Updating schedule UI with:', scheduleData);
        const scheduleTable = document.getElementById('schedule');
        const tbody = scheduleTable.tBodies[0];

        // Clear existing schedule data
        tbody.innerHTML = '';

        // Generate time slots
        const timeSlots = [];
        const start = new Date('1970-01-01T07:00:00');
        const end = new Date('1970-01-01T21:00:00');
        const interval = 30 * 60 * 1000; // 30 minutes in milliseconds
        let current = new Date(start);

        while (current < end) {
            const next = new Date(current.getTime() + interval);
            timeSlots.push({
                start: current.toTimeString().slice(0, 5),
                end: next.toTimeString().slice(0, 5)
            });
            current = next;
        }

        const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        timeSlots.forEach(timeSlot => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${timeSlot.start} - ${timeSlot.end}</td>`;

            days.forEach(day => {
                const cell = document.createElement('td');
                const entries = scheduleData[day] || [];
                const overlappingEntries = entries.filter(entry => {
                    const entryStart = entry.start_time.slice(0, 5);
                    const entryEnd = entry.end_time.slice(0, 5);
                    return (entryStart <= timeSlot.end && entryEnd > timeSlot.start);
                });

                if (overlappingEntries.length > 0) {
                    cell.innerHTML = overlappingEntries.map(entry => entry.room_name).join(', ');
                    cell.classList.add('occupied-cell');
                    cell.setAttribute('data-entry', JSON.stringify(overlappingEntries));
                    cell.addEventListener('click', () => handleCellClick(cell)); // Ensure click event is added
                } else {
                    cell.innerHTML = '-';
                }

                cell.classList.add('schedule-cell');
                row.appendChild(cell);
            });

            tbody.appendChild(row);
        });
    }

    // Handle click events on schedule cells
    function handleCellClick(cell) {
        const scheduleDetailsContainer = document.getElementById('schedule-details-container');
        scheduleDetailsContainer.innerHTML = ''; // Clear previous content

        const cellData = cell.getAttribute('data-entry');

        if (cellData && cellData !== '[]') {
            const entries = JSON.parse(cellData);
            
            const scheduleDetailHtml = entries.map(entry => `
                <div class="schedule-detail" data-id="${entry.schedule_id}">
                    <p><strong>Room:</strong> ${entry.room_name}</p>
                    <p><strong>Subject:</strong> ${entry.subject_name}</p>
                    <p><strong>Professor:</strong> ${entry.professor_name}</p>
                    <p><strong>Time:</strong> ${entry.start_time} - ${entry.end_time}</p>
                    <p><strong>Day:</strong> ${entry.day_of_week}</p>
                    <div class="mt-2">
                        <button class="btn btn-primary btn-sm edit-schedule" data-id="${entry.schedule_id}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-schedule" data-id="${entry.schedule_id}">Delete</button>
                    </div>
                </div>
            `).join('<hr>');

            scheduleDetailsContainer.innerHTML = scheduleDetailHtml;

            // Delete schedule function
            function deleteSchedule(scheduleId) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                // Get the current semester ID from the select element
                const semesterSelect = document.querySelector('select[name="semester_id"]');
                const semesterId = semesterSelect.value;

                fetch('delete_schedule.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${scheduleId}&semester_id=${semesterId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        updateScheduleUI(data.schedule); // Update the schedule UI with the updated data
                        const scheduleDetailModal = bootstrap.Modal.getInstance(document.getElementById('scheduleDetailModal'));
                        if (scheduleDetailModal) {
                            scheduleDetailModal.hide(); // Hide the detail modal after successful deletion
                        }
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the schedule: ' + error.message);
                });
            }
        }

            // Add event listeners for edit and delete buttons
            document.querySelectorAll('.edit-schedule').forEach(button => {
                button.addEventListener('click', () => editSchedule(button.getAttribute('data-id')));
            });
            document.querySelectorAll('.delete-schedule').forEach(button => {
                button.addEventListener('click', () => deleteSchedule(button.getAttribute('data-id')));
            });
        } else {
            scheduleDetailsContainer.innerHTML = '<p>No schedule available for this time slot.</p>';
        }

        const scheduleDetailModal = new bootstrap.Modal(document.getElementById('scheduleDetailModal'));
        scheduleDetailModal.show();
    }

    // Edit schedule function
    function editSchedule(scheduleId) {
    const semesterSelect = document.querySelector('select[name="semester_id"]');
    const semesterId = semesterSelect.value;

    fetch(`fetch_schedule.php?semester_id=${semesterId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                let scheduleEntry = null;
                for (const day in data.schedule) {
                    const entries = data.schedule[day];
                    scheduleEntry = entries.find(entry => entry.schedule_id == scheduleId);
                    if (scheduleEntry) break;
                }

                if (scheduleEntry) {
                    document.getElementById('edit_schedule_id').value = scheduleEntry.schedule_id;
                    document.getElementById('edit_room_id').value = scheduleEntry.room_name; // Changed from room_id to room_name
                    document.getElementById('edit_subject_name').value = scheduleEntry.subject_name;
                    document.getElementById('edit_professor_name').value = scheduleEntry.professor_name;
                    document.getElementById('edit_day_of_week').value = scheduleEntry.day_of_week;
                    document.getElementById('edit_start_time').value = scheduleEntry.start_time;
                    document.getElementById('edit_end_time').value = scheduleEntry.end_time;

                    const editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
                    editModal.show();
                } else {
                    throw new Error('Schedule entry not found');
                }
            } else {
                throw new Error(data.message || 'Failed to fetch schedule data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching schedule data: ' + error.message);
        });
}

// Single event listener for saving edited schedule
document.getElementById('saveEditSchedule').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('editScheduleForm'));
    fetch('update_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Schedule updated successfully');
            updateScheduleUI(result.schedule); // Update the UI with the new schedule data
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editScheduleModal'));
            editModal.hide(); // Hide the edit modal
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the schedule');
    });
});


    // Function to update the course table (if needed)
    function updateCourseTable(formData) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${formData.get('course_name')}</td>
        `;
        courseTableBody.appendChild(newRow);
    }
});
</script>

    </body>
</html>
