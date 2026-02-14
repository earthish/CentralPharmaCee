<?php
// FILE: api_login.php
header("Content-Type: application/json; charset=UTF-8");
require 'db_connect.php'; // Use the connection file you just tested

// 1. Allow Browser Testing (GET Request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(["status" => "success", "message" => "Login API is UP and READY!"]);
    exit();
}

// 2. Read the JSON sent by Android (POST Request)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// 3. Validate Input
if (!isset($input['username']) || !isset($input['password'])) {
    echo json_encode(["status" => "error", "message" => "Missing username or password"]);
    exit();
}

$u = $input['username'];
$p = $input['password'];

// 4. Check Database
// Note: In a real app, use password_verify(). For this exam project, we compare plain text.
$stmt = $conn->prepare("SELECT user_id, full_name, role FROM Users WHERE username = ? AND password_hash = ?");
$stmt->bind_param("ss", $u, $p);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // LOGIN SUCCESS!
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "message" => "Login Successful",
        "user_id" => $row['user_id'],
        "full_name" => $row['full_name'],
        "role" => $row['role']
    ]);
}
else {
    // LOGIN FAILED
    echo json_encode(["status" => "error", "message" => "Invalid Username or Password"]);
}

$conn->close();
?>