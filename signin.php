<?php
require_once 'config/db.php';
require_once 'config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Management System</title>
    <link rel="stylesheet" href="signin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="flex-center">
    <main class="form-container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message show">
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']); // Clear the error after displaying
                ?>
            </div>
        <?php endif; ?>

        <form class="signin-form" id="signin-form" action="login.php" method="POST">
            <h1 class="form-title">Sign in</h1>

            <div class="form-group">
                <input type="email" id="email" name="email" class="form-input" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
            </div>

            <div class="form-group">
                <select name="user_type" class="form-input" required>
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <div class="form-check">
                <input type="checkbox" id="remember-me" class="form-checkbox">
                <label for="remember-me">Remember me</label>
            </div>

            <div class="signin-button">
                <button type="submit" class="submit-button">Sign in</button>
            </div>

            <p class="form-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
    </main>

    <script src="signin.js"></script>
</body>
</html> 