<?php
require_once '../config/functions.php';
requireRole(['super_admin']);

$logs = $conn->query("SELECT al.*, u.first_name, u.last_name, u.email 
                      FROM activity_logs al 
                      LEFT JOIN users u ON al.user_id = u.id 
                      ORDER BY al.created_at DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= url('uploads/settings/69838c04de6ad_281903bf-3ae0-48b7-a44e-acbb35e98438.jpg') ?>" type="image/jpg">
    <title>Activity Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h3 class="mb-4">Activity Logs</h3>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs && $logs->num_rows > 0): ?>
                            <?php while($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?= formatDateTime($log['created_at']) ?></td>
                                <td>
                                    <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($log['email']) ?></small>
                                </td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['description'] ?? '-') ?></td>
                                <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No activity logs yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
