<?php require 'config.php';
// Export participants for an event as CSV
if(!isset($_GET['event_id'])){ die('Missing event_id'); }
$event_id = (int)$_GET['event_id'];
$ev = $mysqli->query('SELECT * FROM Event WHERE id='.$event_id)->fetch_assoc();
if(!$ev){ die('Event not found'); }

// Authorization: admin can export any. Students can export only if event is ALL or matches their college
$is_admin = isset($_SESSION['role']) && $_SESSION['role']==='admin';
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ die('Not authorized');
// release session lock early for concurrent requests
session_write_close();
 }

// fetch participants
$res = $mysqli->query('SELECT fullname, status, created_at FROM EventParticipation WHERE event_id='.$event_id.' ORDER BY created_at ASC');
$rows = [];
while($r = $res->fetch_assoc()){
  $rows[] = $r;
}

// output CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="event_'.$event_id.'_participants.csv"');

$out = fopen('php://output','w');
fputcsv($out, ['Full Name','Status','Signed At']);
foreach($rows as $r){ fputcsv($out, [$r['fullname'],$r['status'],$r['created_at']]); }
fclose($out);
exit();
