<?php
// Add data isolation - each user sees only their own data
$isolationWhere = getDataIsolationWhere();

$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "date_col >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}
if (!empty($filter->dateTo)) {
    $whereConditions[] = "date_col <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

// Check if income table exists and has created_by column
$conn = getDBConnection();
$incomeTableExists = false;
$hasCreatedBy = false;

$tableCheck = $conn->query("SHOW TABLES LIKE 'income'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $incomeTableExists = true;
    $columnCheck = $conn->query("SHOW COLUMNS FROM income LIKE 'created_by'");
    $hasCreatedBy = $columnCheck && $columnCheck->num_rows > 0;
}

// Initialize default values
$trendResult = null;
$summary = ['total_income' => 0, 'total_expenses' => 0, 'net_profit' => 0];

// Only run queries if income table exists with proper structure
if ($incomeTableExists && $hasCreatedBy) {
    // Monthly trend query with data isolation
    $trendQuery = "
        SELECT 
            DATE_FORMAT(date_col, '%Y-%m') as month,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
            SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_profit
        FROM (
            SELECT income_date as date_col, amount, 'income' as type FROM income WHERE $isolationWhere
            UNION ALL
            SELECT expense_date as date_col, amount, 'expense' as type FROM expenses WHERE $isolationWhere
        ) combined
        WHERE $whereClause
        GROUP BY month
        ORDER BY month DESC
    ";

    $trendResult = $reportGen->executeQuery($trendQuery, $params, $types);

    // Overall summary with data isolation
    $summaryQuery = "
        SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
            SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_profit
        FROM (
            SELECT income_date as date_col, amount, 'income' as type FROM income WHERE $isolationWhere
            UNION ALL
            SELECT expense_date as date_col, amount, 'expense' as type FROM expenses WHERE $isolationWhere
        ) combined
        WHERE $whereClause
    ";

    $summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
    $summary = $summaryResult ? $summaryResult->fetch_assoc() : $summary;
} else {
    // Fallback: Only use expenses data
    $expenseQuery = "
        SELECT 
            0 as total_income,
            COALESCE(SUM(amount), 0) as total_expenses,
            -COALESCE(SUM(amount), 0) as net_profit
        FROM expenses 
        WHERE $isolationWhere AND $whereClause
    ";
    
    $summaryResult = $reportGen->executeQuery($expenseQuery, $params, $types);
    $summary = $summaryResult ? $summaryResult->fetch_assoc() : $summary;
}

$profitMargin = ($summary['total_income'] ?? 0) > 0 
    ? (($summary['net_profit'] ?? 0) / ($summary['total_income'] ?? 1)) * 100 
    : 0;
?>

<h3>Profit & Loss Report</h3>

<?php if (!$incomeTableExists || !$hasCreatedBy): ?>
<div class="alert alert-warning" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px;">
    <strong>⚠️ Notice:</strong> The income table is not fully configured. 
    <?php if (!$incomeTableExists): ?>
        The income table doesn't exist yet. 
    <?php else: ?>
        The income table is missing the created_by column for data isolation. 
    <?php endif; ?>
    This report will show expenses only. To enable full profit/loss tracking:
    <ol style="margin: 10px 0 0 20px;">
        <li>Run the database migration: <code>database/add_data_isolation.sql</code></li>
        <li>Or use the fix tool: <a href="../fix_data_isolation.php">fix_data_isolation.php</a></li>
    </ol>
</div>
<?php endif; ?>

<div class="report-summary">
    <div class="summary-card">
        <h4>Total Income</h4>
        <p class="summary-number text-success"><?php echo formatCurrency($summary['total_income'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Expenses</h4>
        <p class="summary-number text-danger"><?php echo formatCurrency($summary['total_expenses'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Net Profit/Loss</h4>
        <p class="summary-number <?php echo ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger'; ?>">
            <strong><?php echo formatCurrency($summary['net_profit'] ?? 0); ?></strong>
        </p>
    </div>
    <div class="summary-card">
        <h4>Profit Margin</h4>
        <p class="summary-number <?php echo $profitMargin >= 0 ? 'text-success' : 'text-danger'; ?>">
            <?php echo number_format($profitMargin, 1); ?>%
        </p>
    </div>
</div>

<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<?php 
// Prepare data for charts
$chartData = [];
if ($trendResult && $trendResult->num_rows > 0) {
    $trendResult->data_seek(0); // Reset pointer
    while ($row = $trendResult->fetch_assoc()) {
        $chartData[] = $row;
    }
    $trendResult->data_seek(0); // Reset again for table
}
?>

<!-- Financial Insights Section -->
<div class="insights-container" style="margin: 30px 0;">
    <?php
    $profitMargin = $summary['total_income'] > 0 ? (($summary['net_profit'] / $summary['total_income']) * 100) : 0;
    $isProfit = $summary['net_profit'] >= 0;
    ?>
    
    <!-- Key Insights -->
    <div style="background: linear-gradient(135deg, <?php echo $isProfit ? '#28a745' : '#dc3545'; ?> 0%, <?php echo $isProfit ? '#20c997' : '#c82333'; ?> 100%); padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: white; margin-bottom: 20px;">
        <h4 style="margin-top: 0; color: white;">📊 Financial Performance Summary</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 6px; text-align: center;">
                <div style="font-size: 2.5em; margin-bottom: 10px;">💰</div>
                <div style="font-size: 0.9em; opacity: 0.9;">Total Income</div>
                <div style="font-size: 1.5em; font-weight: bold; margin-top: 5px;"><?php echo formatCurrency($summary['total_income']); ?></div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 6px; text-align: center;">
                <div style="font-size: 2.5em; margin-bottom: 10px;">💸</div>
                <div style="font-size: 0.9em; opacity: 0.9;">Total Expenses</div>
                <div style="font-size: 1.5em; font-weight: bold; margin-top: 5px;"><?php echo formatCurrency($summary['total_expenses']); ?></div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 6px; text-align: center;">
                <div style="font-size: 2.5em; margin-bottom: 10px;"><?php echo $isProfit ? '📈' : '📉'; ?></div>
                <div style="font-size: 0.9em; opacity: 0.9;">Net <?php echo $isProfit ? 'Profit' : 'Loss'; ?></div>
                <div style="font-size: 1.5em; font-weight: bold; margin-top: 5px;"><?php echo formatCurrency($summary['net_profit']); ?></div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 6px; text-align: center;">
                <div style="font-size: 2.5em; margin-bottom: 10px;">📊</div>
                <div style="font-size: 0.9em; opacity: 0.9;">Profit Margin</div>
                <div style="font-size: 1.5em; font-weight: bold; margin-top: 5px;"><?php echo number_format($profitMargin, 1); ?>%</div>
            </div>
        </div>
    </div>
    
    <!-- Actionable Recommendations -->
    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h4 style="margin-top: 0; color: #2c3e50;">💡 Recommendations for Your Farm</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
            <?php if ($profitMargin >= 20): ?>
                <div style="border-left: 4px solid #28a745; padding: 15px; background: #d4edda;">
                    <strong style="color: #155724;">✅ Excellent Performance!</strong>
                    <p style="margin: 8px 0 0 0; color: #155724;">Your farm is highly profitable. Consider reinvesting profits into expansion or new equipment.</p>
                </div>
            <?php elseif ($profitMargin >= 10): ?>
                <div style="border-left: 4px solid #ffc107; padding: 15px; background: #fff3cd;">
                    <strong style="color: #856404;">👍 Good Performance</strong>
                    <p style="margin: 8px 0 0 0; color: #856404;">Maintain current operations and look for opportunities to optimize costs further.</p>
                </div>
            <?php elseif ($profitMargin >= 0): ?>
                <div style="border-left: 4px solid #fd7e14; padding: 15px; background: #ffe5d0;">
                    <strong style="color: #8a4419;">⚠️ Low Profit Margin</strong>
                    <p style="margin: 8px 0 0 0; color: #8a4419;">Review your expense categories and identify areas where costs can be reduced.</p>
                </div>
            <?php else: ?>
                <div style="border-left: 4px solid #dc3545; padding: 15px; background: #f8d7da;">
                    <strong style="color: #721c24;">❌ Operating at Loss</strong>
                    <p style="margin: 8px 0 0 0; color: #721c24;">Urgent action needed! Review all expenses and consider increasing income sources.</p>
                </div>
            <?php endif; ?>
            
            <div style="border-left: 4px solid #17a2b8; padding: 15px; background: #d1ecf1;">
                <strong style="color: #0c5460;">📈 Increase Income</strong>
                <p style="margin: 8px 0 0 0; color: #0c5460;">Explore new markets, improve crop yields, or add value-added products to boost revenue.</p>
            </div>
            
            <div style="border-left: 4px solid #6610f2; padding: 15px; background: #e7d6ff;">
                <strong style="color: #3d0a91;">💰 Cost Management</strong>
                <p style="margin: 8px 0 0 0; color: #3d0a91;">Negotiate bulk discounts with suppliers and eliminate unnecessary expenses.</p>
            </div>
            
            <div style="border-left: 4px solid #20c997; padding: 15px; background: #d4f4ea;">
                <strong style="color: #0f6848;">📊 Track Regularly</strong>
                <p style="margin: 8px 0 0 0; color: #0f6848;">Monitor your finances weekly to catch issues early and make timely adjustments.</p>
            </div>
        </div>
    </div>
</div>

<?php if ($trendResult && $trendResult->num_rows > 0): ?>
<h4>Monthly Profit & Loss Data Table</h4>
<div class="table-responsive">
    <table class="data-table" id="profitLossTable">
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Income</th>
                <th>Total Expenses</th>
                <th>Net Profit/Loss</th>
                <th>Profit Margin</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $trendResult->fetch_assoc()): 
                $margin = $row['total_income'] > 0 ? ($row['net_profit'] / $row['total_income']) * 100 : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['month']); ?></td>
                <td class="text-success"><?php echo formatCurrency($row['total_income']); ?></td>
                <td class="text-danger"><?php echo formatCurrency($row['total_expenses']); ?></td>
                <td class="<?php echo $row['net_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <strong><?php echo formatCurrency($row['net_profit']); ?></strong>
                </td>
                <td class="<?php echo $margin >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <?php echo number_format($margin, 1); ?>%
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results"><p>No financial data available.</p></div>
<?php endif; ?>

<script>
// CSV Export Function
function exportToCSV() {
    let csv = 'Profit & Loss Report\n\n';
    csv += 'Total Income,<?php echo $summary['total_income'] ?? 0; ?>\n';
    csv += 'Total Expenses,<?php echo $summary['total_expenses'] ?? 0; ?>\n';
    csv += 'Net Profit/Loss,<?php echo $summary['net_profit'] ?? 0; ?>\n';
    csv += 'Profit Margin,<?php echo number_format($profitMargin, 1); ?>%\n\n';
    const table = document.getElementById('profitLossTable');
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
    a.download = 'profit_loss_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>

<style>
@media print {
    .charts-container {
        page-break-inside: avoid;
    }
    canvas {
        max-height: 300px !important;
    }
}
</style>
