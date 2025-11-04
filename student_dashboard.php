<?php
require 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit();
}
$u = $mysqli->query('SELECT * FROM `User` WHERE id='.(int)$_SESSION['user_id'])->fetch_assoc();
$_SESSION['college'] = $u['college']; $_SESSION['full_name'] = $u['full_name'];
?>
<!doctype html><html><head>
<link rel='stylesheet' href='assets/style.css'>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body style="background:#F4F6FF;">
<nav class="navbar navbar-expand bg-light mb-4" style="background:#10375C;color:#F4F6FF;">
  <div class="container-fluid">
    <a class="navbar-brand" style="color:#000000">Student - <?php echo htmlspecialchars($u['full_name']); ?></a>
    <div><a href="logout.php" class="btn btn-sm" style="background:#F3C623;color:#10375C">Logout</a></div>
  </div>
</nav>
<div class="container">
  <div class="row">
    <div class="col-md-8">
      <h5>Announcements for <?php echo htmlspecialchars($u['college']); ?></h5>
      <div id="ann"></div>
      <h5 class="mt-3">Events</h5>
      <div class="d-flex mb-2"><input id="ev_search" class="form-control me-2" placeholder="Search events..."><select id="ev_college" class="form-select me-2"><option value="">All colleges</option><option value="ALL">All students (ALL)</option><option>CCIT</option><option>CIT</option><option>COE</option><option>CAS</option><option>CBAPA</option><option>CON</option><option>CTHM</option><option>CTE</option><option>CCJ</option></select><button class="btn btn-sm" onclick="loadEvents(1)" style="background:#10375C;color:#F4F6FF">Filter</button></div>
      <div id="events" class="card-grid"></div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6>My Info</h6>
          <p><?php echo htmlspecialchars($u['full_name']); ?><br><small><?php echo htmlspecialchars($u['email']); ?></small></p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>

// helper: fetch with timeout using AbortController
function fetchWithTimeout(url, opts = {}, timeout = 8000){
  const controller = new AbortController();
  const signal = controller.signal;
  const combined = Object.assign({}, opts, { signal });
  const id = setTimeout(()=>controller.abort(), timeout);
  return fetch(url, combined).finally(()=>clearTimeout(id));
}
async function reload(){
  document.getElementById('ann').innerHTML = await fetchWithTimeout('api_get_announcements.php').then(r=>r.text());
  loadEvents(1,20);
}

function loadEvents(page=1, per_page=20){
  const q = document.getElementById('ev_search').value;
  const college = document.getElementById('ev_college').value;
  fetch('api_get_events.php?q='+encodeURIComponent(q)+'&college='+encodeURIComponent(college)+'&page='+page).then(r=>r.text()).then(html=>{ document.getElementById('events').innerHTML = html; });
}

// use chained scheduling to avoid overlapping requests if a previous request is still running
reload();
(function scheduleReload(){
  setTimeout(async function(){
    try{ await reload(); } catch(e){ console.error('reload error', e); }
    scheduleReload();
  }, 5000);
})();
</script>
<script>
// SSE client to receive live participation counts
if(typeof(EventSource) !== 'undefined'){
  const src = new EventSource('sse_events.php');
  src.onmessage = function(e){
    try{
      const d = JSON.parse(e.data);
      if(d.heartbeat) return;
      // d is an object: {event_id: {going: n, not_going: m}, ...}
      for(const [eid, obj] of Object.entries(d)){
        const g = document.getElementById('g_'+eid);
        const n = document.getElementById('n_'+eid);
        if(g) g.innerText = obj.going ?? 0;
        if(n) n.innerText = obj.not_going ?? 0;
      }
    }catch(err){ console.error('SSE parse', err); }
  };
  src.onerror = function(e){ /* reconnects automatically */ }
}
</script>


<script>
async function setParticipation(eventId, button, status) {
    try {
        // Disable button during request
        button.disabled = true;
        
        const form = new FormData();
        form.append('event_id', eventId);
        form.append('status', status);
        
        const res = await fetch('api_participate.php', {
            method: 'POST',
            body: form
        });
        
        if (!res.ok) throw new Error('Network response was not ok');
        
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        
        // Update counts
        document.getElementById('going_' + eventId).textContent = data.going;
        document.getElementById('not_going_' + eventId).textContent = data.not_going;
        
        // Update button states
        const buttons = document.querySelectorAll(`[data-part-event="${eventId}"]`);
        buttons.forEach(btn => {
            btn.classList.remove('active', 'btn-success', 'btn-danger');
            btn.classList.add('btn-outline-' + (btn.dataset.partType === 'going' ? 'success' : 'danger'));
        });
        
        // Highlight selected button
        button.classList.remove('btn-outline-' + (status === 'going' ? 'success' : 'danger'));
        button.classList.add('btn-' + (status === 'going' ? 'success' : 'danger'), 'active');
        
    } catch (err) {
        console.error('Participation error:', err);
        alert('Error updating participation: ' + err.message);
    } finally {
        button.disabled = false;
    }
}

// Use event delegation for dynamically loaded buttons
document.addEventListener('click', function(e) {
    const button = e.target.closest('[data-part-event]');
    if (button) {
        e.preventDefault();
        const eventId = button.dataset.partEvent;
        const status = button.dataset.partType;
        setParticipation(eventId, button, status);
    }
});
</script>


<script>
function toggleCollapse(id, btn){
  const el = document.getElementById(id);
  if(!el) return;
  const isHidden = el.style.display === 'none' || el.style.display === '';
  el.style.display = isHidden ? 'block' : 'none';
  if(btn) btn.textContent = isHidden ? 'Hide Details' : 'Show Details';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
