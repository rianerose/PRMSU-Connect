<?php
require 'config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='student'){ 
    echo json_encode(['error'=>'Not authorized']); 
    exit(); 
}

$uid = (int)$_SESSION['user_id'];
$event_id = (int)($_POST['event_id'] ?? 0);
$status = $_POST['status'] ?? '';

if(!$event_id || !in_array($status, ['going', 'not_going'])) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

try {
    // Start transaction
    $mysqli->begin_transaction();

    // Remove any existing participation
    $stmt = $mysqli->prepare('DELETE FROM EventParticipation WHERE user_id=? AND event_id=?');
    $stmt->bind_param('ii', $uid, $event_id);
    $stmt->execute();

    // Add new participation
    $stmt = $mysqli->prepare('INSERT INTO EventParticipation (user_id, event_id, status) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $uid, $event_id, $status);
    $stmt->execute();

    // Get updated counts
    $stmt = $mysqli->prepare('SELECT 
        SUM(CASE WHEN status="going" THEN 1 ELSE 0 END) as going,
        SUM(CASE WHEN status="not_going" THEN 1 ELSE 0 END) as not_going
        FROM EventParticipation WHERE event_id=?');
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $mysqli->commit();
    
    echo json_encode([
        'success' => true,
        'going' => (int)$result['going'],
        'not_going' => (int)$result['not_going']
    ]);

} catch(Exception $e) {
    $mysqli->rollback();
    echo json_encode(['error' => 'Database error']);
}
