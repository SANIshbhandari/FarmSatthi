<?php
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

// Monthly trend query
$trendQuery = "
    SELECT 
        DATE_FORMAT(date_col, '%Y-%m') as month,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
        SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_profit
    FROM (
        SELECT income_date as date_col, amount, 'income' as type FROM income
        UNION ALL
        SELECT expense_date as date_col, amount, 'expense' as type FROM expenses
    ) combined
    WHERE $whereClause
    GROUP BY month
    ORDER BY month DESC
";

$trendResult = $reportGen->executeQuery($trendQuery, $params, $types);

// Overall summary
$summaryQuery = "
    SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
        SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_profit
    FROM (
        SELECT income_date as date_col, amount, 'income' as type FROM income
        UNION ALL
        SELECT expense_date as date_col, amount, 'expense' as type FROM expenses
    ) combined
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

$profitMargin = ($summary['total_income'] ?? 0) > 0 
    ? (($summary['net_profit'] ?? 0) / ($summary['total_income'] ?? 1)) * 100 
    : 0;
?>

<h3>Profit & Loss Report</h3>

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
$trendResult->data_seek(0); // Reset pointer
$chartData = [];
while ($row = $trendResult->fetch_assoc()) {
    $chartData[] = $row;
}
$trendResult->data_seek(0); // Reset again for table
?>

<!-- Charts Section -->
<div class="charts-container" style="margin: 30px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Pie Chart -->
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h4 style="text-align: center; margin-bottom: 20px;">Income vs Expenses Distribution</h4>
            <canvas id="pieChart" style="max-height: 300px;"></canvas>
        </div>
        
        <!-- Profit Margin Gauge -->
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h4 style="text-align: center; margin-bottom: 20px;">Profit Margin Indicator</h4>
            <canvas id="gaugeChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
    
    <!-- Trend Line Chart -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h4 style="text-align: center; margin-bottom: 20px;">Monthly Trend Analysis</h4>
        <canvas id="trendChart" style="max-height: 400px;"></canvas>
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

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Debug: Check if Chart.js loaded
console.log('Chart.js loaded:', typeof Chart !== 'undefined');
console.log('Canvas elements found:', document.querySelectorAll('canvas').length);

// Prepare chart data from PHP
const chartData = <?php echo json_encode($chartData); ?>;
const totalIncome = <?php echo $summary['total_income'] ?? 0; ?>;
const totalExpenses = <?php echo $summary['total_expenses'] ?? 0; ?>;
const profitMargin = <?php echo $profitMargin; ?>;

// Debug: Log data
console.log('Chart Data:', { chartData, totalIncome, totalExpenses, profitMargin });

// Wait for DOM to be fully loaded
if (typeof Chart === 'undefined') {
    console.error('Chart.js library not loaded!');
} else {
    initializeCharts();
}

function initializeCharts() {
    // Check if canvas elements exist
    const pieCanvas = document.getElementById('pieChart');
    const gaugeCanvas = document.getElementById('gaugeChart');
    const trendCanvas = document.getElementById('trendChart');
    
    if (!pieCanvas || !gaugeCanvas || !trendCanvas) {
        console.error('Canvas elements not found!');
        return;
    }
    
    console.log('Initializing charts...');

// 1. PIE CHART - Income vs Expenses
const pieCtx = pieCanvas.getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: ['Income', 'Expenses'],
        datasets: [{
            data: [totalIncome, totalExpenses],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { size: 14 },
                    padding: 15
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': Rs. ' + value.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// 2. GAUGE CHART - Profit Margin
const gaugeCtx = document.getElementById('gaugeChart').getContext('2d');
const gaugeValue = Math.min(Math.max(profitMargin, -100), 100); // Clamp between -100 and 100
const gaugeColor = gaugeValue >= 0 ? 'rgba(75, 192, 192, 0.8)' : 'rgba(255, 99, 132, 0.8)';

new Chart(gaugeCtx, {
    type: 'doughnut',
    data: {
        labels: ['Profit Margin', 'Remaining'],
        datasets: [{
            data: [Math.abs(gaugeValue), 100 - Math.abs(gaugeValue)],
            backgroundColor: [gaugeColor, 'rgba(200, 200, 200, 0.2)'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        circumference: 180,
        rotation: 270,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        }
    },
    plugins: [{
        id: 'gaugeText',
        afterDraw: (chart) => {
            const ctx = chart.ctx;
            const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
            const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2 + 20;
            
            ctx.save();
            ctx.font = 'bold 32px Arial';
            ctx.fillStyle = gaugeColor.replace('0.8', '1');
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(profitMargin.toFixed(1) + '%', centerX, centerY);
            
            ctx.font = '14px Arial';
            ctx.fillStyle = '#666';
            ctx.fillText('Profit Margin', centerX, centerY + 30);
            ctx.restore();
        }
    }]
});

// 3. TREND LINE CHART - Monthly Analysis
const trendCtx = document.getElementById('trendChart').getContext('2d');
const months = chartData.map(d => d.month).reverse();
const incomeData = chartData.map(d => parseFloat(d.total_income)).reverse();
const expenseData = chartData.map(d => parseFloat(d.total_expenses)).reverse();
const profitData = chartData.map(d => parseFloat(d.net_profit)).reverse();

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Income',
                data: incomeData,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Expenses',
                data: expenseData,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Net Profit/Loss',
                data: profitData,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: { size: 14 },
                    padding: 15,
                    usePointStyle: true
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rs. ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value.toLocaleString();
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

} // End of initializeCharts function

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
