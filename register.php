<?php
require_once 'config/db.php';
require_once 'config/session.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_type'] === 'student' ? 'student.php' : 'staff.php'));
    exit();
}

$error = '';
$success = '';
$user_type = isset($_GET['type']) ? $_GET['type'] : '';

// Validate user type
if ($user_type && !in_array($user_type, ['student', 'staff'])) {
    header("Location: register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    $room_number = isset($_POST['room_number']) ? $_POST['room_number'] : null;
    $role = isset($_POST['role']) ? $_POST['role'] : null;

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        try {
            // Check if email already exists
            $table = ($user_type === 'student') ? 'students' : 'staff';
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email already registered";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                if ($user_type === 'student') {
                    $stmt = $pdo->prepare("
                        INSERT INTO students (name, email, password, room_number) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $email, $hashed_password, $room_number]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO staff (name, email, password, role) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $email, $hashed_password, $role]);
                }
                
                $success = "Registration successful! You can now login.";
            }
        } catch(PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hostel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Honk&family=Inconsolata:wght@200..900&family=Lugrasimo&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="signin.css">
</head>
<body class="flex-center">
    <main class="form-container">
        <form class="signin-form" id="register-form" method="POST">
            <h1 class="form-title">Register</h1>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="form-group">
                <input type="text" id="name" name="name" class="form-input" placeholder="Full Name" required>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" class="form-input" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
                <small class="form-text">Password must be at least 8 characters long</small>
            </div>

            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm Password" required>
            </div>

            <div class="form-group">
                <select id="user_type" name="user_type" class="form-input" required onchange="toggleFields()">
                    <option value="">Select User Type</option>
                    <option value="student" <?php echo $user_type === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="staff" <?php echo $user_type === 'staff' ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>

            <div class="form-group" id="room_number_group" style="display: none;">
                <input type="text" id="room_number" name="room_number" class="form-input" placeholder="Room Number">
            </div>

            <div class="form-group" id="role_group" style="display: none;">
                <select id="role" name="role" class="form-input">
                    <option value="">Select Role</option>
                    <option value="cleaner">Cleaner</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" class="submit-button">Register</button>

            <p class="form-footer">
                Already have an account? <a href="signin.php">Login here</a>
            </p>
        </form>
    </main>

    <script>
        function toggleFields() {
            const userType = document.getElementById('user_type').value;
            const roomNumberGroup = document.getElementById('room_number_group');
            const roleGroup = document.getElementById('role_group');
            
            if (userType === 'student') {
                roomNumberGroup.style.display = 'block';
                roleGroup.style.display = 'none';
                document.getElementById('room_number').required = true;
                document.getElementById('role').required = false;
            } else if (userType === 'staff') {
                roomNumberGroup.style.display = 'none';
                roleGroup.style.display = 'block';
                document.getElementById('room_number').required = false;
                document.getElementById('role').required = true;
            } else {
                roomNumberGroup.style.display = 'none';
                roleGroup.style.display = 'none';
                document.getElementById('room_number').required = false;
                document.getElementById('role').required = false;
            }
        }

        // Initialize fields based on URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
        });

        // Form validation
        document.getElementById('register-form').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html> 