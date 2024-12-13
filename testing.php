<?php
include 'connect.php';
// Fetch years
$sql_years = "SELECT yearID, year FROM year ORDER BY year";
$result_years = $conn->query($sql_years);
$years = $result_years->fetch_all(MYSQLI_ASSOC);

// Fetch courses (now we'll fetch all courses and filter them in JavaScript)
$sql_courses = "SELECT c.courseID, c.course_name, y.year, y.yearID 
                FROM course c 
                JOIN year y ON c.yearID = y.yearID 
                ORDER BY y.year, c.course_name";
$result_courses = $conn->query($sql_courses);
$courses = $result_courses->fetch_all(MYSQLI_ASSOC);
?>

<!-- Mini table for years (unchanged) -->
<div class="mini-table">
    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody class="table-body">
                <?php foreach ($years as $year) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($year['year']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modified mini table for courses with year filter -->
<div class="mini-table">
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <select id="yearFilter" class="form-control w-auto">
                <option value="all">All Years</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo htmlspecialchars($year['yearID']); ?>">
                        Year <?php echo htmlspecialchars($year['year']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button id="addCourseBtn" class="btn btn-secondary btn-sm" title="Add Course">
                <i class="fas fa-plus"></i> Add Course
            </button>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody id="courseTableBody" class="table-body">
                <?php foreach ($courses as $course) : ?>
                    <tr data-year="<?php echo htmlspecialchars($course['yearID']); ?>">
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['year']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
                    <div class="form-group">
                        <label for="yearID">Year:</label>
                        <select class="form-control" id="yearID" name="yearID" required>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year['yearID']); ?>">
                                    <?php echo htmlspecialchars($year['year']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const courseModal = new bootstrap.Modal(document.getElementById('courseModal'));
    const yearFilter = document.getElementById('yearFilter');
    const courseTableBody = document.getElementById('courseTableBody');
    
    document.getElementById('addCourseBtn').addEventListener('click', () => courseModal.show());

    yearFilter.addEventListener('change', function() {
        const selectedYear = this.value;
        const rows = courseTableBody.getElementsByTagName('tr');
        
        for (let row of rows) {
            if (selectedYear === 'all' || row.dataset.year === selectedYear) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });

    document.getElementById('courseForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('add_course.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                alert(result.message);
                courseModal.hide();
                updateCourseTable(formData);
                this.reset();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error adding course:', error);
            alert('An error occurred while adding the course');
        });
    });

    function updateCourseTable(formData) {
        const newRow = document.createElement('tr');
        const yearSelect = document.getElementById('yearID');
        const selectedYear = yearSelect.options[yearSelect.selectedIndex].text;
        const yearID = yearSelect.value;
        newRow.dataset.year = yearID;
        newRow.innerHTML = `
            <td>${formData.get('course_name')}</td>
            <td>${selectedYear}</td>
        `;
        courseTableBody.appendChild(newRow);
        
        // Apply current filter
        yearFilter.dispatchEvent(new Event('change'));
    }
});
</script>