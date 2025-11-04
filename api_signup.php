<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ header('Location: signup.php'); exit(); }

$full = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';
$college = trim($_POST['college'] ?? '');

if(!$full || !$email || !$pass || !$college || !$username){
  die('Missing fields. All fields required.');
}

// basic email check
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
  die('Invalid email address.');
}

// check duplicate email or username
$stmt = $mysqli->prepare('SELECT id FROM `User` WHERE email=? OR username=? LIMIT 1');
$stmt->bind_param('ss', $email, $username);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){
  die('Email or username already exists.');
}
$stmt->close();

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO `User` (role,email,username,password,full_name,college) VALUES ("student",?,?,?,?,?)');
$stmt->bind_param('sssss',$email,$username,$hash,$full,$college);
if($stmt->execute()){
  // auto-login
  $_SESSION['user_id'] = $mysqli->insert_id;
  $_SESSION['role'] = 'student';
  header('Location: dashboard.php');
  exit();
} else {
  echo 'Error: '.$mysqli->error;
}
