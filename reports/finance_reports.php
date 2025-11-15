<?php
$pageTitle = 'Finance Reports - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/lib/report_generator.php';
require_once __DIR__ . '/lib/report_filter.php';
require_once __DIR__ . '/lib/report_data.php';

$conn = getDBConnection();
$reportGen = new ReportGenerator($conn);

$reportType = sanitizeInput($_GET['report'] ?? 'income');
$filterData = $_GET;
$filter = new ReportFilter($filterData);

if (!$filter->isValid()) {
    foreach ($filter->getErrors() as $error) {
        setFlashMessage($error, 'error');
    }
}
?>

<div class="module-header">
    <h2>💰 Finance Reports</h2>
    <div class="header-actions">
        <a href="index.php" class="btn btn-outline">← Back to Reports</a>
    </div>
</div>

<div class="report-nav">
    <a href="?report=income" class="btn <?php echo $reportType === 'income' ? 'btn-primary' : 'btn-outline'; ?>">Income Report</a>
    <a href="?report=expense" class="btn <?php echo $reportType === 'expense' ? 'btn-primary' : 'btn-outline'; ?>">Expense Report</a>
    <a href="?report=profit_loss" class="btn <?php echo $reportType === 'profit_loss' ? 'btn-primary' : 'btn-outline'; ?>">Profit & Loss</a>
</div>

<div class="filters-section">
    <form method="GET" action="finance_reports.php" class="filters-form">
        <input type="hidden" name="report" value="<?php echo htmlspecialchars($reportType); ?>">
        <div class="filter-group">
            <label>From Date:</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter->dateFrom); ?>" class="form-control">
        </div>
        <div class="filter-group">
            <label>To Date:</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter->dateTo); ?>" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="finance_reports.php?report=<?php echo htmlspecialchars($reportType); ?>" class="btn btn-outline">Clear</a>
    </form>
</div>

<?php if ($filter->hasFilters()): ?>
<div class="filter-summary">
    <strong>Active Filters:</strong> <?php echo $filter->getSummary(); ?>
</div>
<?php endif; ?>

<div class="report-content">
<?php
switch ($reportType) {
    case 'income':
        include __DIR__ . '/finance_income_report.php';
        break;
    case 'expense':
        include __DIR__ . '/finance_expense_report.php';
        break;
    case 'profit_loss':
        include __DIR__ . '/finance_profit_loss_report.php';
        break;
    default:
        echo '<div class="no-results"><p>Invalid report type selected.</p></div>';
}
?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
