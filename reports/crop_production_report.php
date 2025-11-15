<?php
/**
 * Crop Production Report
 * Shows crop name, field, dates, yields, costs, and profit
 */

// Build query with filters
$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "c.planting_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}

if (!empty($filter->dateTo)) {
    $whereConditions[] = "c.planting_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

if (!empty($filter->customFilters['crop_type'])) {
    $whereConditions[] = "c.crop_type = ?";
    $params[] = $filter->customFilters['crop_type'];
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

// Main query
$query = "
    SELECT 
        c.crop_name,
        c.field_location,
        c.planting_date,
        cp.harvest_date,
        cp.expected_yield,
        cp.actual_yield,
        cp.yield_unit,
        cp.production_cost,
        cp.profit,
        CASE 
            WHEN cp.expected_yield > 0 THEN ROUND((cp.actual_yield / cp.expected_yield * 100), 2)
            ELSE 0 
        END as yield_percentage,
        c.area_hectares
    FROM crops c
    LEFT JOIN crop_production cp ON c.id = cp.crop_id
    WHERE $whereClause
    ORDER BY c.planting_date DESC
";

$result = $reportGen->executeQuery($query, $params, $types);

// Calculate summary statistics
$summaryQuery = "
    SELECT 
        COUNT(DISTINCT c.id) as total_crops,
        SUM(c.area_hectares) as total_area,
        SUM(cp.actual_yield) as total_yield,
        SUM(cp.production_cost) as total_cost,
        SUM(cp.profit) as total_profit
    FROM crops c
    LEFT JOIN crop_production cp ON c.id = cp.crop_id
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

?>

<h3>Crop Production Report</h3>

<!-- Summary Cards -->
<div class="report-summary">
    <div class="summary-card">
        <h4>Total Crops</h4>
        <p class="summary-number"><?php echo number_format($summary['total_crops'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Area</h4>
        <p class="summary-number"><?php echo number_format($summary['total_area'] ?? 0, 2); ?> ha</p>
    </div>
    <div class="summary-card">
        <h4>Total Yield</h4>
        <p class="summary-number"><?php echo number_format($summary['total_yield'] ?? 0, 2); ?> kg</p>
    </div>
    <div class="summary-card">
        <h4>Total Cost</h4>
        <p class="summary-number"><?php echo formatCurrency($summary['total_cost'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Profit</h4>
        <p class="summary-number"><?php echo formatCurrency($summary['total_profit'] ?? 0); ?></p>
    </div>
</div>

<!-- Export Buttons -->
<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<!-- Data Table -->
<?php if ($result && $result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table" id="productionTable">
        <thead>
            <tr>
                <th>Crop Name</th>
                <th>Field/Plot</th>
                <th>Planting Date</th>
                <th>Harvest Date</th>
                <th>Expected Yield</th>
                <th>Actual Yield</th>
                <th>Yield %</th>
                <th>Production Cost</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['crop_name']); ?></td>
                <td><?php echo htmlspecialchars($row['field_location']); ?></td>
                <td><?php echo formatDate($row['planting_date']); ?></td>
                <td><?php echo $row['harvest_date'] ? formatDate($row['harvest_date']) : 'Not harvested'; ?></td>
                <td><?php echo $row['expected_yield'] ? number_format($row['expected_yield'], 2) . ' ' . htmlspecialchars($row['yield_unit']) : 'N/A'; ?></td>
                <td><?php echo $row['actual_yield'] ? number_format($row['actual_yield'], 2) . ' ' . htmlspecialchars($row['yield_unit']) : 'N/A'; ?></td>
                <td>
                    <?php 
                    $percentage = $row['yield_percentage'];
                    $class = $percentage >= 90 ? 'text-success' : ($percentage >= 70 ? 'text-warning' : 'text-danger');
                    echo '<span class="' . $class . '">' . number_format($percentage, 1) . '%</span>';
                    ?>
                </td>
                <td><?php echo formatCurrency($row['production_cost'] ?? 0); ?></td>
                <td>
                    <?php 
                    $profit = $row['profit'] ?? 0;
                    $profitClass = $profit >= 0 ? 'text-success' : 'text-danger';
                    echo '<span class="' . $profitClass . '">' . formatCurrency($profit) . '</span>';
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results">
    <p>No production data available for the selected filters.</p>
    <p>Try adjusting your date range or crop type filter.</p>
</div>
<?php endif; ?>

<script>
function exportToCSV() {
    const table = document.getElementById('productionTable');
    let csv = 'Crop Production Report\n\n';
    
    // Add headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
    csv += headers.join(',') + '\n';
    
    // Add rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            let text = td.textContent.replace(/,/g, '');
            row.push('"' + text + '"');
        });
        csv += row.join(',') + '\n';
    });
    
    // Download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'crop_production_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
