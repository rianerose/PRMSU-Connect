<?php
require 'config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if(!$id || !in_array($type, ['announcement', 'event'])) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

if($type === 'event') {
    $stmt = $mysqli->prepare('SELECT e.*, u.full_name AS creator_name 
                             FROM Event e 
                             LEFT JOIN User u ON e.created_by = u.id 
                             WHERE e.id = ?');
} else {
    $stmt = $mysqli->prepare('SELECT a.*, u.full_name AS creator_name 
                             FROM Announcement a 
                             LEFT JOIN User u ON a.created_by = u.id 
                             WHERE a.id = ?');
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if(!$item) {
    echo json_encode(['error' => 'Item not found']);
    exit();
}

// Format datetime fields for HTML datetime-local input
if($type === 'event') {
    $item['datetime'] = (new DateTime($item['datetime']))->format('Y-m-d\TH:i');
    if($item['end_datetime']) {
        $item['end_datetime'] = (new DateTime($item['end_datetime']))->format('Y-m-d\TH:i');
    }
    if($item['publish_at']) {
        $item['publish_at'] = (new DateTime($item['publish_at']))->format('Y-m-d\TH:i');
    }
    $item['participation_scope'] = $item['participation_scope'] ?? 'ALL';
} else {
    $item['datetime'] = (new DateTime($item['datetime']))->format('Y-m-d\TH:i');
}

echo json_encode($item);