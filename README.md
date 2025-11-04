# Announcement & Event Attendance Dashboard (PHP/Bootstrap)

Features:
- Student signup/login, Admin accounts must be created directly in the database.
- Admin can add announcements (targeted to a college or All) and events.
- Students can mark participation (going / not going) and see real-time counts (simple polling every 5 seconds).
- Color palette used: #10375C (dark blue), #F3C623 (accent yellow), #F4F6FF (background).
- Uses Bootstrap 5 from CDN, minimal JS + PHP + MySQL (mysqli).

Setup:
1. Create a MySQL database server reachable by config.php credentials.
2. Run the `init.sql` to create tables. The init.sql contains placeholders for hashed passwords. See below.
3. To create admin user manually: run in PHP/CLI:
   <?php
   // generate hashed password for init.sql
   echo password_hash('admin123', PASSWORD_DEFAULT) . "\n";
   echo password_hash('student123', PASSWORD_DEFAULT) . "\n";
   ?>
   Put those hashes into the init.sql replacing placeholders, then run init.sql.
4. Place files into your PHP server's document root.
5. Visit index.php to signup or login.

Notes:
- Admin accounts must be added in DB with role='admin'.
- This is a simple starter project and not hardened for production.


## Recommended DB Indexes
Add these indexes to speed queries:

```sql
ALTER TABLE Announcement ADD INDEX idx_announcement_datetime (datetime);
ALTER TABLE Announcement ADD INDEX idx_announcement_college (college);
ALTER TABLE `Event` ADD INDEX idx_event_publish_at (publish_at);
ALTER TABLE `Event` ADD INDEX idx_event_college (college);
```
