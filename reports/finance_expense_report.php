<?php
// Add data isolation - each user sees only their own data
$isolationWhere = getDataIsolationWhere();

$whereConditions = [$isolationWhere];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "expense_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}
if (!empty($filter->dateTo)) {
    $whereConditions[] = "expense_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

$query = "SELECT category, amount, expense_date, description, payment_method FROM expenses WHERE $whereClause ORDER BY expense_date DESC";
$result = $reportGen->executeQuery($query, $params, $types);

$categoryQuery = "
    SELECT 
        category,
        SUM(amount) as total,
        COUNT(*) as count
    FROM expenses 
    WHERE $whereClause
    GROUP BY category
    ORDER BY total DESC
";
$categoryResult = $reportGen->executeQuery($categoryQuery, $params, $types);

$summaryQuery = "SELECT SUM(amount) as total_expenses, COUNT(*) as expense_count FROM expenses WHERE $whereClause";
$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];
?>

<h3>Expense Report</h3>

<div class="report-summary">
    <div class="summary-card">
        <h4>Total Expenses</h4>
        <p class="summary-number text-danger"><strong><?php echo formatCurrency($summary['total_expenses'] ?? 0); ?></strong></p>
    </div>
    <div class="summary-card">
        <h4>Number of Expenses</h4>
        <p class="summary-number"><?php echo number_format($summary['expense_count'] ?? 0); ?></p>
    </div>
</div>

<?php 
// Prepare category data for chart
$categoryData = [];
if ($categoryResult && $categoryResult->num_rows > 0) {
    $categoryResult->data_seek(0);
    while ($cat = $categoryResult->fetch_assoc()) {
        $categoryData[] = $cat;
    }
    $categoryResult->data_seek(0);
}
?>

<?php if ($categoryResult && $categoryResult->num_rows > 0): ?>
<!-- Expense Insights -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: white; margin: 30px 0;">
    <h4 style="margin-top: 0; color: white;">💡 Expense Insights & Recommendations</h4>
    <?php
    $topCategory = null;
    $topPercentage = 0;
    if ($categoryResult && $categoryResult->num_rows > 0) {
        $categoryResult->data_seek(0); // Reset pointer
        $topCategory = $categoryResult->fetch_assoc();
        $categoryResult->data_seek(0); // Reset again for table
        $topPercentage = ($topCategory['total'] / ($summary['total_expenses'] ?? 1)) * 100;
    }
    ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
        <?php if ($topCategory): ?>
        <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px;">
            <div style="font-size: 2em; margin-bottom: 8px;">📊</div>
            <strong>Top Expense Category</strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9em;"><?php echo htmlspecialchars($topCategory['category']); ?> accounts for <?php echo number_format($topPercentage, 1); ?>% of total expenses.</p>
        </div>
        <?php if ($topPercentage > 40): ?>
        <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px;">
            <div style="font-size: 2em; margin-bottom: 8px;">⚠️</div>
            <strong>Cost Optimization</strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9em;">Consider bulk purchasing or finding alternatives for <?php echo htmlspecialchars($topCategory['category']); ?> to reduce costs.</p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px;">
            <div style="font-size: 2em; margin-bottom: 8px;">💰</div>
            <strong>Budget Planning</strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9em;">Set monthly budgets for each category to control spending and improve profitability.</p>
        </div>
        <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px;">
            <div style="font-size: 2em; margin-bottom: 8px;">📈</div>
            <strong>Track Trends</strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9em;">Compare expenses month-over-month to identify seasonal patterns and plan accordingly.</p>
        </div>
    </div>
</div>

<div class="category-breakdown">
    <h4>Expenses by Category (Detailed)</h4>
    <table class="data-table" id="categoryTable">
        <thead>
            <tr>
                <th>Category</th>
                <th>Count</th>
                <th>Total Amount</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = $summary['total_expenses'] ?? 1;
            while ($cat = $categoryResult->fetch_assoc()): 
                $percentage = ($cat['total'] / $total) * 100;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($cat['category']); ?></td>
                <td><?php echo number_format($cat['count']); ?></td>
                <td><?php echo formatCurrency($cat['total']); ?></td>
                <td><?php echo number_format($percentage, 1); ?>%</td>
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
    <table class="data-table" id="expenseTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo formatDate($row['expense_date']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><strong><?php echo formatCurrency($row['amount']); ?></strong></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $row['payment_method'])); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results"><p>No expense data available.</p></div>
<?php endif; ?>

<script>
// CSV Export Function
function exportToCSV() {
    let csv = 'Expense Report\n\n';
    csv += 'Total Expenses,<?php echo $summary['total_expenses'] ?? 0; ?>\n\n';
    
    // Add category breakdown
    csv += 'Category Breakdown\n';
    const catTable = document.getElementById('categoryTable');
    if (catTable) {
        const headers = [];
        catTable.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
        csv += headers.join(',') + '\n';
        catTable.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach(td => row.push('"' + td.textContent.replace(/,/g, '').trim() + '"'));
            csv += row.join(',') + '\n';
        });
    }
    
    csv += '\nDetailed Expenses\n';
    const table = document.getElementById('expenseTable');
    if (table) {
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
        csv += headers.join(',') + '\n';
        table.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach(td => row.push('"' + td.textContent.replace(/,/g, '').trim() + '"'));
            csv += row.join(',') + '\n';
        });
    }
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'expense_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
