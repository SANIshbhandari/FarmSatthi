<?php
$pageTitle = 'Test Charts - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/lib/report_generator.php';

$conn = getDBConnection();
$reportGen = new ReportGenerator($conn);

// Check for actual data in database
$incomeCheck = $reportGen->executeQuery("SELECT COUNT(*) as count, SUM(amount) as total FROM income", [], '');
$expenseCheck = $reportGen->executeQuery("SELECT COUNT(*) as count, SUM(amount) as total FROM expenses", [], '');

$incomeData = $incomeCheck ? $incomeCheck->fetch_assoc() : ['count' => 0, 'total' => 0];
$expenseData = $expenseCheck ? $expenseCheck->fetch_assoc() : ['count' => 0, 'total' => 0];
?>

<div class="module-header">
    <h2>📊 Chart Test & Diagnostics</h2>
    <p>Testing if Chart.js is working and checking database data</p>
</div>

<div style="max-width: 1000px; margin: 30px auto;">
    <!-- Database Status -->
    <div style="background: <?php echo ($incomeData['count'] > 0 && $expenseData['count'] > 0) ? '#d4edda' : '#f8d7da'; ?>; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 2px solid <?php echo ($incomeData['count'] > 0 && $expenseData['count'] > 0) ? '#28a745' : '#dc3545'; ?>;">
        <h3 style="margin-top: 0;">Database Status</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <strong>Income Records:</strong> <?php echo $incomeData['count']; ?> records<br>
                <strong>Total Income:</strong> Rs. <?php echo number_format($incomeData['total'] ?? 0, 2); ?>
            </div>
            <div>
                <strong>Expense Records:</strong> <?php echo $expenseData['count']; ?> records<br>
                <strong>Total Expenses:</strong> Rs. <?php echo number_format($expenseData['total'] ?? 0, 2); ?>
            </div>
        </div>
        <?php if ($incomeData['count'] == 0 || $expenseData['count'] == 0): ?>
        <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 5px;">
            <strong>⚠️ No Data Found!</strong><br>
            Please run the seed file to add sample data:<br>
            <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 3px; display: inline-block; margin-top: 5px;">
                mysql -u root -p farm_management < database/reporting_seed.sql
            </code>
        </div>
        <?php endif; ?>
    </div>

    <!-- Test Chart -->
    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h3>Test Chart (Static Data)</h3>
        <canvas id="testChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
console.log('Chart.js loaded:', typeof Chart !== 'undefined');

// Simple test chart
const ctx = document.getElementById('testChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Income', 'Expenses', 'Profit'],
        datasets: [{
            label: 'Amount (Rs.)',
            data: [100000, 60000, 40000],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Sample Financial Data'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

console.log('Chart created successfully!');
</script>

<div style="max-width: 800px; margin: 30px auto; background: #f0f0f0; padding: 20px; border-radius: 8px;">
    <h4>Troubleshooting:</h4>
    <p><strong>If you see a chart above:</strong> ✅ Chart.js is working correctly!</p>
    <p><strong>If you don't see a chart:</strong></p>
    <ul>
        <li>Check browser console for errors (F12)</li>
        <li>Verify internet connection (Chart.js loads from CDN)</li>
        <li>Try refreshing the page</li>
        <li>Check if JavaScript is enabled</li>
    </ul>
    
    <p><strong>Next Steps:</strong></p>
    <ol>
        <li>If this test chart works, the issue is with data in your reports</li>
        <li>Make sure you have data in income and expenses tables</li>
        <li>Run: <code>database/reporting_seed.sql</code> to add sample data</li>
    </ol>
    
    <p><a href="finance_reports.php?report=profit_loss" class="btn btn-primary">Go to Profit & Loss Report</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
