<?php
session_start();
require_once 'db_connect.php';

// 1. Security Check
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message = "";

// 2. Handle the "Sell" Button Click (Multiple Items)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize basic inputs
    $cust_name = $conn->real_escape_string($_POST['customer_name']);
    $cust_phone = $conn->real_escape_string($_POST['customer_phone']);
    
    $batches = $_POST['batch_id']; 
    $quantities = $_POST['quantity']; 

    $grand_total = 0;
    $items_billed = 0;

    // ==========================================
    // START SQL TRANSACTION
    // ==========================================
    $conn->begin_transaction();

    try {
        // A. Add/Find Customer
        $conn->query("INSERT INTO Customers (name, phone) VALUES ('$cust_name', '$cust_phone') 
                      ON DUPLICATE KEY UPDATE name='$cust_name'");
        
        $c_query = $conn->query("SELECT cust_id FROM Customers WHERE phone='$cust_phone'");
        $cust = $c_query->fetch_assoc();
        $cust_id = $cust['cust_id'];

        // B. Create initial Sale Record (Total = 0 for now)
        $user_id = $_SESSION['user_id'];
        $conn->query("INSERT INTO Sales (cust_id, user_id, total_amount) VALUES ($cust_id, $user_id, 0)");
        $sale_id = $conn->insert_id;

        // C. Loop through every medicine submitted
        for ($i = 0; $i < count($batches); $i++) {
            $batch_id = $conn->real_escape_string($batches[$i]);
            $qty_sold = intval($quantities[$i]);

            // Skip empty rows
            if (empty($batch_id) || $qty_sold <= 0) continue; 

            // Check Stock with FOR UPDATE (Locks the row to prevent race conditions)
            $sql_check = "SELECT b.stock_qty, m.price_per_unit 
                          FROM Batches b 
                          JOIN Medicines m ON b.med_id = m.med_id 
                          WHERE b.batch_id = '$batch_id' FOR UPDATE";
            $check = $conn->query($sql_check);

            if ($check->num_rows > 0) {
                $item = $check->fetch_assoc();

                if ($item['stock_qty'] >= $qty_sold) {
                    $subtotal = $item['price_per_unit'] * $qty_sold;
                    $grand_total += $subtotal;

                    // Add Item to Sale_Items
                    $conn->query("INSERT INTO Sale_Items (sale_id, batch_id, quantity, unit_price, subtotal) 
                                  VALUES ($sale_id, '$batch_id', $qty_sold, {$item['price_per_unit']}, $subtotal)");

                    // REDUCE STOCK
                    $conn->query("UPDATE Batches SET stock_qty = stock_qty - $qty_sold WHERE batch_id = '$batch_id'");
                    $items_billed++;
                } else {
                    // Throwing an exception triggers the ROLLBACK
                    throw new Exception("Not enough stock for Batch: $batch_id. Available: {$item['stock_qty']}");
                }
            } else {
                throw new Exception("Batch $batch_id not found.");
            }
        }

        // D. Finalize Grand Total or Rollback if empty
        if ($items_billed > 0) {
            $conn->query("UPDATE Sales SET total_amount = $grand_total WHERE sale_id = $sale_id");
            
            // ==========================================
            // COMMIT TRANSACTION (Permanently save everything)
            // ==========================================
            $conn->commit();
            
            $message .= "<div class='alert alert-success'> Sale #$sale_id Complete! $items_billed items billed. Grand Total: ₹$grand_total. <br><a href='receipt.php?id=$sale_id' class='btn btn-sm btn-dark mt-2' target='_blank'>Print Receipt</a></div>";
        } else {
            // Nothing valid was submitted
            $conn->rollback();
            $message .= "<div class='alert alert-warning'>No valid items were submitted. Transaction cancelled.</div>";
        }

    } catch (Exception $e) {
        // ==========================================
        // ROLLBACK TRANSACTION (Undo everything if an error occurs)
        // ==========================================
        $conn->rollback();
        $message .= "<div class='alert alert-danger'><strong>Transaction Failed:</strong> " . $e->getMessage() . " <br>The entire bill has been cancelled.</div>";
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
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">🛒 Billing Counter (POS)</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>

                        <form method="POST">
                            <h5 class="mb-3">Customer Info</h5>
                            <div class="row mb-3">
                                <div class="col">
                                    <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" required>
                                </div>
                                <div class="col">
                                    <input type="text" name="customer_phone" class="form-control" placeholder="Phone Number" required>
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3">Medicine Details</h5>
                            
                            <div id="medicine-container">
                                <div class="row mb-3 medicine-row">
                                    <div class="col-md-8">
                                        <label>Select Medicine</label>
                                        <select name="batch_id[]" class="form-select" required>
                                            <option value="">-- Choose Medicine --</option>
                                            <?php
                                            // Fetch list for the dropdown
                                            $sql = "SELECT b.batch_id, b.stock_qty, m.name AS medicine_name, m.price_per_unit
                                                    FROM Batches b
                                                    JOIN Medicines m ON b.med_id = m.med_id
                                                    WHERE b.stock_qty > 0 AND b.expiry_date >= CURDATE()";
                                            $stock = $conn->query($sql);
                                            if ($stock->num_rows > 0) {
                                                while ($row = $stock->fetch_assoc()) {
                                                    echo "<option value='{$row['batch_id']}'>";
                                                    echo "{$row['medicine_name']} - ₹{$row['price_per_unit']} (Stock: {$row['stock_qty']})";
                                                    echo "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No Stock Available</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Quantity</label>
                                        <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-info mb-4" onclick="addMedicineRow()">➕ Add Another Medicine</button>

                            <button type="submit" class="btn btn-success w-100 btn-lg">Complete Sale</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addMedicineRow() {
            // Get the container
            const container = document.getElementById('medicine-container');
            // Get the first row
            const firstRow = document.querySelector('.medicine-row');
            // Clone it
            const newRow = firstRow.cloneNode(true);
            // Reset the quantity back to 1 in the cloned row
            newRow.querySelector('input[type="number"]').value = 1;
            // Add it to the container
            container.appendChild(newRow);
        }
    </script>
</body>
</html>
