<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Add User Logic
if (isset($_POST['add_user'])) {
    $u = $_POST['username'];
    $p = $_POST['password']; // Storing plain text as per your project setup
    $r = $_POST['role'];
    $n = $_POST['full_name'];
    $conn->query("INSERT INTO Users (username, password_hash, role, full_name) VALUES ('$u', '$p', '$r', '$n')");
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h3>👥 Manage Staff</h3>

        <div class="card mb-4 p-3">
            <h5>Add New Employee</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-3"><input type="text" name="full_name" class="form-control" placeholder="Full Name"
                        required></div>
                <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Username"
                        required></div>
                <div class="col-md-2"><input type="text" name="password" class="form-control" placeholder="Password"
                        required></div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option>Pharmacist</option>
                        <option>Admin</option>
                        <option>Billing</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" name="add_user" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>

        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php
$res = $conn->query("SELECT * FROM Users");
while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['user_id']}</td><td>{$row['full_name']}</td><td>{$row['username']}</td><td>{$row['role']}</td></tr>";
}
?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>

</html>