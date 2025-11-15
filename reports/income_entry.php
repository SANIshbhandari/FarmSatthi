<?php
$pageTitle = 'Add Income - FarmSaathi';
$currentModule = 'reports';
require_once __DIR__ . '/../includes/header.php';

requireLogin();
$conn = getDBConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = sanitizeInput($_POST['source']);
    $amount = floatval($_POST['amount']);
    $income_date = sanitizeInput($_POST['income_date']);
    $description = sanitizeInput($_POST['description']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    
    if (empty($source)) $errors[] = "Source is required.";
    if ($amount <= 0) $errors[] = "Amount must be greater than 0.";
    if (empty($income_date)) $errors[] = "Date is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO income (source, amount, income_date, description, payment_method) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsss", $source, $amount, $income_date, $description, $payment_method);
        
        if ($stmt->execute()) {
            $stmt->close();
            logActivity('create', 'income', "Added income: $description");
            setFlashMessage("Income recorded successfully!", 'success');
            redirect('data_entry.php');
        } else {
            $errors[] = "Failed to record income.";
            $stmt->close();
        }
    }
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Add Income</h2>
        <a href="data_entry.php" class="btn btn-outline">← Back</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" class="data-form">
        <div class="form-row">
            <div class="form-group">
                <label for="source">Income Source *</label>
                <select id="source" name="source" class="form-control" required>
                    <option value="miscellaneous">Miscellaneous</option>
                    <option value="crop_sales">Crop Sales</option>
                    <option value="livestock_sales">Livestock Sales</option>
                </select>
            </div>
            <div class="form-group">
                <label for="income_date">Date *</label>
                <input type="date" id="income_date" name="income_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="amount">Amount (₹) *</label>
                <input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" class="form-control">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="check">Check</option>
                    <option value="online">Online</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Income</button>
            <a href="data_entry.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
