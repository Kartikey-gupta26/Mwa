<?php
require_once 'config/db.php';
require_once 'config/session.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $request_type = $_POST['request_type'];
    $staff_id = $_SESSION['user_id'];

    try {
        if ($request_type === 'cleaning') {
            $stmt = $pdo->prepare("
                UPDATE cleaning_requests 
                SET status = 'completed', 
                    staff_id = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE complaint_requests 
                SET status = 'resolved', 
                    staff_id = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
        }
        
        $stmt->execute([$staff_id, $request_id]);
        
        // Redirect back to staff dashboard
        header("Location: staff.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error updating request: " . $e->getMessage();
        header("Location: staff.php?error=" . urlencode($error));
        exit();
    }
} else {
    header("Location: staff.php");
    exit();
}
?> 