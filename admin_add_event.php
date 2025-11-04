<?php
require 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='admin'){
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = $mysqli->real_escape_string($_POST['title'] ?? '');
    $desc = $mysqli->real_escape_string($_POST['description'] ?? '');
    $dt = $_POST['datetime'] ?? '';
    $end = $_POST['end_datetime'] ?? '';
    $loc = $mysqli->real_escape_string($_POST['location'] ?? '');
    $college = $mysqli->real_escape_string($_POST['college'] ?? 'ALL');
    $publish_at = !empty($_POST['publish_at']) ? $_POST['publish_at'] : date('Y-m-d H:i:s'); // Current time if not specified
    $participation_scope = in_array($_POST['participation_scope'] ?? 'ALL', ['ALL','COLLEGE']) ? $_POST['participation_scope'] : 'ALL';
    $created_by = (int)$_SESSION['user_id'];

    if($title && $dt){
        $stmt = $mysqli->prepare('INSERT INTO Event (title, description, datetime, end_datetime, location, college, publish_at, participation_scope, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssssi', $title, $desc, $dt, $end, $loc, $college, $publish_at, $participation_scope, $created_by);
        $stmt->execute();
    }
    header('Location: admin_dashboard.php');
    exit();
}
header('Location: admin_dashboard.php');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Event - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-3">Add New Event</h4>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">Event Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4"></textarea>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Start Date & Time</label>
        <input type="datetime-local" name="datetime" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">End Date & Time</label>
        <input type="datetime-local" name="end_datetime" class="form-control">
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Target College</label>
        <select name="college" class="form-select">
          <option value="ALL">All Colleges</option>
          <option>CCIT</option><option>CIT</option><option>COE</option><option>CAS</option>
          <option>CBAPA</option><option>CON</option><option>CTHM</option><option>CTE</option><option>CCJ</option>
        </select>
      </div>
    </div>
    <div class="mb-3">
      <label for="publish_at" class="form-label">Publish At</label>
      <input type="datetime-local" class="form-control" id="publish_at" name="publish_at" value="<?php echo date('Y-m-d\TH:i'); ?>">
      <small class="form-text text-muted">Leave empty to publish immediately</small>
    </div>
    <div class="mb-3">
      <label class="form-label">Participation</label>
      <select name="participation_scope" class="form-select">
        <option value="ALL">All students may participate</option>
        <option value="COLLEGE">Only students from the selected college may participate</option>
      </select>
      <div class="form-text">Choose who is allowed to participate (visibility is unchanged).</div>
    </div>
    <button type="submit" class="btn btn-primary">Save Event</button>
    <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
