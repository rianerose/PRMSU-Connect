<?php
require 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background:#F4F6FF;">
    <nav class="navbar navbar-expand bg-light mb-4" style="background:#10375C;color:#F4F6FF;">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <!-- Add Event Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Event</h5>
                        <form action="admin_add_event.php" method="POST">
                            <div class="mb-2">
                                <input name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="mb-2">
                                <textarea name="description" class="form-control" placeholder="Description" rows="3"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Start Date & Time</label>
                                <input type="datetime-local" name="datetime" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">End Date & Time</label>
                                <input type="datetime-local" name="end_datetime" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Location</label>
                                <input name="location" class="form-control" placeholder="Location">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Publish Date & Time</label>
                                <input type="datetime-local" name="publish_at" class="form-control">
                                <div class="form-text">Leave empty to publish immediately</div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">For</label>
                                <select name="college" class="form-select">
                                    <option value="ALL">All Colleges</option>
                                    <option value="CCIT">CCIT</option>
                                    <option value="CIT">CIT</option>
                                    <option value="COE">COE</option>
                                    <option value="CAS">CAS</option>
                                    <option value="CBAPA">CBAPA</option>
                                    <option value="CON">CON</option>
                                    <option value="CTHM">CTHM</option>
                                    <option value="CTE">CTE</option>
                                    <option value="CCJ">CCJ</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <select name="participation_scope" class="form-select">
                                    <option value="ALL">All students may participate</option>
                                    <option value="COLLEGE">Only selected college may participate</option>
                                </select>
                            </div>
                            <button class="btn" style="background:#F3C623;color:#10375C">Add Event</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <!-- Add Announcement Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Announcement</h5>
                        <form action="admin_add_announcement.php" method="POST">
                            <div class="mb-2">
                                <input name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="mb-2">
                                <textarea name="description" class="form-control" placeholder="Description" rows="3"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Date:</label>
                                <input type="datetime-local" name="datetime" class="form-control" required>
                            </div>
                            <div class="mb-2">
                              <label class="form-label">For:</label>
                                <select name="college" class="form-select">
                                    <option value="ALL">All Colleges</option>
                                    <option value="CCIT">CCIT</option>
                                    <option value="CIT">CIT</option>
                                    <option value="COE">COE</option>
                                    <option value="CAS">CAS</option>
                                    <option value="CBAPA">CBAPA</option>
                                    <option value="CON">CON</option>
                                    <option value="CTHM">CTHM</option>
                                    <option value="CTE">CTE</option>
                                    <option value="CCJ">CCJ</option>
                                </select>
                            </div>
                            <button class="btn" style="background:#F3C623;color:#10375C">Add Announcement</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div id="announcements" class="mb-4"></div>
                <div id="events"></div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit_id">
                        <input type="hidden" id="edit_type">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" id="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="edit_description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DateTime</label>
                            <input type="datetime-local" id="edit_datetime" class="form-control" required>
                        </div>
                        <div id="event_specific_fields">
                            <div class="mb-3">
                                <label class="form-label">End DateTime</label>
                                <input type="datetime-local" id="edit_end_datetime" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" id="edit_location" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Publish At</label>
                                <input type="datetime-local" id="edit_publish_at" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Participation Scope</label>
                                <select id="edit_participation_scope" class="form-select">
                                    <option value="ALL">All students may participate</option>
                                    <option value="COLLEGE">Only selected college may participate</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">College</label>
                            <select id="edit_college" class="form-select">
                                <option value="ALL">All Colleges</option>
                                <option value="CCIT">CCIT</option>
                                <option value="CIT">CIT</option>
                                <option value="COE">COE</option>
                                <option value="CAS">CAS</option>
                                <option value="CBAPA">CBAPA</option>
                                <option value="CON">CON</option>
                                <option value="CTHM">CTHM</option>
                                <option value="CTE">CTE</option>
                                <option value="CCJ">CCJ</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveChanges()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    async function fetchLists() {
        try {
            const annResp = await fetch('api_get_announcements.php');
            if(annResp.ok) {
                document.getElementById('announcements').innerHTML = await annResp.text();
            }
            
            const evResp = await fetch('api_get_events.php');
            if(evResp.ok) {
                document.getElementById('events').innerHTML = await evResp.text();
            }
        } catch (err) {
            console.error('fetchLists error', err);
        }
    }

    async function editItem(type, id) {
        try {
            const res = await fetch(`api_get_item.php?type=${type}&id=${id}`);
            const item = await res.json();
            if(item.error) throw new Error(item.error);
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_title').value = item.title;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_datetime').value = item.datetime.slice(0, 16);
            document.getElementById('edit_college').value = item.college;
            
            if(type === 'event') {
                document.getElementById('event_specific_fields').style.display = 'block';
                document.getElementById('edit_end_datetime').value = item.end_datetime ? item.end_datetime.slice(0, 16) : '';
                document.getElementById('edit_location').value = item.location || '';
                document.getElementById('edit_publish_at').value = item.publish_at ? item.publish_at.slice(0, 16) : '';
                document.getElementById('edit_participation_scope').value = item.participation_scope || 'ALL';
            } else {
                document.getElementById('event_specific_fields').style.display = 'none';
            }
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        } catch(err) {
            console.error('Edit error:', err);
            alert('Error loading item details');
        }
    }

    async function saveChanges() {
        try {
            const form = new FormData();
            form.append('action', 'update');
            form.append('type', document.getElementById('edit_type').value);
            form.append('id', document.getElementById('edit_id').value);
            form.append('title', document.getElementById('edit_title').value);
            form.append('description', document.getElementById('edit_description').value);
            form.append('datetime', document.getElementById('edit_datetime').value);
            form.append('college', document.getElementById('edit_college').value);

            if(document.getElementById('edit_type').value === 'event') {
                form.append('end_datetime', document.getElementById('edit_end_datetime').value);
                form.append('location', document.getElementById('edit_location').value);
                form.append('publish_at', document.getElementById('edit_publish_at').value);
                form.append('participation_scope', document.getElementById('edit_participation_scope').value);
            }

            const res = await fetch('api_admin_crud.php', {
                method: 'POST',
                body: form
            });

            // log status and raw text for debugging
            const text = await res.text();
            console.log('api_admin_crud response status', res.status, 'text:', text);

            // try parse JSON only if response is JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned non-JSON response. See console/network for details.');
            }

            if(data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                await fetchLists();
            } else {
                throw new Error(data.error || 'Error saving changes');
            }
        } catch(err) {
            console.error('Save error:', err);
            alert('Network error: ' + (err.message || 'See console for details'));
        }
    }

    async function deleteItem(type, id) {
        if(!confirm('Are you sure you want to delete this ' + type + '?')) return;
        
        try {
            const form = new FormData();
            form.append('action', 'delete');
            form.append('type', type);
            form.append('id', id);
            
            const res = await fetch('api_admin_crud.php', {
                method: 'POST',
                body: form
            });
            
            const data = await res.json();
            if(data.success) {
                await fetchLists();
            } else {
                alert(data.error || 'Error deleting item');
            }
        } catch(err) {
            console.error('Delete error:', err);
            alert('Network error');
        }
    }

    // Initial load and refresh
    fetchLists();
    setInterval(fetchLists, 5000);
    </script>
</body>
</html>
