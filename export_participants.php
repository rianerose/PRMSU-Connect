<?php
require 'config.php';

// Start output buffering to prevent stray HTML
ob_clean();
if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    echo 'Not logged in';
    exit();
}
if($_SESSION['role'] !== 'admin'){
    http_response_code(403);
    echo 'Forbidden';
    exit();
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if($event_id <= 0){
    die('Invalid event ID');
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="event_' . $event_id . '_participants.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header row
fputcsv($output, ['Full Name', 'Email', 'College', 'Status']);

// Fetch data
$stmt = $mysqli->prepare("
    SELECT u.full_name, u.email, u.college, ep.status 
    FROM EventParticipation ep 
    JOIN user u ON ep.user_id = u.id 
    WHERE ep.event_id = ?
");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();

// Output each row
while($row = $result->fetch_assoc()){
    $status = ($row['status'] === 'going') ? 'Participating' : 'Not Participating';
    fputcsv($output, [$row['full_name'], $row['email'], $row['college'], $status]);
}

fclose($output);
exit();
?>
