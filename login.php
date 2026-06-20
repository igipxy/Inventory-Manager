<?php
// Always start the session before any output
session_start();

// If the user is already logged in, redirect them to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_message = '';

$defaultUsername = 'admin';
$defaultPassword = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $username === 'admin' &&
        $password === 'admin123'
    ) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';

        header('Location: index.php');
        exit;
    }

    $error_message = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="text-center fw-bold mb-4 text-dark">Sign In</h3>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger p-2 text-center" role="alert">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>

                    <div class="text-center mt-2">
                        <span class="text-muted">Don't have an account?</span>
                        <a href="register.php" class="text-decoration-none fw-semibold"> Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>