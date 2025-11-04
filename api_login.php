<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ header('Location: index.php'); exit(); }
$email = $mysqli->real_escape_string($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
$stmt = $mysqli->prepare('SELECT id,password,role FROM `User` WHERE email=? LIMIT 1');
$stmt->bind_param('s',$email);
$stmt->execute();
$stmt->bind_result($id,$hash,$role);
if($stmt->fetch()){
  if(password_verify($pass,$hash)){
    $_SESSION['user_id']=$id;
    $_SESSION['role']=$role;
    header('Location: dashboard.php');
    exit();
  }
}
echo 'Invalid credentials'; 
