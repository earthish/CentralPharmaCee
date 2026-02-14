<?php
session_start();
require_once 'db_connect.php';

// Security: Only Admin can see money!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📊 Sales Report</h3>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <?php
$total = $conn->query("SELECT SUM(total_amount) as grand_total FROM Sales")->fetch_assoc();
$revenue = number_format($total['grand_total'], 2);
echo "<h2>₹{$revenue}</h2>";
?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-dark shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Transactions</h5>
                        <?php
$count = $conn->query("SELECT COUNT(*) as total_sales FROM Sales")->fetch_assoc();
echo "<h2>{$count['total_sales']}</h2>";
?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                Recent Transactions
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Sale ID</th>
                                <th>Date/Time</th>
                                <th>Customer</th>
                                <th>Billed By</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
// JOIN 3 Tables: Sales + Customers + Users
$sql = "SELECT 
                                    s.sale_id, 
                                    s.sale_date, 
                                    s.total_amount,
                                    c.name AS customer_name,
                                    u.full_name AS staff_name
                                FROM Sales s
                                JOIN Customers c ON s.cust_id = c.cust_id
                                JOIN Users u ON s.user_id = u.user_id
                                ORDER BY s.sale_date DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>#{$row['sale_id']}</td>";
        echo "<td>{$row['sale_date']}</td>";
        echo "<td>{$row['customer_name']}</td>";
        echo "<td><span class='badge bg-secondary'>{$row['staff_name']}</span></td>";
        echo "<td class='fw-bold text-success'>₹" . number_format($row['total_amount'], 2) . "</td>";
        echo "</tr>";
    }
}
else {
    echo "<tr><td colspan='5' class='text-center'>No sales recorded yet.</td></tr>";
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>