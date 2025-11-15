<?php
$pageTitle = 'Crop Reports - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/lib/report_generator.php';
require_once __DIR__ . '/lib/report_filter.php';
require_once __DIR__ . '/lib/report_data.php';

$conn = getDBConnection();
$reportGen = new ReportGenerator($conn);

// Get report type and filters
$reportType = sanitizeInput($_GET['report'] ?? 'production');
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
    <h2>📊 Crop Reports</h2>
    <div class="header-actions">
        <a href="index.php" class="btn btn-outline">← Back to Reports</a>
    </div>
</div>

<!-- Report Type Navigation -->
<div class="report-nav">
    <a href="?report=production" class="btn <?php echo $reportType === 'production' ? 'btn-primary' : 'btn-outline'; ?>">
        Production Report
    </a>
    <a href="?report=growth" class="btn <?php echo $reportType === 'growth' ? 'btn-primary' : 'btn-outline'; ?>">
        Growth Monitoring
    </a>
    <a href="?report=sales" class="btn <?php echo $reportType === 'sales' ? 'btn-primary' : 'btn-outline'; ?>">
        Sales Report
    </a>
</div>

<!-- Filter Panel -->
<div class="filters-section">
    <form method="GET" action="crop_reports.php" class="filters-form">
        <input type="hidden" name="report" value="<?php echo htmlspecialchars($reportType); ?>">
        
        <div class="filter-group">
            <label>From Date:</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter->dateFrom); ?>" class="form-control">
        </div>
        
        <div class="filter-group">
            <label>To Date:</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter->dateTo); ?>" class="form-control">
        </div>
        
        <?php if ($reportType === 'production' || $reportType === 'growth'): ?>
        <div class="filter-group">
            <label>Crop Type:</label>
            <select name="crop_type" class="form-control">
                <option value="">All Types</option>
                <?php
                $types = $conn->query("SELECT DISTINCT crop_type FROM crops ORDER BY crop_type");
                while ($type = $types->fetch_assoc()):
                ?>
                <option value="<?php echo htmlspecialchars($type['crop_type']); ?>" 
                    <?php echo ($filter->customFilters['crop_type'] ?? '') === $type['crop_type'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($type['crop_type']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if ($reportType === 'growth'): ?>
        <div class="filter-group">
            <label>Growth Stage:</label>
            <select name="growth_stage" class="form-control">
                <option value="">All Stages</option>
                <option value="seedling" <?php echo ($filter->customFilters['growth_stage'] ?? '') === 'seedling' ? 'selected' : ''; ?>>Seedling</option>
                <option value="vegetative" <?php echo ($filter->customFilters['growth_stage'] ?? '') === 'vegetative' ? 'selected' : ''; ?>>Vegetative</option>
                <option value="flowering" <?php echo ($filter->customFilters['growth_stage'] ?? '') === 'flowering' ? 'selected' : ''; ?>>Flowering</option>
                <option value="fruiting" <?php echo ($filter->customFilters['growth_stage'] ?? '') === 'fruiting' ? 'selected' : ''; ?>>Fruiting</option>
                <option value="harvest" <?php echo ($filter->customFilters['growth_stage'] ?? '') === 'harvest' ? 'selected' : ''; ?>>Harvest</option>
            </select>
        </div>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="crop_reports.php?report=<?php echo htmlspecialchars($reportType); ?>" class="btn btn-outline">Clear</a>
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
    case 'production':
        include __DIR__ . '/crop_production_report.php';
        break;
    case 'growth':
        include __DIR__ . '/crop_growth_report.php';
        break;
    case 'sales':
        include __DIR__ . '/crop_sales_report.php';
        break;
    default:
        echo '<div class="no-results"><p>Invalid report type selected.</p></div>';
}
?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
