<?php 
require 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if($_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit();
} else if($_SESSION['role'] === 'student') {
    header('Location: student_dashboard.php');
    exit();
} else {
    // Invalid role
    session_destroy();
    header('Location: index.php');
    exit();
}
