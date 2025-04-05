<?php
require_once 'config/db.php';
require_once 'config/session.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: signin.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle marking cleaning request as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_cleaned'])) {
    $request_id = $_POST['request_id'];
    $staff_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE cleaning_requests 
            SET status = 'completed', staff_id = ?, completed_at = NOW() 
            WHERE id = ? AND status = 'pending'
        ");
        $stmt->execute([$staff_id, $request_id]);
        $success_message = "Cleaning request marked as completed!";
    } catch(PDOException $e) {
        $error_message = "Error updating request: " . $e->getMessage();
    }
}

// Get all pending cleaning requests with student details
try {
    $stmt = $pdo->prepare("
        SELECT cr.*, s.name as student_name, s.room_number
        FROM cleaning_requests cr 
        JOIN students s ON cr.student_id = s.id 
        WHERE cr.status = 'pending' 
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute();
    $cleaning_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching requests: " . $e->getMessage();
    $cleaning_requests = [];
}

// Handle marking complaint as resolved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_resolved'])) {
    $request_id = $_POST['request_id'];
    $staff_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE complaint_requests 
            SET status = 'resolved', staff_id = ? 
            WHERE id = ? AND status = 'pending'
        ");
        $stmt->execute([$staff_id, $request_id]);
        $success_message = "Complaint marked as resolved!";
    } catch(PDOException $e) {
        $error_message = "Error updating complaint: " . $e->getMessage();
    }
}

// Get all pending complaint requests with student details
try {
    $stmt = $pdo->prepare("
        SELECT cr.*, s.name as student_name, s.room_number
        FROM complaint_requests cr 
        JOIN students s ON cr.student_id = s.id 
        WHERE cr.status = 'pending' 
        ORDER BY cr.created_at DESC
    ");
    $stmt->execute();
    $complaint_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching complaints: " . $e->getMessage();
    $complaint_requests = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Hostel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Honk&family=Inconsolata:wght@200..900&family=Jersey+25&family=Lugrasimo&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="staff.css">
</head>
<body>
    <header>
        <h1>Hostel Buddy - Staff Dashboard</h1>
        <nav>
            <a href="#">Home</a>
            <a href="#cleaning-requests">Cleaning Requests</a>
            <a href="#complaint-requests">Complaint Requests</a>
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </header>

    <?php if ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <section id="cleaning-requests" class="cleaning-requests">
        <div class="container">
            <h2>Room Cleaning Requests</h2>
            <div class="request-grid">
                <?php if (empty($cleaning_requests)): ?>
                    <p class="no-requests">No pending cleaning requests.</p>
                <?php else: ?>
                    <?php foreach ($cleaning_requests as $request): ?>
                        <div class="request-card">
                            <h3>Room <?php echo htmlspecialchars($request['room_number']); ?></h3>
                            <p>Requested by: <?php echo htmlspecialchars($request['student_name']); ?></p>
                            <p>Requested on: <?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?></p>
                            <p>Status: <span class="status-pending">Pending</span></p>
                            <form method="POST" class="mark-completed-form">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="mark_cleaned" class="complete-btn">
                                    <i class="fas fa-check"></i> Mark as Completed
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="complaint-requests" class="complaint-requests">
        <div class="container">
            <h2>Complaint Requests</h2>
            <div class="request-grid">
                <?php if (empty($complaint_requests)): ?>
                    <p class="no-requests">No pending complaint requests.</p>
                <?php else: ?>
                    <?php foreach ($complaint_requests as $request): ?>
                        <div class="request-card">
                            <h3>Room <?php echo htmlspecialchars($request['room_number']); ?></h3>
                            <p>Requested by: <?php echo htmlspecialchars($request['student_name']); ?></p>
                            <p>Description: <?php echo htmlspecialchars($request['description']); ?></p>
                            <p>Submitted on: <?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?></p>
                            <p>Status: <span class="status-pending">Pending</span></p>
                            <form method="POST" class="mark-resolved-form">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="mark_resolved" class="resolve-btn">
                                    <i class="fas fa-check"></i> Mark as Resolved
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <p>Contact us: info@hostelmanagement.com | +123 456 7890</p>
        <div class="social-icons">
            <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.x.com/" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.linkedin.com/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html> 