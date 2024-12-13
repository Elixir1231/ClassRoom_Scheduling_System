<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "cs_schedulingdb";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
