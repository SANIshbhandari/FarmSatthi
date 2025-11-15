<?php
$whereConditions = ['1=1'];
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
$categoryResult->data_seek(0);
$categoryData = [];
while ($cat = $categoryResult->fetch_assoc()) {
    $categoryData[] = $cat;
}
$categoryResult->data_seek(0);
?>

<?php if ($categoryResult && $categoryResult->num_rows > 0): ?>
<!-- Expense Category Chart -->
<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 30px 0;">
    <h4 style="text-align: center; margin-bottom: 20px;">Expense Distribution by Category</h4>
    <div style="max-width: 600px; margin: 0 auto;">
        <canvas id="expensePieChart"></canvas>
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

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Debug: Check if Chart.js loaded
console.log('Chart.js loaded:', typeof Chart !== 'undefined');

// Prepare category data for pie chart
const categoryData = <?php echo json_encode($categoryData); ?>;

// Check if we have data
if (!categoryData || categoryData.length === 0) {
    console.warn('No category data available for chart');
    const chartContainer = document.getElementById('expensePieChart');
    if (chartContainer) {
        chartContainer.parentElement.innerHTML = '<p style="text-align: center; padding: 40px; color: #666;">No expense data available to display chart.</p>';
    }
} else {
    initializeExpenseChart();
}

function initializeExpenseChart() {
const categories = categoryData.map(d => d.category);
const amounts = categoryData.map(d => parseFloat(d.total));

// Generate colors for categories
const colors = [
    'rgba(255, 99, 132, 0.8)',
    'rgba(54, 162, 235, 0.8)',
    'rgba(255, 206, 86, 0.8)',
    'rgba(75, 192, 192, 0.8)',
    'rgba(153, 102, 255, 0.8)',
    'rgba(255, 159, 64, 0.8)',
    'rgba(199, 199, 199, 0.8)',
    'rgba(83, 102, 255, 0.8)',
    'rgba(255, 99, 255, 0.8)',
    'rgba(99, 255, 132, 0.8)'
];

// Expense Category Pie Chart
const expensePieCanvas = document.getElementById('expensePieChart');
if (!expensePieCanvas) {
    console.error('Expense pie chart canvas not found!');
    return;
}
const expensePieCtx = expensePieCanvas.getContext('2d');
new Chart(expensePieCtx, {
    type: 'doughnut',
    data: {
        labels: categories,
        datasets: [{
            data: amounts,
            backgroundColor: colors.slice(0, categories.length),
            borderColor: colors.slice(0, categories.length).map(c => c.replace('0.8', '1')),
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: { size: 12 },
                    padding: 15,
                    generateLabels: function(chart) {
                        const data = chart.data;
                        if (data.labels.length && data.datasets.length) {
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            return data.labels.map((label, i) => {
                                const value = data.datasets[0].data[i];
                                const percentage = ((value / total) * 100).toFixed(1);
                                return {
                                    text: label + ' (' + percentage + '%)',
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                        return [];
                    }
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

console.log('Expense chart initialized successfully!');
} // End of initializeExpenseChart function

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
