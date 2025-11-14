<?php
$pageTitle = 'Reports - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

$reportType = sanitizeInput($_GET['type'] ?? '');
$date_from = sanitizeInput($_GET['date_from'] ?? '');
$date_to = sanitizeInput($_GET['date_to'] ?? '');
?>

<div class="module-header">
    <h2>Reports & Analytics</h2>
</div>

<div class="report-selector">
    <h3>Select Report Type</h3>
    <div class="report-buttons">
        <a href="?type=crops" class="btn <?php echo $reportType === 'crops' ? 'btn-primary' : 'btn-outline'; ?>">📊 Crops Report</a>
        <a href="?type=livestock" class="btn <?php echo $reportType === 'livestock' ? 'btn-primary' : 'btn-outline'; ?>">🐄 Livestock Report</a>
        <a href="?type=equipment" class="btn <?php echo $reportType === 'equipment' ? 'btn-primary' : 'btn-outline'; ?>">🔧 Equipment Report</a>
        <a href="?type=employees" class="btn <?php echo $reportType === 'employees' ? 'btn-primary' : 'btn-outline'; ?>">👥 Employee Report</a>
        <a href="?type=expenses" class="btn <?php echo $reportType === 'expenses' ? 'btn-primary' : 'btn-outline'; ?>">💰 Expense Report</a>
        <a href="?type=inventory" class="btn <?php echo $reportType === 'inventory' ? 'btn-primary' : 'btn-outline'; ?>">📦 Inventory Report</a>
    </div>
</div>

<?php if ($reportType === 'crops'): ?>
    <?php
    $result = $conn->query("SELECT 
        crop_type,
        COUNT(*) as count,
        SUM(area_hectares) as total_area,
        status
    FROM crops 
    GROUP BY crop_type, status
    ORDER BY crop_type, status");
    
    $totalArea = $conn->query("SELECT SUM(area_hectares) as total FROM crops WHERE status = 'active'")->fetch_assoc()['total'];
    $totalCrops = $conn->query("SELECT COUNT(*) as total FROM crops WHERE status = 'active'")->fetch_assoc()['total'];
    ?>
    
    <div class="report-content">
        <h3>Crops Report</h3>
        
        <div class="report-summary">
            <div class="summary-card">
                <h4>Total Active Crops</h4>
                <p class="summary-number"><?php echo number_format($totalCrops); ?></p>
            </div>
            <div class="summary-card">
                <h4>Total Area Planted</h4>
                <p class="summary-number"><?php echo number_format($totalArea, 2); ?> ha</p>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Crop Type</th>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Total Area (ha)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['crop_type']); ?></td>
                    <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td><?php echo number_format($row['count']); ?></td>
                    <td><?php echo number_format($row['total_area'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($reportType === 'livestock'): ?>
    <?php
    $result = $conn->query("SELECT 
        animal_type,
        health_status,
        SUM(count) as total_count,
        SUM(current_value) as total_value
    FROM livestock 
    GROUP BY animal_type, health_status
    ORDER BY animal_type, health_status");
    
    $totalAnimals = $conn->query("SELECT SUM(count) as total FROM livestock")->fetch_assoc()['total'];
    $totalValue = $conn->query("SELECT SUM(current_value) as total FROM livestock")->fetch_assoc()['total'];
    ?>
    
    <div class="report-content">
        <h3>Livestock Report</h3>
        
        <div class="report-summary">
            <div class="summary-card">
                <h4>Total Animals</h4>
                <p class="summary-number"><?php echo number_format($totalAnimals); ?></p>
            </div>
            <div class="summary-card">
                <h4>Total Value</h4>
                <p class="summary-number"><?php echo formatCurrency($totalValue); ?></p>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Animal Type</th>
                    <th>Health Status</th>
                    <th>Count</th>
                    <th>Total Value</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['animal_type']); ?></td>
                    <td><span class="badge badge-<?php echo $row['health_status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['health_status'])); ?></span></td>
                    <td><?php echo number_format($row['total_count']); ?></td>
                    <td><?php echo formatCurrency($row['total_value']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($reportType === 'equipment'): ?>
    <?php
    $result = $conn->query("SELECT 
        type,
        `condition`,
        COUNT(*) as count,
        SUM(value) as total_value
    FROM equipment 
    GROUP BY type, `condition`
    ORDER BY type, `condition`");
    
    $totalEquipment = $conn->query("SELECT COUNT(*) as total FROM equipment")->fetch_assoc()['total'];
    $totalValue = $conn->query("SELECT SUM(value) as total FROM equipment")->fetch_assoc()['total'];
    
    $maintenanceDue = $conn->query("SELECT * FROM equipment WHERE next_maintenance <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY) ORDER BY next_maintenance");
    ?>
    
    <div class="report-content">
        <h3>Equipment Report</h3>
        
        <div class="report-summary">
            <div class="summary-card">
                <h4>Total Equipment</h4>
                <p class="summary-number"><?php echo number_format($totalEquipment); ?></p>
            </div>
            <div class="summary-card">
                <h4>Total Value</h4>
                <p class="summary-number"><?php echo formatCurrency($totalValue); ?></p>
            </div>
        </div>
        
        <h4>Equipment by Type & Condition</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Condition</th>
                    <th>Count</th>
                    <th>Total Value</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                    <td><span class="badge badge-<?php echo $row['condition']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['condition'])); ?></span></td>
                    <td><?php echo number_format($row['count']); ?></td>
                    <td><?php echo formatCurrency($row['total_value']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h4>Upcoming Maintenance (Next 30 Days)</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Equipment Name</th>
                    <th>Type</th>
                    <th>Next Maintenance</th>
                    <th>Condition</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($maintenanceDue->num_rows > 0): ?>
                    <?php while ($row = $maintenanceDue->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo formatDate($row['next_maintenance']); ?></td>
                        <td><span class="badge badge-<?php echo $row['condition']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['condition'])); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No maintenance due in the next 30 days</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($reportType === 'employees'): ?>
    <?php
    $result = $conn->query("SELECT 
        role,
        status,
        COUNT(*) as count,
        SUM(salary) as total_salary
    FROM employees 
    GROUP BY role, status
    ORDER BY role, status");
    
    $totalEmployees = $conn->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'")->fetch_assoc()['total'];
    $totalSalary = $conn->query("SELECT SUM(salary) as total FROM employees WHERE status = 'active'")->fetch_assoc()['total'];
    ?>
    
    <div class="report-content">
        <h3>Employee Report</h3>
        
        <div class="report-summary">
            <div class="summary-card">
                <h4>Active Employees</h4>
                <p class="summary-number"><?php echo number_format($totalEmployees); ?></p>
            </div>
            <div class="summary-card">
                <h4>Total Monthly Salary</h4>
                <p class="summary-number"><?php echo formatCurrency($totalSalary); ?></p>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Total Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td><?php echo number_format($row['count']); ?></td>
                    <td><?php echo formatCurrency($row['total_salary']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($reportType === 'expenses'): ?>
    <div class="report-content">
        <h3>Expense Report</h3>
        
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="type" value="expenses">
            <div class="filter-group">
                <label>From Date:</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control">
            </div>
            <div class="filter-group">
                <label>To Date:</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </form>
        
        <?php
        $whereClause = '';
        $params = [];
        $types = '';
        
        if (!empty($date_from)) {
            $whereClause .= " AND expense_date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        if (!empty($date_to)) {
            $whereClause .= " AND expense_date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        $query = "SELECT 
            category,
            COUNT(*) as count,
            SUM(amount) as total_amount
        FROM expenses 
        WHERE 1=1 $whereClause
        GROUP BY category
        ORDER BY total_amount DESC";
        
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $totalQuery = "SELECT SUM(amount) as total FROM expenses WHERE 1=1 $whereClause";
        $stmt2 = $conn->prepare($totalQuery);
        if (!empty($params)) {
            $stmt2->bind_param($types, ...$params);
        }
        $stmt2->execute();
        $totalExpenses = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;
        ?>
        
        <div class="report-summary">
            <div class="summary-card">
                <h4>Total Expenses</h4>
                <p class="summary-number"><?php echo formatCurrency($totalExpenses); ?></p>
                <?php if (!empty($date_from) || !empty($date_to)): ?>
                <small>
                    <?php echo !empty($date_from) ? formatDate($date_from) : 'Beginning'; ?> - 
                    <?php echo !empty($date_to) ? formatDate($date_to) : 'Today'; ?>
                </small>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Number of Expenses</th>
                    <th>Total Amount</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $percentage = $totalExpenses > 0 ? ($row['total_amount'] / $totalExpenses) * 100 : 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo number_format($row['count']); ?></td>
                    <td><?php echo formatCurrency($row['total_amount']); ?></td>
                    <td><?php echo number_format($percentage, 1); ?>%</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($reportType === 'inventory'): ?>
    <?php
    $result = $conn->query("SELECT 
        category,
        COUNT(*) as item_count,
        SUM(quantity) as total_quantity
    FROM inventory 
    GROUP BY category
    ORDER BY category");
    
    $lowStock = $conn->query("SELECT * FROM inventory WHERE quantity <= reorder_level ORDER BY item_name");
    $totalItems = $conn->query("SELECT COUNT(*) as total FROM inventory")->fetch_assoc()['total'];
    $lowStockCount = $conn->query("SELECT COUNT(*) as total FROM inventory WHERE quantity <= reorder_level")->fetch_assoc()['total'];
    ?>
    
    <div class="report-content">
        <h3>Inventory Report</h3>
        
        <div class="report-summary">
            <div class="summary-card">
                <h4>Total Items</h4>
                <p class="summary-number"><?php echo number_format($totalItems); ?></p>
            </div>
            <div class="summary-card">
                <h4>Low Stock Items</h4>
                <p class="summary-number"><?php echo number_format($lowStockCount); ?></p>
            </div>
        </div>
        
        <h4>Inventory by Category</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Number of Items</th>
                    <th>Total Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo number_format($row['item_count']); ?></td>
                    <td><?php echo number_format($row['total_quantity'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h4>Items Below Reorder Level</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Current Quantity</th>
                    <th>Reorder Level</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lowStock->num_rows > 0): ?>
                    <?php while ($row = $lowStock->fetch_assoc()): ?>
                    <tr class="low-stock-row">
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo number_format($row['quantity'], 2); ?></td>
                        <td><?php echo number_format($row['reorder_level'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">All items are adequately stocked</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <div class="report-content">
        <div class="no-results">
            <p>Please select a report type above to view analytics and data.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
