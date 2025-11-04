<?php require 'config.php';
header('Content-Type: application/json');
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='student'){ echo json_encode(['error'=>'Not authorized']); exit(); }
$uid = (int)$_SESSION['user_id'];
$event_id = (int)($_POST['event_id'] ?? 0);
if(!$event_id){ echo json_encode(['error'=>'Missing event_id']); exit(); }

// delete participation
$stmt = $mysqli->prepare('DELETE FROM EventParticipation WHERE event_id=? AND user_id=?');
$stmt->bind_param('ii',$event_id,$uid);
$stmt->execute();

// return updated counts
$row = $mysqli->query("SELECT status,COUNT(*) as c FROM EventParticipation WHERE event_id=$event_id GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$going=0;$not_going=0;
foreach($row as $r){ if($r['status']=='going') $going=$r['c']; if($r['status']=='not_going') $not_going=$r['c']; }
echo json_encode(['success'=>true,'going'=>$going,'not_going'=>$not_going]);
