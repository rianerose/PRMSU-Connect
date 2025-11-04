<?php
require 'config.php';
if(!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Not logged in';
    exit();
}

$role = $_SESSION['role'] ?? '';
$user_college = $_SESSION['college'] ?? '';

$sql = "SELECT a.*, u.full_name AS creator_name 
        FROM Announcement a
        LEFT JOIN User u ON a.created_by = u.id
        WHERE (a.college = 'ALL' OR a.college = ?)
        ORDER BY a.datetime DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $user_college);
$stmt->execute();
$res = $stmt->get_result();

if(!$res || $res->num_rows === 0) {
    echo '<div class="alert alert-info">No announcements found.</div>';
    exit();
}

while($r = $res->fetch_assoc()) {
    $id = (int)$r['id'];
    $title = htmlspecialchars($r['title']);
    $desc = nl2br(htmlspecialchars($r['description']));
    $dt = (new DateTime($r['datetime']))->format('M j, Y g:ia');
    $col = htmlspecialchars($r['college']);
    $creator = htmlspecialchars($r['creator_name'] ?? 'Unknown');

    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>$title</h5>";
    echo "<p class='card-text'>$desc</p>";
    echo "<div class='text-muted small'>";
    echo "<strong>Posted:</strong> $dt | <strong>For:</strong> $col<br>";
    echo "<strong>Created by:</strong> $creator";
    echo "</div>";

    if($role === 'admin') {
        echo "<div class='btn-group float-end'>";
        echo "<button class='btn btn-sm btn-outline-primary' onclick='editItem(\"announcement\", $id)'>Edit</button>";
        echo "<button class='btn btn-sm btn-outline-danger' onclick='deleteItem(\"announcement\", $id)'>Delete</button>";
        echo "</div>";
    }

    echo "</div></div>";
}
$stmt->close();
?>
