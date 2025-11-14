<?php
$pageTitle = 'Dashboard - FarmSaathi';
$currentModule = 'dashboard';
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();
$userRole = getCurrentUserRole();

// Get statistics based on role
$stats = [];
$alerts = [];

if ($userRole === 'admin') {
    // Admin Dashboard - User Management Statistics
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Admin users
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stats['admin_users'] = $result->fetch_assoc()['count'];
    
    // Manager users
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'manager'");
    $stats['manager_users'] = $result->fetch_assoc()['count'];
    
    // Recent users (last 30 days)
    $result = $conn->query("
        SELECT COUNT(*) as count FROM users 
        WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
    ");
    $stats['recent_users'] = $result->fetch_assoc()['count'];
    
    // Get recent user activity
    $result = $conn->query("
        SELECT username, email, role, created_at, last_login 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentUsers = [];
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
    
} else {
    // Manager Dashboard - Farm Operations Statistics
    
    // Total active crops
    $result = $conn->query("SELECT COUNT(*) as count FROM crops WHERE status = 'active'");
    $stats['total_crops'] = $result->fetch_assoc()['count'];
    
    // Total livestock
    $result = $conn->query("SELECT COALESCE(SUM(count), 0) as count FROM livestock");
    $stats['total_livestock'] = $result->fetch_assoc()['count'];
    
    // Active employees
    $result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $stats['active_employees'] = $result->fetch_assoc()['count'];
    
    // Current month expenses
    $result = $conn->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE MONTH(expense_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(expense_date) = YEAR(CURRENT_DATE())
    ");
    $stats['monthly_expenses'] = $result->fetch_assoc()['total'];
    
    // Low inventory alerts
    $result = $conn->query("SELECT * FROM inventory WHERE quantity <= reorder_level ORDER BY item_name");
    while ($row = $result->fetch_assoc()) {
        $alerts[] = [
            'type' => 'warning',
            'icon' => '📦',
            'message' => "Low stock: " . $row['item_name'] . " (" . $row['quantity'] . " " . $row['unit'] . " remaining)",
            'link' => url('inventory/index.php')
        ];
    }
    
    // Upcoming equipment maintenance
    $result = $conn->query("
        SELECT * FROM equipment 
        WHERE next_maintenance IS NOT NULL 
        AND next_maintenance <= DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
        AND next_maintenance >= CURRENT_DATE()
        ORDER BY next_maintenance
    ");
    while ($row = $result->fetch_assoc()) {
        $daysUntil = floor((strtotime($row['next_maintenance']) - time()) / 86400);
        $alerts[] = [
            'type' => 'info',
            'icon' => '🔧',
            'message' => "Maintenance due: " . $row['equipment_name'] . " in " . $daysUntil . " day(s)",
            'link' => url('equipment/index.php')
        ];
    }
    
    // Approaching harvest dates
    $result = $conn->query("
        SELECT * FROM crops 
        WHERE expected_harvest <= DATE_ADD(CURRENT_DATE(), INTERVAL 14 DAY)
        AND expected_harvest >= CURRENT_DATE()
        AND status = 'active'
        ORDER BY expected_harvest
    ");
    while ($row = $result->fetch_assoc()) {
        $daysUntil = floor((strtotime($row['expected_harvest']) - time()) / 86400);
        $alerts[] = [
            'type' => 'success',
            'icon' => '🌾',
            'message' => "Harvest approaching: " . $row['crop_name'] . " in " . $daysUntil . " day(s)",
            'link' => url('crops/index.php')
        ];
    }
}
?>

<div class="dashboard">
    <h2>Dashboard</h2>
    <p class="dashboard-subtitle">Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?> (<?php echo ucfirst($userRole); ?>)</p>
    
    <?php if ($userRole === 'admin'): ?>
    <!-- Admin Dashboard -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Users</h3>
                <p class="stat-number"><?php echo number_format($stats['total_users']); ?></p>
                <a href="<?php echo url('admin/users/index.php'); ?>" class="stat-link">Manage Users →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔑</div>
            <div class="stat-content">
                <h3>Admin Users</h3>
                <p class="stat-number"><?php echo number_format($stats['admin_users']); ?></p>
                <a href="<?php echo url('admin/users/index.php'); ?>" class="stat-link">View Admins →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👨‍💼</div>
            <div class="stat-content">
                <h3>Manager Users</h3>
                <p class="stat-number"><?php echo number_format($stats['manager_users']); ?></p>
                <a href="<?php echo url('admin/users/index.php'); ?>" class="stat-link">View Managers →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <h3>New Users (30 days)</h3>
                <p class="stat-number"><?php echo number_format($stats['recent_users']); ?></p>
                <a href="<?php echo url('admin/activity/index.php'); ?>" class="stat-link">View Activity →</a>
            </div>
        </div>
    </div>

    <div class="recent-activity-section">
        <h3>Recent User Registrations</h3>
        <?php if (!empty($recentUsers)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                        <td><?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">No recent user registrations.</p>
        <?php endif; ?>
    </div>

    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            <a href="<?php echo url('admin/users/add.php'); ?>" class="btn btn-primary">+ Create User Account</a>
            <a href="<?php echo url('admin/users/index.php'); ?>" class="btn btn-secondary">👥 Manage Users</a>
            <a href="<?php echo url('admin/activity/index.php'); ?>" class="btn btn-secondary">📊 View User Activity</a>
        </div>
    </div>

    <?php else: ?>
    <!-- Manager Dashboard -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🌱</div>
            <div class="stat-content">
                <h3>Total Crops</h3>
                <p class="stat-number"><?php echo number_format($stats['total_crops']); ?></p>
                <a href="<?php echo url('crops/index.php'); ?>" class="stat-link">View Crops →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🐄</div>
            <div class="stat-content">
                <h3>Total Livestock</h3>
                <p class="stat-number"><?php echo number_format($stats['total_livestock']); ?></p>
                <a href="<?php echo url('livestock/index.php'); ?>" class="stat-link">View Livestock →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Active Employees</h3>
                <p class="stat-number"><?php echo number_format($stats['active_employees']); ?></p>
                <a href="<?php echo url('employees/index.php'); ?>" class="stat-link">View Employees →</a>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>Monthly Expenses</h3>
                <p class="stat-number"><?php echo formatCurrency($stats['monthly_expenses']); ?></p>
                <a href="<?php echo url('expenses/index.php'); ?>" class="stat-link">View Expenses →</a>
            </div>
        </div>
    </div>

    <?php if (!empty($alerts)): ?>
    <div class="alerts-section">
        <h3>Alerts & Notifications</h3>
        <div class="alerts-list">
            <?php foreach ($alerts as $alert): ?>
            <div class="alert-item alert-<?php echo $alert['type']; ?>">
                <span class="alert-icon"><?php echo $alert['icon']; ?></span>
                <span class="alert-message"><?php echo htmlspecialchars($alert['message']); ?></span>
                <a href="<?php echo $alert['link']; ?>" class="alert-action">View →</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="alerts-section">
        <h3>Alerts & Notifications</h3>
        <p class="text-muted">No alerts at this time. Everything looks good! ✓</p>
    </div>
    <?php endif; ?>

    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            <a href="<?php echo url('crops/add.php'); ?>" class="btn btn-success">+ Add Crop</a>
            <a href="<?php echo url('livestock/add.php'); ?>" class="btn btn-success">+ Add Livestock</a>
            <a href="<?php echo url('expenses/add.php'); ?>" class="btn btn-success">+ Add Expense</a>
            <a href="<?php echo url('inventory/add.php'); ?>" class="btn btn-success">+ Add Inventory</a>
            <a href="<?php echo url('reports/index.php'); ?>" class="btn btn-primary">📊 View Reports</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
