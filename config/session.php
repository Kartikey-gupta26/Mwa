<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: signin.php");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if ($_SESSION['user_type'] === 'student') {
            header("Location: student.php");
        } else {
            header("Location: staff.php");
        }
        exit();
    }
}
?> 