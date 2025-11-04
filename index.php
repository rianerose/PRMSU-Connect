<?php require 'config.php'; if(isset($_SESSION['user_id'])){ header('Location: dashboard.php'); exit(); } ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Announcement Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#F4F6FF;">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm" style="border-radius:12px;">
        <div class="card-body p-4" style="background:linear-gradient(90deg,#10375C, #00B0FF); color:#fff;">
          <h3>Announcement Dashboard</h3>
          <p class="mb-0">Login to participate or manage events.</p>
        </div>
        <div class="card-body p-4">
          <form id="loginForm" method="post" action="api_login.php">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button class="btn" style="background:#10375C;color:#F4F6FF">Login</button>
              <a href="signup.php" class="btn btn-link">Sign up</a>
            </div>
            <div id="msg" class="mt-3 text-danger"></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e){
  // default form submit - allow PHP handle and redirect
});
</script>
</body>
</html>
