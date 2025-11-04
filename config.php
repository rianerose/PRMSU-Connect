<?php
// Database config - update with your credentials
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'AnnouncementDB';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error'=>'DB connect failed: '.$mysqli->connect_error]);
    exit();
}
$mysqli->set_charset('utf8mb4');
session_start();
?>
