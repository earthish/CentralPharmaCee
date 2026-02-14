<?php
session_start();
// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>CentralPharmaCee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>CentralPharmaCee</h3>
            </div>
            <div class="card-body text-center">
                <h4 class="mb-3">Welcome,
                    <?php echo htmlspecialchars($name); ?>!
                </h4>
                <span class="badge bg-warning text-dark mb-4">Role:
                    <?php echo htmlspecialchars($role); ?>
                </span>

                <hr>

                <div class="d-grid gap-2 col-md-6 mx-auto">
                    <a href="inventory.php" class="btn btn-outline-light btn-lg mb-2">📦 View Inventory</a>

                    <?php if ($role === 'Admin'): ?>
                    <a href="add_medicine.php" class="btn btn-warning btn-lg mb-2">➕ Add Medicine</a>
                    <a href="manage_users.php" class="btn btn-secondary btn-lg mb-2">👥 Manage Staff</a>
                    <a href="sales_report.php" class="btn btn-info btn-lg mb-2">📊 Sales Reports</a>
                    <?php
endif; ?>

                    <a href="billing.php" class="btn btn-success btn-lg mb-2">💰 Billing Counter</a>
                </div>

                <hr>
                <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>

</html>