<?php require 'config.php'; if(isset($_SESSION['user_id'])){ header('Location: dashboard.php'); exit(); } ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign up - Announcement Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#F4F6FF;">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4>Create account</h4>
          <form method="post" action="api_signup.php">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Full name</label>
                <input name="full_name" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Username</label>
                <input name="username" class="form-control" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">College</label>
                <select name="college" class="form-select" required>
                  <option value="">Choose...</option>
                  <option>CCIT</option><option>CIT</option><option>COE</option><option>CAS</option>
                  <option>CBAPA</option><option>CON</option><option>CTHM</option><option>CTE</option><option>CCJ</option>
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <a href="index.php" class="btn btn-link">Back</a>
              <button class="btn" style="background:#10375C;color:#F4F6FF">Sign up</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
