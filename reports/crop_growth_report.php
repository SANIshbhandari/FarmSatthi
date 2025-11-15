<?php
/**
 * Crop Growth Monitoring Report
 * Shows growth stages, inputs used, and disease notes
 */

// Build query with filters
$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "cgm.monitoring_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}

if (!empty($filter->dateTo)) {
    $whereConditions[] = "cgm.monitoring_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

if (!empty($filter->customFilters['crop_type'])) {
    $whereConditions[] = "c.crop_type = ?";
    $params[] = $filter->customFilters['crop_type'];
    $types .= 's';
}

if (!empty($filter->customFilters['growth_stage'])) {
    $whereConditions[] = "cgm.growth_stage = ?";
    $params[] = $filter->customFilters['growth_stage'];
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

// Main query
$query = "
    SELECT 
        c.crop_name,
        c.crop_type,
        cgm.monitoring_date,
        cgm.growth_stage,
        cgm.watering_frequency,
        cgm.fertilizer_used,
        cgm.pesticide_used,
        cgm.disease_notes,
        cgm.health_status
    FROM crop_growth_monitoring cgm
    INNER JOIN crops c ON cgm.crop_id = c.id
    WHERE $whereClause
    ORDER BY cgm.monitoring_date DESC, c.crop_name
";

$result = $reportGen->executeQuery($query, $params, $types);

// Calculate summary statistics
$summaryQuery = "
    SELECT 
        COUNT(DISTINCT cgm.crop_id) as monitored_crops,
        COUNT(*) as total_observations,
        SUM(CASE WHEN cgm.health_status = 'excellent' THEN 1 ELSE 0 END) as excellent_count,
        SUM(CASE WHEN cgm.health_status = 'good' THEN 1 ELSE 0 END) as good_count,
        SUM(CASE WHEN cgm.health_status = 'fair' THEN 1 ELSE 0 END) as fair_count,
        SUM(CASE WHEN cgm.health_status = 'poor' THEN 1 ELSE 0 END) as poor_count
    FROM crop_growth_monitoring cgm
    INNER JOIN crops c ON cgm.crop_id = c.id
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

?>

<h3>Crop Growth Monitoring Report</h3>

<!-- Summary Cards -->
<div class="report-summary">
    <div class="summary-card">
        <h4>Monitored Crops</h4>
        <p class="summary-number"><?php echo number_format($summary['monitored_crops'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Observations</h4>
        <p class="summary-number"><?php echo number_format($summary['total_observations'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Excellent Health</h4>
        <p class="summary-number text-success"><?php echo number_format($summary['excellent_count'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Good Health</h4>
        <p class="summary-number"><?php echo number_format($summary['good_count'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Needs Attention</h4>
        <p class="summary-number text-warning"><?php echo number_format(($summary['fair_count'] ?? 0) + ($summary['poor_count'] ?? 0)); ?></p>
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
    <table class="data-table" id="growthTable">
        <thead>
            <tr>
                <th>Crop Name</th>
                <th>Type</th>
                <th>Monitoring Date</th>
                <th>Growth Stage</th>
                <th>Watering</th>
                <th>Fertilizer Used</th>
                <th>Pesticide Used</th>
                <th>Disease Notes</th>
                <th>Health Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['crop_name']); ?></td>
                <td><?php echo htmlspecialchars($row['crop_type']); ?></td>
                <td><?php echo formatDate($row['monitoring_date']); ?></td>
                <td>
                    <span class="badge badge-info">
                        <?php echo ucfirst(htmlspecialchars($row['growth_stage'])); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($row['watering_frequency'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['fertilizer_used'] ?? 'None'); ?></td>
                <td><?php echo htmlspecialchars($row['pesticide_used'] ?? 'None'); ?></td>
                <td><?php echo htmlspecialchars($row['disease_notes'] ?? 'None'); ?></td>
                <td>
                    <?php 
                    $healthClass = [
                        'excellent' => 'success',
                        'good' => 'info',
                        'fair' => 'warning',
                        'poor' => 'danger'
                    ][$row['health_status']] ?? 'secondary';
                    ?>
                    <span class="badge badge-<?php echo $healthClass; ?>">
                        <?php echo ucfirst(htmlspecialchars($row['health_status'])); ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results">
    <p>No growth monitoring data available for the selected filters.</p>
    <p>Try adjusting your date range or filters.</p>
</div>
<?php endif; ?>

<script>
function exportToCSV() {
    const table = document.getElementById('growthTable');
    let csv = 'Crop Growth Monitoring Report\n\n';
    
    // Add headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
    csv += headers.join(',') + '\n';
    
    // Add rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            let text = td.textContent.replace(/,/g, ';').trim();
            row.push('"' + text + '"');
        });
        csv += row.join(',') + '\n';
    });
    
    // Download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'crop_growth_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
