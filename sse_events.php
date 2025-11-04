<?php
require 'config.php';
// release session lock early for concurrent requests
session_write_close();
// SSE endpoint that streams participation counts for all published events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
ignore_user_abort(true);
set_time_limit(0);

$lastData = null;
while (true) {
    $out = [];

    // Only include published events (publish_at IS NULL OR <= NOW())
    $res = $mysqli->query("SELECT id FROM Event WHERE publish_at IS NULL OR publish_at <= NOW()");
    $ids = [];
    while($r = $res->fetch_assoc()){ $ids[] = (int)$r['id']; }

    if(count($ids)===0){
        // send heartbeat
        echo "data: {\"heartbeat\":1}\n\n";
        @ob_flush(); flush();
        sleep(3);
        continue;
    }

    $idList = implode(',', $ids);
    $q = "SELECT event_id, status, COUNT(*) as c FROM EventParticipation WHERE event_id IN ($idList) GROUP BY event_id, status";
    $res2 = $mysqli->query($q);
    $map = [];
    while($r = $res2->fetch_assoc()){
        $eid = $r['event_id'];
        if(!isset($map[$eid])) $map[$eid] = ['going'=>0,'not_going'=>0];
        if($r['status']=='going') $map[$eid]['going'] = (int)$r['c'];
        if($r['status']=='not_going') $map[$eid]['not_going'] = (int)$r['c'];
    }

    $data = json_encode($map);
    if($data !== $lastData){
        echo "data: $data\n\n";
        @ob_flush(); flush();
        $lastData = $data;
    } else {
        // heartbeat to keep connection alive
        echo "data: {\"heartbeat\":1}\n\n";
        @ob_flush(); flush();
    }

    sleep(2);
}