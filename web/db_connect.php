<?php
$servername = "sql104.infinityfree.com";
$username = "if0_41158185";
$password = "kahibiniP2";
$dbname = "if0_41158185_pharma_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}