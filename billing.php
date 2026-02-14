<?php
session_start();
require_once 'db_connect.php';

// 1. Security Check: Kick out if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

// 2. Handle the "Sell" Button Click
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_id = $_POST['batch_id'];
    $qty_sold = intval($_POST['quantity']);
    $cust_name = $_POST['customer_name'];
    $cust_phone = $_POST['customer_phone'];

    // A. Check Stock (Using our View!)
    $check = $conn->query("SELECT stock_qty, price_per_unit FROM View_Available_Stock WHERE batch_id = '$batch_id'");

    if ($check->num_rows > 0) {
        $item = $check->fetch_assoc();

        if ($item['stock_qty'] >= $qty_sold) {
            // Transaction Start
            $total = $item['price_per_unit'] * $qty_sold;

            // B. Add/Find Customer (Auto-Update if exists)
            $conn->query("INSERT INTO Customers (name, phone) VALUES ('$cust_name', '$cust_phone') 
                          ON DUPLICATE KEY UPDATE name='$cust_name'");

            // Get Customer ID (handling the case where it wasn't a new insert)
            $c_query = $conn->query("SELECT cust_id FROM Customers WHERE phone='$cust_phone'");
            $cust = $c_query->fetch_assoc();
            $cust_id = $cust['cust_id'];

            // C. Create Sale Record
            $user_id = $_SESSION['user_id'];
            $conn->query("INSERT INTO Sales (cust_id, user_id, total_amount) VALUES ($cust_id, $user_id, $total)");
            $sale_id = $conn->insert_id;

            // D. Add Item to Sale
            $conn->query("INSERT INTO Sale_Items (sale_id, batch_id, quantity, unit_price, subtotal) 
                          VALUES ($sale_id, '$batch_id', $qty_sold, {$item['price_per_unit']}, $total)");

            // E. REDUCE STOCK (The most important part!)
            $conn->query("UPDATE Batches SET stock_qty = stock_qty - $qty_sold WHERE batch_id = '$batch_id'");

            $message = "<div class='alert alert-success'>Sale Complete! Total: ₹$total</div>";
        }
        else {
            $message = "<div class='alert alert-danger'>Error: Not enough stock! (Only {$item['stock_qty']} left)</div>";
        }
    }
    else {
        $message = "<div class='alert alert-danger'>Error: Batch not found!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Billing Counter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4>Billing Counter (POS)</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>

                        <form method="POST">
                            <h5 class="mb-3">👤 Customer Info</h5>
                            <div class="row mb-3">
                                <div class="col">
                                    <input type="text" name="customer_name" class="form-control"
                                        placeholder="Customer Name" required>
                                </div>
                                <div class="col">
                                    <input type="text" name="customer_phone" class="form-control"
                                        placeholder="Phone Number" required>
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3"> Medicine Details</h5>
                            <div class="mb-3">
                                <label>Select Medicine (Live Stock)</label>
                                <select name="batch_id" class="form-select" required>
                                    <option value="">-- Choose Medicine --</option>
                                    <?php
// DIRECT QUERY (Bypassing the blocked View)
$sql = "SELECT 
                                                b.batch_id, 
                                                b.stock_qty, 
                                                m.name AS medicine_name, 
                                                m.price_per_unit
                                            FROM Batches b
                                            JOIN Medicines m ON b.med_id = m.med_id
                                            WHERE b.stock_qty > 0 
                                            AND b.expiry_date >= CURDATE()";

$stock = $conn->query($sql);

if ($stock->num_rows > 0) {
    while ($row = $stock->fetch_assoc()) {
        echo "<option value='{$row['batch_id']}'>";
        echo "{$row['medicine_name']} - ₹{$row['price_per_unit']} (Stock: {$row['stock_qty']})";
        echo "</option>";
    }
}
else {
    echo "<option value='' disabled>No Stock Available</option>";
}
?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100 btn-lg"> Complete Sale</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>