<?php
$pageTitle = 'Livestock Reports - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/lib/report_generator.php';
require_once __DIR__ . '/lib/report_filter.php';
require_once __DIR__ . '/lib/report_data.php';

$conn = getDBConnection();
$reportGen = new ReportGenerator($conn);

// Get report type and filters
$reportType = sanitizeInput($_GET['report'] ?? 'health');
$filterData = $_GET;
$filter = new ReportFilter($filterData);

// Validate filters
if (!$filter->isValid()) {
    foreach ($filter->getErrors() as $error) {
        setFlashMessage($error, 'error');
    }
}
?>

<div class="module-header">
    <h2>🐄 Livestock Reports</h2>
    <div class="header-actions">
        <a href="index.php" class="btn btn-outline">← Back to Reports</a>
    </div>
</div>

<!-- Report Type Navigation -->
<div class="report-nav">
    <a href="?report=health" class="btn <?php echo $reportType === 'health' ? 'btn-primary' : 'btn-outline'; ?>">
        Health Report
    </a>
    <a href="?report=production" class="btn <?php echo $reportType === 'production' ? 'btn-primary' : 'btn-outline'; ?>">
        Production Report
    </a>
    <a href="?report=sales" class="btn <?php echo $reportType === 'sales' ? 'btn-primary' : 'btn-outline'; ?>">
        Sales Report
    </a>
</div>

<!-- Filter Panel -->
<div class="filters-section">
    <form method="GET" action="livestock_reports.php" class="filters-form">
        <input type="hidden" name="report" value="<?php echo htmlspecialchars($reportType); ?>">
        
        <div class="filter-group">
            <label>From Date:</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter->dateFrom); ?>" class="form-control">
        </div>
        
        <div class="filter-group">
            <label>To Date:</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter->dateTo); ?>" class="form-control">
        </div>
        
        <div class="filter-group">
            <label>Animal Type:</label>
            <select name="animal_type" class="form-control">
                <option value="">All Types</option>
                <?php
                $types = $conn->query("SELECT DISTINCT animal_type FROM livestock ORDER BY animal_type");
                while ($type = $types->fetch_assoc()):
                ?>
                <option value="<?php echo htmlspecialchars($type['animal_type']); ?>" 
                    <?php echo ($filter->customFilters['animal_type'] ?? '') === $type['animal_type'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($type['animal_type']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <?php if ($reportType === 'production'): ?>
        <div class="filter-group">
            <label>Aggregation:</label>
            <select name="aggregation" class="form-control">
                <option value="daily" <?php echo ($filter->customFilters['aggregation'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                <option value="monthly" <?php echo ($filter->customFilters['aggregation'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
            </select>
        </div>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="livestock_reports.php?report=<?php echo htmlspecialchars($reportType); ?>" class="btn btn-outline">Clear</a>
    </form>
</div>

<?php if ($filter->hasFilters()): ?>
<div class="filter-summary">
    <strong>Active Filters:</strong> <?php echo $filter->getSummary(); ?>
</div>
<?php endif; ?>

<div class="report-content">
<?php
// Generate report based on type
switch ($reportType) {
    case 'health':
        include __DIR__ . '/livestock_health_report.php';
        break;
    case 'production':
        include __DIR__ . '/livestock_production_report.php';
        break;
    case 'sales':
        include __DIR__ . '/livestock_sales_report.php';
        break;
    default:
        echo '<div class="no-results"><p>Invalid report type selected.</p></div>';
}
?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
