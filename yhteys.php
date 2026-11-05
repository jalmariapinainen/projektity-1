<?php
$servername = "Projekti1";
$username = "db";
$password = "root";
$dbname = "root";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}