<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Not logged in';
    exit();
}

$uid = (int)$_SESSION['user_id'];
$role = strtolower(trim($_SESSION['role'] ?? ''));
$user_college = trim($_SESSION['college'] ?? '');

$q = trim($_GET['q'] ?? '');
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = '1=1';
$params = [];
$types = '';

// Search conditions
if ($q !== '') {
    $where .= ' AND (e.title LIKE ? OR e.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types .= 'ss';
}

// Only show published events to non-admins
if ($role !== 'admin') {
    $where .= ' AND (e.publish_at IS NULL OR e.publish_at <= NOW())';
}

// Add pagination params
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$sql = "
SELECT e.*,
       COALESCE(u.full_name, '') AS creator_name,
       COALESCE(e.participation_scope,'ALL') AS participation_scope,
       (SELECT COUNT(*) FROM EventParticipation WHERE event_id = e.id AND status = 'going') AS going_count,
       (SELECT COUNT(*) FROM EventParticipation WHERE event_id = e.id AND status = 'not_going') AS not_going_count
FROM Event e
LEFT JOIN `User` u ON e.created_by = u.id
WHERE $where
ORDER BY e.datetime DESC
LIMIT ? OFFSET ?
";

$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo 'Prepare failed: ' . htmlspecialchars($mysqli->error);
    exit();
}

// helper to pass params by reference to bind_param
function refValues(array $arr) {
    $refs = [];
    foreach ($arr as $k => $v) $refs[$k] = &$arr[$k];
    return $refs;
}

$bind_params = array_merge([$types], $params);
if (!call_user_func_array([$stmt, 'bind_param'], refValues($bind_params))) {
    http_response_code(500);
    echo 'Bind failed: ' . htmlspecialchars($stmt->error);
    exit();
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo 'Execute failed: ' . htmlspecialchars($stmt->error);
    exit();
}

$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo '<div class="alert alert-info">No events found.</div>';
    exit();
}

while ($r = $res->fetch_assoc()) {
    $id = (int)$r['id'];
    $title = htmlspecialchars($r['title']);
    $desc = nl2br(htmlspecialchars($r['description']));
    $dt = !empty($r['datetime']) ? (new DateTime($r['datetime']))->format('M j, Y g:ia') : 'N/A';
    $end = !empty($r['end_datetime']) ? (new DateTime($r['end_datetime']))->format('M j, Y g:ia') : 'N/A';
    $loc = htmlspecialchars($r['location'] ?? '');
    $col = htmlspecialchars($r['college'] ?? 'ALL');
    $creator = htmlspecialchars($r['creator_name'] ?? 'Unknown');
    $going_count = (int)($r['going_count'] ?? 0);
    $not_going_count = (int)($r['not_going_count'] ?? 0);
    $participation_scope = $r['participation_scope'] ?? 'ALL';

    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>{$title}</h5>";
    echo "<p class='card-text'>{$desc}</p>";
    echo "<div class='text-muted small mb-2'>";
    echo "<strong>Start:</strong> {$dt} | <strong>End:</strong> {$end}<br>";
    echo "<strong>Location:</strong> {$loc} | <strong>For:</strong> {$col}<br>";
    echo "<strong>Created by:</strong> {$creator}";
    echo "</div>";

    // ADDED: show participation scope badge
    $ps_label = $participation_scope === 'ALL' ? 'All students may participate' : 'Only selected college may participate';
    $ps_class = $participation_scope === 'ALL' ? 'badge bg-success' : 'badge bg-warning text-dark';
    echo "<div class='mb-2 small'><span class='{$ps_class}'>Participation: {$ps_label}</span></div>";

    // participation counts
    echo "<div class='mb-2 small'>";
    echo "<strong>Going:</strong> <span id='going_count_{$id}'>{$going_count}</span> | ";
    echo "<strong>Not Going:</strong> <span id='not_going_count_{$id}'>{$not_going_count}</span>";
    echo "</div>";

    // Student participation rules & visibility note
    if ($role === 'student') {
        // Allow participation when:
        // - participation_scope == 'ALL'  => any student may participate (regardless of event.college)
        // - participation_scope == 'COLLEGE' => only students whose college matches event.college may participate
        $can_participate = false;
        if ($participation_scope === 'ALL') {
            $can_participate = true;
        } elseif ($participation_scope === 'COLLEGE' && $user_college !== '' && $user_college === ($r['college'] ?? '')) {
            $can_participate = true;
        }

        // show target audience badge when event is for a specific college
        if (($r['college'] ?? '') !== '' && ($r['college'] ?? '') !== 'ALL') {
            if (($r['college'] ?? '') !== $user_college) {
                echo "<div class='text-muted small mb-2'><span class='badge bg-secondary'>For {$col} Students</span></div>";
            } else {
                echo "<div class='text-muted small mb-2'><span class='badge bg-info text-dark'>For your college</span></div>";
            }
        }

        if ($can_participate) {
            $pstmt = $mysqli->prepare('SELECT status FROM EventParticipation WHERE user_id = ? AND event_id = ?');
            if ($pstmt) {
                $pstmt->bind_param('ii', $uid, $id);
                $pstmt->execute();
                $pinfo = $pstmt->get_result()->fetch_assoc();
                $pstmt->close();
            } else {
                $pinfo = null;
            }

            $user_status = $pinfo['status'] ?? '';
            $going_class = $user_status === 'going' ? 'btn-success active' : 'btn-outline-success';
            $not_going_class = $user_status === 'not_going' ? 'btn-danger active' : 'btn-outline-danger';

            echo "<div class='btn-group'>";
            echo "<button class='btn btn-sm {$going_class}' data-part-event='{$id}' data-part-type='going'>Going <span class='badge bg-secondary' id='going_{$id}'>{$going_count}</span></button>";
            echo "<button class='btn btn-sm {$not_going_class}' data-part-event='{$id}' data-part-type='not_going'>Not Going <span class='badge bg-secondary' id='not_going_{$id}'>{$not_going_count}</span></button>";
            echo "</div>";
        } else {
            echo "<div class='text-muted small'>You cannot participate in this event.</div>";
        }
    }

    // Admin controls + publish status
    if ($role === 'admin') {
        echo "<div class='text-muted small mb-2'>";
        if (!empty($r['publish_at'])) {
            try {
                $publish_time = new DateTime($r['publish_at']);
                $now = new DateTime();
                $is_published = $publish_time <= $now;
                echo "<strong>Status:</strong> ";
                if ($is_published) {
                    echo "<span class='text-success'>Published</span>";
                } else {
                    echo "<span class='text-warning'>Scheduled for " . $publish_time->format('M j, Y g:ia') . "</span>";
                }
            } catch (Exception $e) {
                echo "<strong>Status:</strong> <span class='text-muted'>Unknown</span>";
            }
        } else {
            echo "<strong>Status:</strong> <span class='text-success'>Published</span>";
        }
        echo "</div>";

        echo "<div class='btn-group float-end'>";
        echo "<a href='export_participants.php?event_id={$id}' class='btn btn-sm btn-outline-primary'>Export</a>";
        echo "<button class='btn btn-sm btn-outline-primary' onclick='editItem(\"event\", {$id})'>Edit</button>";
        echo "<button class='btn btn-sm btn-outline-danger' onclick='deleteItem(\"event\", {$id})'>Delete</button>";
        echo "</div>";
    }

    echo "</div></div>";
}

$stmt->close();
?>