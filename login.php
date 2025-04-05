<?php
require_once 'config/db.php';
require_once 'config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    try {
        // Prepare SQL statement based on user type
        $table = ($user_type === 'student') ? 'students' : 'staff';
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user_type;
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];

            // Redirect based on user type
            if ($user_type === 'student') {
                header("Location: student.php");
            } else {
                header("Location: staff.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
            require_once 'signin.php';
        }
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        require_once 'signin.php';
    }
} else {
    // If not a POST request, redirect to signin page
    header("Location: signin.php");
    exit();
}
?> 