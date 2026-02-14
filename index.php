<?php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    }
    else {
        // ADDED 'full_name' to the SELECT query
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, full_name FROM Users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($password === $user['password_hash']) {
                    session_regenerate_id();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // SAVING THE NAME to the Session Badge
                    $_SESSION['full_name'] = $user['full_name'];

                    header("Location: dashboard.php");
                    exit();
                }
                else {
                    $error = "Invalid username or password.";
                }
            }
            else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        }
        else {
            $error = "Database error: " . $conn->error;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CentralPharmaCee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: var(--bs-body-bg);
        }

        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>

<body>
    <main class="form-signin">
        <div class="card shadow">
            <div class="card-body">
                <h1 class="h3 mb-3 fw-normal text-center">CentralPharmaCee Login</h1>

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php
endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER[" PHP_SELF"]); ?>" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="floatingInput" name="username"
                            placeholder="Username" required>
                        <label for="floatingInput">Username</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="floatingPassword" name="password"
                            placeholder="Password" required>
                        <label for="floatingPassword">Password</label>
                    </div>
                    <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
                    <p class="mt-4 mb-0 text-muted text-center">&copy; 2026 CentralPharmaCee</p>
                </form>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>