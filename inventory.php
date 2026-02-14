<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Pharmacy Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Inventory List</h2>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Medicine Name</th>
                                <th>Category</th>
                                <th>Batch ID</th>
                                <th>Expiry Date</th>
                                <th>Stock Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
// --- FIXED SQL QUERY ---
$sql = "SELECT 
            m.name AS medicine_name, 
            c.category_name, 
            b.batch_id, 
            b.expiry_date, 
            b.stock_qty, 
            m.price_per_unit 
        FROM Batches b
        JOIN Medicines m ON b.med_id = m.med_id
        JOIN Categories c ON m.category_id = c.category_id
        ORDER BY b.expiry_date ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $today = date("Y-m-d");

    while ($row = $result->fetch_assoc()) {
        // Check for expiry to highlight row
        $is_expired = ($row['expiry_date'] < $today);
        $row_class = $is_expired ? "table-danger" : "";
        $text_class = $is_expired ? "text-danger fw-bold" : "";

        echo "<tr class='{$row_class}'>";
        echo "<td>" . htmlspecialchars($row['medicine_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['batch_id']) . "</td>";
        echo "<td class='{$text_class}'>" . htmlspecialchars($row['expiry_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['stock_qty']) . "</td>";
        echo "<td>$" . number_format($row['price_per_unit'], 2) . "</td>";
        echo "</tr>";
    }
}
else {
    // If query fails or is empty, show why
    $error = $conn->error ? $conn->error : "No inventory found";
    echo "<tr><td colspan='6' class='text-center text-warning'>Status: $error</td></tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>