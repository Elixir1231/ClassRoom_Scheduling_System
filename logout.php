<?php
session_start(); // Start the session
$_SESSION = []; // Clear session data
session_destroy(); // Destroy the session
header('Location: loginpage.php'); // Redirect to login page
exit();
?>
