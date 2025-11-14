<?php
$pageTitle = 'Livestock - FarmSaathi';
$currentModule = 'livestock';
require_once __DIR__ . '/../includes/header.php';

$conn = getDBConnection();

// Get search and filter parameters
$search = sanitizeInput($_GET['search'] ?? '');
$animal_type = sanitizeInput($_GET['animal_type'] ?? '');
$health_status = sanitizeInput($_GET['health_status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$recordsPerPage = 20;

// Build query
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $whereConditions[] = "(animal_type LIKE ? OR breed LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if (!empty($animal_type)) {
    $whereConditions[] = "animal_type = ?";
    $params[] = $animal_type;
    $types .= 's';
}

if (!empty($health_status)) {
    $whereConditions[] = "health_status = ?";
    $params[] = $health_status;
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM livestock $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalRecords = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get pagination data
$pagination = getPagination($totalRecords, $page, $recordsPerPage);

// Get livestock
$query = "SELECT * FROM livestock $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $recordsPerPage;
$params[] = $pagination['offset'];
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="module-header">
    <h2>Livestock Management</h2>
    <?php if (canModify()): ?>
    <a href="add.php" class="btn btn-primary">+ Add New Livestock</a>
    <?php endif; ?>
</div>

<div class="filters-section">
    <form method="GET" action="index.php" class="filters-form">
        <div class="filter-group">
            <input 
                type="text" 
                name="search" 
                placeholder="Search livestock..." 
                value="<?php echo htmlspecialchars($search); ?>"
                class="form-control"
            >
        </div>
        <div class="filter-group">
            <select name="health_status" class="form-control">
                <option value="">All Health Status</option>
                <option value="healthy" <?php echo $health_status === 'healthy' ? 'selected' : ''; ?>>Healthy</option>
                <option value="sick" <?php echo $health_status === 'sick' ? 'selected' : ''; ?>>Sick</option>
                <option value="under_treatment" <?php echo $health_status === 'under_treatment' ? 'selected' : ''; ?>>Under Treatment</option>
                <option value="quarantine" <?php echo $health_status === 'quarantine' ? 'selected' : ''; ?>>Quarantine</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="index.php" class="btn btn-outline">Clear</a>
    </form>
</div>

<?php if ($result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Animal Type</th>
                <th>Breed</th>
                <th>Count</th>
                <th>Age (months)</th>
                <th>Health Status</th>
                <th>Purchase Date</th>
                <th>Current Value</th>
                <?php if (canModify()): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['animal_type']); ?></td>
                <td><?php echo htmlspecialchars($row['breed']); ?></td>
                <td><?php echo number_format($row['count']); ?></td>
                <td><?php echo number_format($row['age_months']); ?></td>
                <td><span class="badge badge-<?php echo $row['health_status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['health_status'])); ?></span></td>
                <td><?php echo formatDate($row['purchase_date']); ?></td>
                <td><?php echo formatCurrency($row['current_value']); ?></td>
                <?php if (canModify()): ?>
                <td class="actions">
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this livestock record?');">Delete</a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
<div class="pagination">
    <?php if ($pagination['has_previous']): ?>
    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&health_status=<?php echo urlencode($health_status); ?>" class="btn btn-sm">Previous</a>
    <?php endif; ?>
    
    <span class="pagination-info">
        Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
        (<?php echo $totalRecords; ?> total records)
    </span>
    
    <?php if ($pagination['has_next']): ?>
    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&health_status=<?php echo urlencode($health_status); ?>" class="btn btn-sm">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="no-results">
    <p>No livestock found.</p>
    <?php if (canModify()): ?>
    <a href="add.php" class="btn btn-primary">Add Your First Livestock</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
$stmt->close();
require_once __DIR__ . '/../includes/footer.php';
?>
