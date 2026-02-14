<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pharmacy Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="bg-body-tertiary">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">CentralPharmaCee</a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3">
                    Welcome, <strong>
                        <?php echo htmlspecialchars($username); ?>
                    </strong>
                </span>
                <a href="?logout=true" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row g-4 justify-content-center">

            <!-- View Inventory Card -->
            <div class="col-md-4">
                <div class="card h-100 text-center shadow dashboard-card border-primary">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h5 class="card-title text-primary">Inventory</h5>
                        <p class="card-text">Manage stock, view medicines, and check expiry dates.</p>
                        <a href="inventory.php" class="btn btn-primary mt-auto stretched-link">View Inventory</a>
                    </div>
                </div>
            </div>

            <!-- View Sales Card (Placeholder) -->
            <div class="col-md-4">
                <div class="card h-100 text-center shadow dashboard-card border-success">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h5 class="card-title text-success">Sales</h5>
                        <p class="card-text">View sales records and transaction history.</p>
                        <button class="btn btn-success mt-auto" disabled>View Sales (Coming Soon)</button>
                    </div>
                </div>
            </div>

            <!-- Logout Card -->
            <div class="col-md-4">
                <div class="card h-100 text-center shadow dashboard-card border-danger">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h5 class="card-title text-danger">Account</h5>
                        <p class="card-text">Sign out of the system securely.</p>
                        <a href="?logout=true" class="btn btn-danger mt-auto stretched-link">Logout</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>