<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['company_name'];
    $conn->query("INSERT INTO Suppliers (company_name) VALUES ('$name')");
    echo "<script>alert('Supplier Added!'); window.close();</script>";
}
?>
<!DOCTYPE html>
<html data-bs-theme="dark">

<body class="p-4">
    <h3>Add New Supplier</h3>
    <form method="POST">
        <input type="text" name="company_name" class="form-control mb-2" placeholder="Company Name" required>
        <button class="btn btn-success w-100">Save & Close</button>
    </form>
</body>

</html>