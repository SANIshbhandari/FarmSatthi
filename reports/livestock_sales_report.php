<?php
/**
 * Livestock Sales Report
 * Shows sales data with profit/loss calculations
 */

$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "ls.sale_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}

if (!empty($filter->dateTo)) {
    $whereConditions[] = "ls.sale_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

if (!empty($filter->customFilters['animal_type'])) {
    $whereConditions[] = "ls.animal_type = ?";
    $params[] = $filter->customFilters['animal_type'];
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

$query = "
    SELECT 
        ls.animal_type,
        ls.breed,
        ls.quantity,
        ls.selling_price,
        ls.purchase_cost,
        ls.profit_loss,
        ls.buyer_name,
        ls.buyer_contact,
        ls.sale_date,
        ls.notes
    FROM livestock_sales ls
    WHERE $whereClause
    ORDER BY ls.sale_date DESC
";

$result = $reportGen->executeQuery($query, $params, $types);

$summaryQuery = "
    SELECT 
        COUNT(*) as total_sales,
        SUM(ls.quantity) as total_quantity,
        SUM(ls.selling_price) as total_revenue,
        SUM(ls.purchase_cost) as total_cost,
        SUM(ls.profit_loss) as total_profit,
        COUNT(DISTINCT ls.buyer_name) as unique_buyers
    FROM livestock_sales ls
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

$topBuyersQuery = "
    SELECT 
        ls.buyer_name,
        COUNT(*) as purchase_count,
        SUM(ls.selling_price) as total_spent
    FROM livestock_sales ls
    WHERE $whereClause
    GROUP BY ls.buyer_name
    ORDER BY total_spent DESC
    LIMIT 5
";

$topBuyers = $reportGen->executeQuery($topBuyersQuery, $params, $types);
?>

<h3>Livestock Sales Report</h3>

<div class="report-summary">
    <div class="summary-card">
        <h4>Total Sales</h4>
        <p class="summary-number"><?php echo number_format($summary['total_sales'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Animals Sold</h4>
        <p class="summary-number"><?php echo number_format($summary['total_quantity'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Revenue</h4>
        <p class="summary-number text-success"><?php echo formatCurrency($summary['total_revenue'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Cost</h4>
        <p class="summary-number"><?php echo formatCurrency($summary['total_cost'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Net Profit/Loss</h4>
        <p class="summary-number <?php echo ($summary['total_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
            <?php echo formatCurrency($summary['total_profit'] ?? 0); ?>
        </p>
    </div>
</div>

<?php if ($topBuyers && $topBuyers->num_rows > 0): ?>
<div class="top-buyers-section">
    <h4>Top 5 Buyers</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>Buyer Name</th>
                <th>Purchases</th>
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

<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table" id="salesTable">
        <thead>
            <tr>
                <th>Animal Type</th>
                <th>Breed</th>
                <th>Quantity</th>
                <th>Selling Price</th>
                <th>Purchase Cost</th>
                <th>Profit/Loss</th>
                <th>Buyer Name</th>
                <th>Contact</th>
                <th>Sale Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['animal_type']); ?></td>
                <td><?php echo htmlspecialchars($row['breed'] ?? 'N/A'); ?></td>
                <td><?php echo number_format($row['quantity']); ?></td>
                <td><?php echo formatCurrency($row['selling_price']); ?></td>
                <td><?php echo formatCurrency($row['purchase_cost'] ?? 0); ?></td>
                <td class="<?php echo ($row['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <strong><?php echo formatCurrency($row['profit_loss'] ?? 0); ?></strong>
                </td>
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
</div>
<?php endif; ?>

<script>
function exportToCSV() {
    const table = document.getElementById('salesTable');
    let csv = 'Livestock Sales Report\n\n';
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
    a.download = 'livestock_sales_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
