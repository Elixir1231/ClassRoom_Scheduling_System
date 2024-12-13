<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connect.php'; // Ensure your connect.php file is correct and connects to your database.

// Check if the connection was successful
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Fetch courses and semesters for lookups
$courses = [];
$sql_courses = "SELECT courseID, course_name FROM course ORDER BY course_name";
$result_courses = $conn->query($sql_courses);
if ($result_courses) {
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
}

$semesters = [];
$sql_semesters = "SELECT semester_id, semester, term FROM schedule_sem ORDER BY semester, term";
$result_semesters = $conn->query($sql_semesters);
if ($result_semesters) {
    $semesters = $result_semesters->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $subject_code = isset($_POST['subject_code']) ? trim($_POST['subject_code']) : '';
    $subject_name = isset($_POST['subject_name']) ? trim($_POST['subject_name']) : '';
    $semester_id = isset($_POST['semester']) ? (int)$_POST['semester'] : 0;
    $course_id = isset($_POST['course']) ? (int)$_POST['course'] : 0;

    // Check for empty fields
    if (empty($subject_code) || empty($subject_name) || $semester_id <= 0 || $course_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields correctly.']);
        exit;
    }

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, semester_id, courseID) VALUES (?, ?, ?, ?)");
    
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // Bind parameters
    $stmt->bind_param("ssii", $subject_code, $subject_name, $semester_id, $course_id);

    // Execute statement
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'New subject added successfully.',
            'subject_code' => $subject_code,
            'subject_name' => $subject_name,
            'course_name' => getCourseName($course_id, $courses), // Get course name
            'semester' => getSemester($semester_id, $semesters), // Get semester name
            'term' => getTerm($semester_id, $semesters) // Get term name
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }

    // Close statement
    $stmt->close();
}

$conn->close();

// Functions to get course name and semester details
function getCourseName($courseID, $courses) {
    foreach ($courses as $course) {
        if ($course['courseID'] == $courseID) {
            return htmlspecialchars($course['course_name']);
        }
    }
    return '';
}

function getSemester($semester_id, $semesters) {
    foreach ($semesters as $semester) {
        if ($semester['semester_id'] == $semester_id) {
            return htmlspecialchars($semester['semester']);
        }
    }
    return '';
}

function getTerm($semester_id, $semesters) {
    foreach ($semesters as $semester) {
        if ($semester['semester_id'] == $semester_id) {
            return htmlspecialchars($semester['term']);
        }
    }
    return '';
}
?>
