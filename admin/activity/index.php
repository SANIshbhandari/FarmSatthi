<?php
$pageTitle = 'User Activity - FarmSaathi';
$currentModule = 'activity';
require_once __DIR__ . '/../../includes/header.php';

// Only admin can access
requirePermission('admin');

$conn = getDBConnection();

// Get all users with their activity
$query = "SELECT id, username, email, role, created_at, last_login FROM users ORDER BY last_login DESC, created_at DESC";
$result = $conn->query($query);
?>

<div class="module-header">
    <h2>User Activity & Reports</h2>
</div>

<div class="stats-grid" style="margin-bottom: 2rem;">
    <?php
    // Get statistics
    $totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $adminUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
    $managerUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'manager'")->fetch_assoc()['count'];
    $recentUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
    ?>
    
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <h3>Total Users</h3>
            <p class="stat-number"><?php echo $totalUsers; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🔑</div>
        <div class="stat-content">
            <h3>Admin Users</h3>
            <p class="stat-number"><?php echo $adminUsers; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">👨‍💼</div>
        <div class="stat-content">
            <h3>Manager Users</h3>
            <p class="stat-number"><?php echo $managerUsers; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
            <h3>New (30 days)</h3>
            <p class="stat-number"><?php echo $recentUsers; ?></p>
        </div>
    </div>
</div>

<div class="table-responsive">
    <h3 style="margin-bottom: 1rem;">User Login Activity</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Account Created</th>
                <th>Last Login</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $row['role']; ?>">
                            <?php echo ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td><?php echo formatDate($row['created_at']); ?></td>
                    <td>
                        <?php if ($row['last_login']): ?>
                            <?php echo formatDate($row['last_login']); ?>
                        <?php else: ?>
                            <span style="color: #6c757d;">Never logged in</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if ($row['last_login']) {
                            $lastLogin = strtotime($row['last_login']);
                            $daysSince = floor((time() - $lastLogin) / 86400);
                            
                            if ($daysSince == 0) {
                                echo '<span class="badge badge-success">Active Today</span>';
                            } elseif ($daysSince <= 7) {
                                echo '<span class="badge badge-success">Active</span>';
                            } elseif ($daysSince <= 30) {
                                echo '<span class="badge badge-warning">Inactive ' . $daysSince . ' days</span>';
                            } else {
                                echo '<span class="badge badge-inactive">Inactive ' . $daysSince . ' days</span>';
                            }
                        } else {
                            echo '<span class="badge badge-warning">Never Logged In</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h3 style="color: #2d7a3e; margin-bottom: 1rem;">Activity Summary</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <?php
        // Active users (logged in within 7 days)
        $activeUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
        
        // Inactive users (not logged in for 30+ days)
        $inactiveUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login < DATE_SUB(NOW(), INTERVAL 30 DAY) OR last_login IS NULL")->fetch_assoc()['count'];
        
        // Users who never logged in
        $neverLoggedIn = $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login IS NULL")->fetch_assoc()['count'];
        ?>
        
        <div style="background: #d4edda; padding: 1rem; border-radius: 4px;">
            <strong style="color: #155724;">Active Users (7 days):</strong>
            <p style="font-size: 1.5rem; margin: 0.5rem 0 0 0; color: #155724;"><?php echo $activeUsers; ?></p>
        </div>
        
        <div style="background: #fff3cd; padding: 1rem; border-radius: 4px;">
            <strong style="color: #856404;">Inactive Users (30+ days):</strong>
            <p style="font-size: 1.5rem; margin: 0.5rem 0 0 0; color: #856404;"><?php echo $inactiveUsers; ?></p>
        </div>
        
        <div style="background: #f8d7da; padding: 1rem; border-radius: 4px;">
            <strong style="color: #721c24;">Never Logged In:</strong>
            <p style="font-size: 1.5rem; margin: 0.5rem 0 0 0; color: #721c24;"><?php echo $neverLoggedIn; ?></p>
        </div>
    </div>
</div>

<?php
// Check if activity_log table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'activity_log'");
if ($tableCheck && $tableCheck->num_rows > 0):
    // Get recent manager activities
    $activityQuery = "SELECT a.*, u.role 
                      FROM activity_log a 
                      JOIN users u ON a.user_id = u.id 
                      WHERE u.role = 'manager' 
                      ORDER BY a.created_at DESC 
                      LIMIT 50";
    $activityResult = $conn->query($activityQuery);
?>

<div style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h3 style="color: #2d7a3e; margin-bottom: 1rem;">Recent Manager Activities</h3>
    
    <?php if ($activityResult && $activityResult->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Manager</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = $activityResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($activity['username']); ?></strong></td>
                    <td>
                        <?php
                        $actionColors = [
                            'create' => 'success',
                            'update' => 'info',
                            'delete' => 'danger',
                            'view' => 'secondary'
                        ];
                        $badgeClass = $actionColors[$activity['action']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?php echo $badgeClass; ?>">
                            <?php echo ucfirst($activity['action']); ?>
                        </span>
                    </td>
                    <td><?php echo ucfirst($activity['module']); ?></td>
                    <td><?php echo htmlspecialchars($activity['description']); ?></td>
                    <td><small><?php echo htmlspecialchars($activity['ip_address']); ?></small></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p style="color: #6c757d; text-align: center; padding: 2rem;">
        No manager activities recorded yet. Activities will appear here once managers start using the system.
    </p>
    <p style="text-align: center;">
        <small style="color: #6c757d;">
            Note: Run <code>database/add_activity_log.sql</code> to enable activity tracking.
        </small>
    </p>
    <?php endif; ?>
</div>

<?php else: ?>
<div style="margin-top: 2rem; background: #fff3cd; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ffc107;">
    <h4 style="color: #856404; margin-top: 0;">⚠️ Activity Tracking Not Enabled</h4>
    <p style="color: #856404;">
        To track manager activities, please run the following SQL file in phpMyAdmin:
    </p>
    <p style="background: white; padding: 1rem; border-radius: 4px; font-family: monospace;">
        database/add_activity_log.sql
    </p>
    <p style="color: #856404;">
        This will create the activity_log table and enable tracking of all manager actions.
    </p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
