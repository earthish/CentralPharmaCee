<?php
session_start();
require_once 'db_connect.php';

// Security: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$message = "";

// Handle Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $med_name = $_POST['med_name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $batch_id = $_POST['batch_id'];
    $supplier_id = $_POST['supplier_id'];
    $qty = $_POST['qty'];
    $expiry = $_POST['expiry'];

    // 1. Check if Medicine exists, if not create it
    // (Simplification: We assume the name is unique)
    $check = $conn->query("SELECT med_id FROM Medicines WHERE name = '$med_name'");
    if ($check->num_rows > 0) {
        $m = $check->fetch_assoc();
        $med_id = $m['med_id'];
    }
    else {
        $conn->query("INSERT INTO Medicines (name, category_id, price_per_unit) VALUES ('$med_name', '$category_id', '$price')");
        $med_id = $conn->insert_id;
    }

    // 2. Insert Batch
    $sql = "INSERT INTO Batches (batch_id, med_id, supplier_id, expiry_date, stock_qty) 
            VALUES ('$batch_id', '$med_id', '$supplier_id', '$expiry', '$qty')";

    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>Stock Added Successfully!</div>";
    }
    else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Add Medicine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4>Add New Stock</h4>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Medicine Name</label>
                            <input type="text" name="med_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Category</label>
                            <select name="category_id" class="form-select">
                                <?php
$cats = $conn->query("SELECT * FROM Categories");
while ($c = $cats->fetch_assoc())
    echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Batch ID (Unique)</label>
                            <input type="text" name="batch_id" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Expiry Date</label>
                            <input type="date" name="expiry" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Quantity</label>
                            <input type="number" name="qty" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Price Per Unit (₹)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">-- Select Supplier --</option>
                                    <?php
$sups = $conn->query("SELECT * FROM Suppliers");
while ($s = $sups->fetch_assoc()) {
    echo "<option value='{$s['supplier_id']}'>{$s['company_name']}</option>";
}
?>
                                </select>
                                <a href="add_supplier.php" class="btn btn-outline-secondary" target="_blank">+ New</a>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">Add to Inventory</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>

</html>