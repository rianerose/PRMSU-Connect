<?php
require 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin'){
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = $mysqli->real_escape_string($_POST['title'] ?? '');
    $desc = $mysqli->real_escape_string($_POST['description'] ?? '');
    $dt = $_POST['datetime'] ?? '';
    $college = $mysqli->real_escape_string($_POST['college'] ?? 'ALL');
    $created_by = (int)$_SESSION['user_id'];

    if($title && $dt){
        $stmt = $mysqli->prepare('INSERT INTO Announcement (title, description, datetime, college, created_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssi', $title, $desc, $dt, $college, $created_by);
        $stmt->execute();
    }
    header('Location: admin_dashboard.php');
    exit();
}
