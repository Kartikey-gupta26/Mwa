<?php
require_once 'config/db.php';
require_once 'config/session.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: signin.php");
    exit();
}

$success_message = '';
$error_message = '';

// Get student's room number from database
try {
    $stmt = $pdo->prepare("SELECT room_number FROM students WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $room_number = $student ? $student['room_number'] : '';
} catch(PDOException $e) {
    $room_number = '';
    $error_message = "Error fetching student information: " . $e->getMessage();
}

// Handle cleaning request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_cleaning'])) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $student_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO cleaning_requests (student_id, room_number, status) 
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$student_id, $room_number]);
        $success_message = "Cleaning request submitted successfully!";
    } catch(PDOException $e) {
        $error_message = "Error submitting request: " . $e->getMessage();
    }
}

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $student_id = $_SESSION['user_id'];

    // Get student's room number from the database
    try {
        $stmt = $pdo->prepare("SELECT room_number FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            $room_number = $student['room_number'];
            
            $stmt = $pdo->prepare("
                INSERT INTO complaint_requests (student_id, room_number, description, status) 
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->execute([$student_id, $room_number, $description]);
            $success_message = "Complaint submitted successfully!";
        } else {
            $error_message = "Error: Student information not found.";
        }
    } catch(PDOException $e) {
        $error_message = "Error submitting complaint: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Honk&family=Inconsolata:wght@200..900&family=Jersey+25&family=Lugrasimo&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h1>Hostel Buddy - Student Dashboard</h1>
        <nav>
            <a href="#">Home</a>
            <a href="#about">About</a>
            <a href="#book-cleaning">Book Room Cleaning</a>
            <a href="#file-complaint">File Complaint</a>
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

    <section class="hero">
        <div>
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
            <p>Manage your room cleaning and complaints easily.</p>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Us</h2>
                    <p>Welcome to Hostel Buddy, your all-in-one solution for hostel management. We aim to provide a seamless and efficient experience for both students and staff. Our platform simplifies tasks such as room cleaning bookings and complaint filing, ensuring a comfortable living environment for all residents.</p>
                    <p>Our mission is to enhance student living by providing tools that promote cleanliness, efficiency, and communication within the hostel community.</p>
                </div>
                <div class="about-image">
                    <img src="https://via.placeholder.com/400" alt="About Us Image">
                </div>
            </div>
        </div>
    </section>

    <section id="book-cleaning" class="book-cleaning">
        <div class="container">
            <div class="cleaning-content">
                <div class="cleaning-image">
                    <img src="https://via.placeholder.com/400" alt="Book Cleaning Image">  
                </div>
                <div class="cleaning-text">
                    <h2>Book Room Cleaning</h2>
                    <p>Schedule your room cleaning with just a few clicks. Choose your preferred date and time, and our staff will take care of the rest.</p>
                    <form class="cleaning-form" method="POST">
                        <input type="hidden" name="submit_cleaning" value="1">
                        <label for="room-number">Room Number:</label>
                        <input type="text" id="room-number" name="room_number" value="<?php echo htmlspecialchars($room_number); ?>" required>
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required>
                        <label for="time">Time:</label>
                        <input type="time" id="time" name="time" required>
                        <button type="submit">Book Cleaning</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section id="file-complaint" class="file-complaint">
        <div class="container">
            <div class="complaint-content">
                <div class="complaint-text">
                    <h2>File a Complaint</h2>
                    <p>If you have any issues or concerns, please let us know. Fill out the form below to file a complaint, and our team will address it promptly.</p>
                    <form class="complaint-form" method="POST">
                        <input type="hidden" name="submit_complaint" value="1">
                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" name="subject" required>
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                        <button type="submit">Submit Complaint</button>
                    </form>
                </div>
                <div class="complaint-image">
                    <img src="https://via.placeholder.com/400" alt="File Complaint Image">
                </div>
            </div>
        </div>
    </section>

    <footer id="contact-us">
        <p>Contact us: info@hostelmanagement.com | +123 456 7890</p>
        <div class="social-icons">
            <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.x.com/" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.linkedin.com/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </footer>
    <link rel="stylesheet" href="student.css">
    <script src="script.js"></script>
</body>
</html> 