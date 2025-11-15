<?php
$pageTitle = 'Report Data Entry - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';

requireLogin();
$conn = getDBConnection();
?>

<div class="module-header">
    <h2>📝 Report Data Entry</h2>
    <p>Quick forms to add data for reporting</p>
</div>

<div class="data-entry-sections">
    
    <!-- Crop Production Data -->
    <div class="entry-section">
        <h3>🌾 Crop Production Data</h3>
        <p>Record yield and production costs for crops</p>
        <a href="crop_production_entry.php" class="btn btn-primary">Add Production Data</a>
    </div>

    <!-- Crop Growth Monitoring -->
    <div class="entry-section">
        <h3>🌱 Crop Growth Monitoring</h3>
        <p>Track growth stages and inputs used</p>
        <a href="crop_growth_entry.php" class="btn btn-primary">Add Growth Record</a>
    </div>

    <!-- Crop Sales -->
    <div class="entry-section">
        <h3>💰 Crop Sales</h3>
        <p>Record crop sales transactions</p>
        <a href="../crops/record_sale.php" class="btn btn-primary">Record Crop Sale</a>
    </div>

    <!-- Livestock Health -->
    <div class="entry-section">
        <h3>🏥 Livestock Health Records</h3>
        <p>Track health checkups and vaccinations</p>
        <a href="livestock_health_entry.php" class="btn btn-primary">Add Health Record</a>
    </div>

    <!-- Livestock Production -->
    <div class="entry-section">
        <h3>🥛 Livestock Production</h3>
        <p>Record daily production (milk, eggs, meat)</p>
        <a href="livestock_production_entry.php" class="btn btn-primary">Add Production Data</a>
    </div>

    <!-- Livestock Sales -->
    <div class="entry-section">
        <h3>🐄 Livestock Sales</h3>
        <p>Record livestock sales transactions</p>
        <a href="livestock_sales_entry.php" class="btn btn-primary">Record Livestock Sale</a>
    </div>

    <!-- Income Entry -->
    <div class="entry-section">
        <h3>💵 Income Entry</h3>
        <p>Record miscellaneous income</p>
        <a href="income_entry.php" class="btn btn-primary">Add Income</a>
    </div>

    <!-- Inventory Transactions -->
    <div class="entry-section">
        <h3>📦 Inventory Transactions</h3>
        <p>Record stock in/out movements</p>
        <a href="inventory_transaction_entry.php" class="btn btn-primary">Add Transaction</a>
    </div>

</div>

<style>
.data-entry-sections {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.entry-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.entry-section h3 {
    margin-top: 0;
    color: #2c3e50;
}

.entry-section p {
    color: #666;
    margin-bottom: 15px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
