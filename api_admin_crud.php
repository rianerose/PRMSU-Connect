<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Always return JSON
header('Content-Type: application/json; charset=utf-8');
// Turn off HTML error display (errors will be returned as JSON)
ini_set('display_errors', '0');
error_reporting(E_ALL);

require 'config.php';

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}

try {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        json_error('Unauthorized', 403);
    }

    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!$action || !$type) json_error('Missing action or type');

    // Normalize type
    if (!in_array($type, ['event','announcement'])) json_error('Invalid type');

    if ($action === 'update') {
        // common fields
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $datetime = $_POST['datetime'] ?? '';

        if (!$title || !$datetime) json_error('Missing required fields: title or datetime');

        if ($type === 'event') {
            // event-specific
            $end_datetime = !empty($_POST['end_datetime']) ? $_POST['end_datetime'] : null;
            $location = trim($_POST['location'] ?? '');
            $college = trim($_POST['college'] ?? 'ALL') ?: 'ALL';
            $publish_at = !empty($_POST['publish_at']) ? $_POST['publish_at'] : null;
            $participation_scope = in_array($_POST['participation_scope'] ?? 'ALL', ['ALL','COLLEGE']) ? $_POST['participation_scope'] : 'ALL';

            // ensure id provided
            if (!$id) json_error('Missing event id');

            $stmt = $mysqli->prepare('UPDATE `Event` SET title=?, description=?, datetime=?, end_datetime=?, location=?, college=?, publish_at=?, participation_scope=? WHERE id=?');
            if (!$stmt) json_error('Prepare failed: '.$mysqli->error, 500);

            // bind with appropriate types (s = string, i = int). Use nulls as strings (MySQL will accept)
            $stmt->bind_param('ssssssssi',
                $title,
                $description,
                $datetime,
                $end_datetime,
                $location,
                $college,
                $publish_at,
                $participation_scope,
                $id
            );

            if (!$stmt->execute()) {
                json_error('Execute failed: '.$stmt->error, 500);
            }
            $stmt->close();

            // If participation scope changed, cleanup invalid participants
            // Fetch current participation_scope and college to compare
            $check = $mysqli->prepare('SELECT participation_scope, college FROM `Event` WHERE id = ?');
            if ($check) {
                $check->bind_param('i', $id);
                $check->execute();
                $cur = $check->get_result()->fetch_assoc() ?: null;
                $check->close();

                // If new scope is COLLEGE, remove participants not belonging to the event college
                if ($participation_scope === 'COLLEGE') {
                    $del = $mysqli->prepare('DELETE ep FROM EventParticipation ep JOIN `User` u ON ep.user_id = u.id WHERE ep.event_id = ? AND u.college <> ?');
                    if ($del) {
                        $del->bind_param('is', $id, $college);
                        $del->execute();
                        $del->close();
                    }
                }
            }

            echo json_encode(['success' => true]);
            exit();
        } else { // announcement
            $college = trim($_POST['college'] ?? 'ALL') ?: 'ALL';
            if (!$id) json_error('Missing announcement id');

            $stmt = $mysqli->prepare('UPDATE `Announcement` SET title=?, description=?, datetime=?, college=? WHERE id=?');
            if (!$stmt) json_error('Prepare failed: '.$mysqli->error, 500);
            $stmt->bind_param('ssssi', $title, $description, $datetime, $college, $id);
            if (!$stmt->execute()) json_error('Execute failed: '.$stmt->error, 500);
            $stmt->close();

            echo json_encode(['success' => true]);
            exit();
        }
    } elseif ($action === 'delete') {
        if (!$id) json_error('Missing id for delete');

        if ($type === 'event') {
            $stmt = $mysqli->prepare('DELETE FROM `Event` WHERE id = ?');
            if (!$stmt) json_error('Prepare failed: '.$mysqli->error, 500);
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) json_error('Execute failed: '.$stmt->error, 500);
            $stmt->close();
            echo json_encode(['success' => true]);
            exit();
        } else {
            $stmt = $mysqli->prepare('DELETE FROM `Announcement` WHERE id = ?');
            if (!$stmt) json_error('Prepare failed: '.$mysqli->error, 500);
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) json_error('Execute failed: '.$stmt->error, 500);
            $stmt->close();
            echo json_encode(['success' => true]);
            exit();
        }
    } else {
        json_error('Invalid action');
    }
} catch (Throwable $e) {
    // return exception as JSON for debugging
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server exception: '.$e->getMessage()]);
    exit();
}
?>