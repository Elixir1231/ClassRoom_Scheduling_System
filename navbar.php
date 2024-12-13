<div class="side-navbar">
    <div class="logo-container" data-bs-toggle="modal" data-bs-target="#logoChangeModal">
        <img id="schoolLogo" src="Pictures/ramon.png" alt="School Logo" class="school-logo">
        <h4 id="schoolName">RMMC</h4>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="index.php">SCHEDULE</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="rooms.php">ROOM</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="subjects.php">SUBJECT & COURSES</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="professors.php">PROFESSOR'S PROFILE</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="school_map.php">SCHOOL MAP</a>
        </li>
        <li class="nav-item">
            <a class="nav-link logout-link" href="logout.php">LOGOUT</a>
        </li>
    </ul>

    <!-- Time and Day Display -->
    <div id="time" class="time-display"></div>
</div>

<!-- Modal for changing the logo and school name -->
<div class="modal fade" id="logoChangeModal" tabindex="-1" aria-labelledby="logoChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoChangeModalLabel">Change Logo and School Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="logoChangeForm">
                    <div class="mb-3">
                        <label for="logoInput" class="form-label">Change Logo:</label>
                        <input type="file" class="form-control" id="logoInput" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="schoolNameInput" class="form-label">Change School Name:</label>
                        <input type="text" class="form-control" id="schoolNameInput" placeholder="Enter School Name">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to load the logo and school name from local storage
    function loadLogoAndSchoolName() {
        const storedLogo = localStorage.getItem('schoolLogo');
        const storedSchoolName = localStorage.getItem('schoolName');

        if (storedLogo) {
            document.getElementById('schoolLogo').src = storedLogo;
        }
        if (storedSchoolName) {
            document.getElementById('schoolName').textContent = storedSchoolName;
        }
    }

    // Highlight 'active' class based on current page
    document.addEventListener('DOMContentLoaded', function () {
        loadLogoAndSchoolName(); // Load the logo and school name on page load

        const currentLocation = window.location.pathname.split("/").pop(); // Get the current file name from URL
        const navLinks = document.querySelectorAll('.nav-link'); // Select all nav links

        // Loop through all links and add 'active' class if href matches current page
        navLinks.forEach(link => {
            const linkPath = link.getAttribute('href').split("/").pop();
            if (linkPath === currentLocation) {
                link.classList.add('active');
            } else {
                link.classList.remove('active'); // Ensure other links do not retain active state
            }
        });
    });

    // Save changes 
    function saveChanges() {
        const logoInput = document.getElementById('logoInput');
        const schoolNameInput = document.getElementById('schoolNameInput');

        // Change the logo if a new image is uploaded
        if (logoInput.files && logoInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const logoUrl = e.target.result;
                document.getElementById('schoolLogo').src = logoUrl;
                // Save logo URL to local storage
                localStorage.setItem('schoolLogo', logoUrl);
            };
            reader.readAsDataURL(logoInput.files[0]);
        }

        // Change the school name
        const newSchoolName = schoolNameInput.value.trim();
        if (newSchoolName) {
            document.getElementById('schoolName').textContent = newSchoolName;
            // Save school name to local storage
            localStorage.setItem('schoolName', newSchoolName);
        }


    }

    // Time update function
    function updateTime() {
        const now = new Date();
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        const displayHours = hours % 12 || 12;
        const displayMinutes = minutes < 10 ? '0' + minutes : minutes;
        const displaySeconds = seconds < 10 ? '0' + seconds : seconds;
        const dayName = days[now.getDay()];

        const timeString = `${displayHours}:${displayMinutes}:${displaySeconds} ${ampm}`;
        const dayString = `${dayName}`;

        document.getElementById('time').textContent = `${timeString} | ${dayString}`;
    }

    setInterval(updateTime, 1000); 
    updateTime(); 
</script>

<!-- Add Bootstrap 5 CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<style>
    /* Style for the logout link as a button */
.logout-link {
    display: block; /* Makes the link fill the parent space */
    background-color: #343a40; 
    color: #fff; /* White text */
    padding: 12px 20px; /* Padding for a button feel */
    border-top-right-radius: 5px; /* Rounded top right corner */
    border-bottom-right-radius: 5px;
    transition: background-color 0.5s ease, transform 0.2s; /* Smooth transitions */
    margin-top: auto; /* Push to the bottom of the navbar */
}

/* Hover effect for logout link */
.logout-link:hover {
    background-color: #c82333; /* Darker red on hover */
    transform: scale(1.05); /* Slightly scale up for emphasis */
}

/* Add focus style for accessibility */
.logout-link:focus {
    outline: none; /* Remove default outline */
    box-shadow: 0 0 5px rgba(220, 53, 69, 0.5); /* Soft glow effect */
}

   /* Style for the entire side-navbar */
.side-navbar {
    width: 250px; /* Fixed width */
    height: 100vh; /* Full height */
    background-color: #343a40; /* Dark background for the sidebar */
    color: #fff;
    padding-top: 20px;
    position: fixed; /* Keeps the sidebar fixed */
    z-index: 1000; /* Ensure it stays on top */
}

/* Style for the logo container */
.logo-container {
    text-align: center;
    margin-bottom: 30px;
    cursor: pointer;
}

/* Logo style */
.school-logo {
    width: 100px;
    height: auto;
    border-radius: 50%;
}

/* School name styling */
#schoolName {
    margin-top: 10px;
    font-weight: bold;
}

/* Style for the nav links */
.nav-link {
    color: #adb5bd;
    padding: 10px 20px;
    font-size: 16px;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Darker background and white text for active link */
.nav-link.active, .nav-link:hover {
    background-color: #495057;
    color: #fff;
}

/* Optional: Style for time display */
.time-display {
    margin-top: 100px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
}

/* Modal content customization */
.modal-content {
    background-color: #f8f9fa;
    border-radius: 8px;
}

.modal-title {
    color: #343a40;
}

/* Main content area adjustments */
.content {
    margin-left: 250px; /* Offset for the sidebar */
    padding: 20px; /* Add padding for aesthetics */
    transition: margin-left 0.3s; /* Smooth transition */
}

/* Responsive Design */
@media (max-width: 768px) {
    .side-navbar {
        width: 100%; /* Full width on small screens */
        height: auto; /* Let it expand as needed */
        position: relative; /* Change positioning */
    }

    .content {
        margin-left: 0; /* Remove margin on small screens */
        padding: 10px; /* Reduce padding for smaller screens */
    }
}

</style>
