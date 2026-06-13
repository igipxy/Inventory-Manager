<?php
// Start session if you need to pass success messages via session, 
// though here we will use a URL parameter for the redirect.
session_start();

// Include your database connection
// Example db.php content: $pdo = new PDO("mysql:host=localhost;dbname=your_db", "user", "pass");
require_once 'db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture and sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Basic validation
    if (empty($username) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            // 3. Check if the username already exists
            $checkSql = "SELECT id FROM users WHERE username = :username LIMIT 1";
            $stmt = $pdo->prepare($checkSql);
            $stmt->execute([':username' => $username]);
            
            if ($stmt->fetch()) {
                $error_message = "That username is already taken. Please choose another.";
            } else {
                // 4. Hash the password securely
                // PASSWORD_DEFAULT ensures PHP uses the strongest available algorithm (currently BCRYPT)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 5. Insert the new user into the database
                $insertSql = "INSERT INTO users (username, password) VALUES (:username, :password)";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([
                    ':username' => $username,
                    ':password' => $hashed_password
                ]);

                // 6. Redirect to login page on success
                header("Location: login.php?status=registered");
                exit; // Always exit after a header redirect
            }
        } catch (PDOException $e) {
            // Log this error in a real production environment
            $error_message = "A database error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 register-card">
                
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark">Create Account</h2>
                    <p class="text-muted">Sign up for the Inventory Manager</p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="register.php" method="POST">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                       required autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="8">
                                <div class="form-text">Minimum 8 characters.</div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <span class="text-muted">Already have an account?</span> 
                                <a href="login.php" class="text-decoration-none fw-semibold">Sign in here</a>
                            </div>

                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>