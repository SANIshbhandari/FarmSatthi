<?php
/**
 * Livestock Production Report
 * Shows production metrics (milk, eggs, meat), feed consumption, and mortality
 */

$aggregation = $filter->customFilters['aggregation'] ?? 'daily';
$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "lp.production_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}

if (!empty($filter->dateTo)) {
    $whereConditions[] = "lp.production_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

if (!empty($filter->customFilters['animal_type'])) {
    $whereConditions[] = "l.animal_type = ?";
    $params[] = $filter->customFilters['animal_type'];
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

// Determine grouping based on aggregation
$dateFormat = $aggregation === 'monthly' ? '%Y-%m' : '%Y-%m-%d';
$dateLabel = $aggregation === 'monthly' ? 'Month' : 'Date';

$query = "
    SELECT 
        DATE_FORMAT(lp.production_date, '$dateFormat') as period,
        l.animal_type,
        lp.animal_id,
        SUM(lp.milk_production) as total_milk,
        SUM(lp.egg_production) as total_eggs,
        SUM(lp.meat_production) as total_meat,
        SUM(lp.feed_consumption) as total_feed,
        COUNT(DISTINCT lp.production_date) as production_days
    FROM livestock_production lp
    INNER JOIN livestock l ON lp.livestock_id = l.id
    WHERE $whereClause
    GROUP BY period, l.animal_type, lp.animal_id
    ORDER BY period DESC, l.animal_type
";

$result = $reportGen->executeQuery($query, $params, $types);

$summaryQuery = "
    SELECT 
        SUM(lp.milk_production) as total_milk,
        SUM(lp.egg_production) as total_eggs,
        SUM(lp.meat_production) as total_meat,
        SUM(lp.feed_consumption) as total_feed,
        COUNT(DISTINCT lp.livestock_id) as producing_animals
    FROM livestock_production lp
    INNER JOIN livestock l ON lp.livestock_id = l.id
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

// Get mortality data
$mortalityQuery = "
    SELECT COUNT(*) as deaths
    FROM livestock_mortality
    WHERE death_date BETWEEN ? AND ?
";
$mortalityParams = [
    $filter->dateFrom ?: '1900-01-01',
    $filter->dateTo ?: date('Y-m-d')
];
$mortalityResult = $reportGen->executeQuery($mortalityQuery, $mortalityParams, 'ss');
$mortality = $mortalityResult ? $mortalityResult->fetch_assoc() : [];
?>

<h3>Livestock Production Report (<?php echo ucfirst($aggregation); ?>)</h3>

<div class="report-summary">
    <div class="summary-card">
        <h4>Total Milk Production</h4>
        <p class="summary-number"><?php echo number_format($summary['total_milk'] ?? 0, 2); ?> L</p>
    </div>
    <div class="summary-card">
        <h4>Total Egg Production</h4>
        <p class="summary-number"><?php echo number_format($summary['total_eggs'] ?? 0); ?> eggs</p>
    </div>
    <div class="summary-card">
        <h4>Total Meat Production</h4>
        <p class="summary-number"><?php echo number_format($summary['total_meat'] ?? 0, 2); ?> kg</p>
    </div>
    <div class="summary-card">
        <h4>Total Feed Consumed</h4>
        <p class="summary-number"><?php echo number_format($summary['total_feed'] ?? 0, 2); ?> kg</p>
    </div>
    <div class="summary-card">
        <h4>Mortality Count</h4>
        <p class="summary-number text-danger"><?php echo number_format($mortality['deaths'] ?? 0); ?></p>
    </div>
</div>

<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table" id="productionTable">
        <thead>
            <tr>
                <th><?php echo $dateLabel; ?></th>
                <th>Animal Type</th>
                <th>Animal ID</th>
                <th>Milk (L)</th>
                <th>Eggs</th>
                <th>Meat (kg)</th>
                <th>Feed (kg)</th>
                <th>Days</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['period']); ?></td>
                <td><?php echo htmlspecialchars($row['animal_type']); ?></td>
                <td><?php echo htmlspecialchars($row['animal_id'] ?? 'N/A'); ?></td>
                <td><?php echo number_format($row['total_milk'], 2); ?></td>
                <td><?php echo number_format($row['total_eggs']); ?></td>
                <td><?php echo number_format($row['total_meat'], 2); ?></td>
                <td><?php echo number_format($row['total_feed'], 2); ?></td>
                <td><?php echo number_format($row['production_days']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results">
    <p>No production data available for the selected filters.</p>
</div>
<?php endif; ?>

<script>
function exportToCSV() {
    const table = document.getElementById('productionTable');
    let csv = 'Livestock Production Report\n\n';
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
    csv += headers.join(',') + '\n';
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => row.push('"' + td.textContent.replace(/,/g, '') + '"'));
        csv += row.join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'livestock_production_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
