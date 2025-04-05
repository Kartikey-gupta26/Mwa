<?php
require_once 'config/db.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Check in students table
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['name'] = $student['name'];
            $_SESSION['room_number'] = $student['room_number'];
            header("Location: student.php");
            exit();
        }

        // Check in staff table
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
        $stmt->execute([$email]);
        $staff = $stmt->fetch();

        if ($staff && password_verify($password, $staff['password'])) {
            $_SESSION['user_id'] = $staff['id'];
            $_SESSION['user_type'] = 'staff';
            $_SESSION['name'] = $staff['name'];
            header("Location: staff.php");
            exit();
        }

        $error = "Invalid email or password";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
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
        <form class="signin-form" id="signin-form" method="POST">
            <h1 class="form-title">Sign in</h1>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-group">
                <input type="email" id="email" name="email" class="form-input" placeholder="name@example.com" required>
                <small class="error-message" id="email-error"></small>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
                <small class="error-message" id="password-error"></small>
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