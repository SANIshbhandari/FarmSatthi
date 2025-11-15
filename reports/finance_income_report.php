<?php
$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "income_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}
if (!empty($filter->dateTo)) {
    $whereConditions[] = "income_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

$query = "SELECT source, amount, income_date, description, payment_method FROM income WHERE $whereClause ORDER BY income_date DESC";
$result = $reportGen->executeQuery($query, $params, $types);

$summaryQuery = "
    SELECT 
        SUM(CASE WHEN source = 'crop_sales' THEN amount ELSE 0 END) as crop_income,
        SUM(CASE WHEN source = 'livestock_sales' THEN amount ELSE 0 END) as livestock_income,
        SUM(CASE WHEN source = 'miscellaneous' THEN amount ELSE 0 END) as misc_income,
        SUM(amount) as total_income
    FROM income WHERE $whereClause
";
$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];
?>

<h3>Income Report</h3>

<div class="report-summary">
    <div class="summary-card">
        <h4>Crop Sales Income</h4>
        <p class="summary-number text-success"><?php echo formatCurrency($summary['crop_income'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Livestock Sales Income</h4>
        <p class="summary-number text-success"><?php echo formatCurrency($summary['livestock_income'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Miscellaneous Income</h4>
        <p class="summary-number"><?php echo formatCurrency($summary['misc_income'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Income</h4>
        <p class="summary-number text-success"><strong><?php echo formatCurrency($summary['total_income'] ?? 0); ?></strong></p>
    </div>
</div>

<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table" id="incomeTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo formatDate($row['income_date']); ?></td>
                <td><span class="badge badge-success"><?php echo ucfirst(str_replace('_', ' ', $row['source'])); ?></span></td>
                <td><strong><?php echo formatCurrency($row['amount']); ?></strong></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $row['payment_method'])); ?></td>
                <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results"><p>No income data available.</p></div>
<?php endif; ?>

<script>
function exportToCSV() {
    let csv = 'Income Report\n\n';
    csv += 'Crop Sales Income,<?php echo $summary['crop_income'] ?? 0; ?>\n';
    csv += 'Livestock Sales Income,<?php echo $summary['livestock_income'] ?? 0; ?>\n';
    csv += 'Miscellaneous Income,<?php echo $summary['misc_income'] ?? 0; ?>\n';
    csv += 'Total Income,<?php echo $summary['total_income'] ?? 0; ?>\n\n';
    const table = document.getElementById('incomeTable');
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
    csv += headers.join(',') + '\n';
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => row.push('"' + td.textContent.replace(/,/g, '').trim() + '"'));
        csv += row.join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'income_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
