<?php
require 'config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

// release the session lock so the browser can make concurrent requests
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

try {
    $eventRes = $mysqli->query('SELECT id FROM Event WHERE publish_at IS NULL OR publish_at <= NOW()');
    if (!$eventRes) {
        throw new RuntimeException('Failed to load events: ' . $mysqli->error);
    }

    $eventIds = [];
    while ($row = $eventRes->fetch_assoc()) {
        $eventIds[] = (int)$row['id'];
    }
    $eventRes->free();

    if (empty($eventIds)) {
        echo json_encode(['events' => [], 'generated_at' => time()]);
        exit();
    }

    $idList = implode(',', $eventIds);
    $countRes = $mysqli->query("SELECT event_id, status, COUNT(*) AS total FROM EventParticipation WHERE event_id IN ($idList) GROUP BY event_id, status");
    if (!$countRes) {
        throw new RuntimeException('Failed to load participation counts: ' . $mysqli->error);
    }

    $counts = [];
    foreach ($eventIds as $id) {
        $counts[$id] = ['going' => 0, 'not_going' => 0];
    }

    while ($row = $countRes->fetch_assoc()) {
        $eventId = (int)$row['event_id'];
        $status = $row['status'];
        if (!isset($counts[$eventId])) {
            $counts[$eventId] = ['going' => 0, 'not_going' => 0];
        }
        if ($status === 'going') {
            $counts[$eventId]['going'] = (int)$row['total'];
        } elseif ($status === 'not_going') {
            $counts[$eventId]['not_going'] = (int)$row['total'];
        }
    }
    $countRes->free();

    echo json_encode([
        'events' => $counts,
        'generated_at' => time()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load participation counts']);
}