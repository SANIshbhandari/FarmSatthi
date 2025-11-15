<?php
/**
 * Crop Sales Report
 * Shows sales data with buyer information and revenue
 */

// Build query with filters
$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "cs.sale_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}

if (!empty($filter->dateTo)) {
    $whereConditions[] = "cs.sale_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

if (!empty($filter->customFilters['crop_name'])) {
    $whereConditions[] = "cs.crop_name LIKE ?";
    $params[] = '%' . $filter->customFilters['crop_name'] . '%';
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

// Main query
$query = "
    SELECT 
        cs.crop_name,
        cs.quantity_sold,
        cs.unit,
        cs.rate_per_unit,
        cs.total_price,
        cs.buyer_name,
        cs.buyer_contact,
        cs.sale_date
    FROM crop_sales cs
    WHERE $whereClause
    ORDER BY cs.sale_date DESC
";

$result = $reportGen->executeQuery($query, $params, $types);

// Calculate summary statistics
$summaryQuery = "
    SELECT 
        COUNT(*) as total_sales,
        SUM(cs.quantity_sold) as total_quantity,
        SUM(cs.total_price) as total_revenue,
        AVG(cs.rate_per_unit) as avg_rate,
        COUNT(DISTINCT cs.buyer_name) as unique_buyers
    FROM crop_sales cs
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

// Top buyers
$topBuyersQuery = "
    SELECT 
        cs.buyer_name,
        COUNT(*) as purchase_count,
        SUM(cs.total_price) as total_spent
    FROM crop_sales cs
    WHERE $whereClause
    GROUP BY cs.buyer_name
    ORDER BY total_spent DESC
    LIMIT 5
";

$topBuyers = $reportGen->executeQuery($topBuyersQuery, $params, $types);

?>

<h3>Crop Sales Report</h3>

<!-- Summary Cards -->
<div class="report-summary">
    <div class="summary-card">
        <h4>Total Sales</h4>
        <p class="summary-number"><?php echo number_format($summary['total_sales'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Quantity Sold</h4>
        <p class="summary-number"><?php echo number_format($summary['total_quantity'] ?? 0, 2); ?> kg</p>
    </div>
    <div class="summary-card">
        <h4>Total Revenue</h4>
        <p class="summary-number text-success"><?php echo formatCurrency($summary['total_revenue'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Average Rate</h4>
        <p class="summary-number"><?php echo formatCurrency($summary['avg_rate'] ?? 0); ?>/kg</p>
    </div>
    <div class="summary-card">
        <h4>Unique Buyers</h4>
        <p class="summary-number"><?php echo number_format($summary['unique_buyers'] ?? 0); ?></p>
    </div>
</div>

<!-- Top Buyers Section -->
<?php if ($topBuyers && $topBuyers->num_rows > 0): ?>
<div class="top-buyers-section">
    <h4>Top 5 Buyers</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>Buyer Name</th>
                <th>Number of Purchases</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($buyer = $topBuyers->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($buyer['buyer_name']); ?></td>
                <td><?php echo number_format($buyer['purchase_count']); ?></td>
                <td><?php echo formatCurrency($buyer['total_spent']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Export Buttons -->
<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<!-- Data Table -->
<?php if ($result && $result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table" id="salesTable">
        <thead>
            <tr>
                <th>Crop Name</th>
                <th>Quantity Sold</th>
                <th>Rate per Unit</th>
                <th>Total Price</th>
                <th>Buyer Name</th>
                <th>Contact</th>
                <th>Sale Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['crop_name']); ?></td>
                <td><?php echo number_format($row['quantity_sold'], 2) . ' ' . htmlspecialchars($row['unit']); ?></td>
                <td><?php echo formatCurrency($row['rate_per_unit']); ?></td>
                <td><strong><?php echo formatCurrency($row['total_price']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['buyer_contact'] ?? 'N/A'); ?></td>
                <td><?php echo formatDate($row['sale_date']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results">
    <p>No sales data available for the selected filters.</p>
    <p>Try adjusting your date range or filters.</p>
</div>
<?php endif; ?>

<script>
function exportToCSV() {
    const table = document.getElementById('salesTable');
    let csv = 'Crop Sales Report\n\n';
    
    // Add summary
    csv += 'Total Sales,<?php echo $summary['total_sales'] ?? 0; ?>\n';
    csv += 'Total Revenue,<?php echo $summary['total_revenue'] ?? 0; ?>\n';
    csv += 'Total Quantity,<?php echo $summary['total_quantity'] ?? 0; ?>\n\n';
    
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
    a.download = 'crop_sales_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
