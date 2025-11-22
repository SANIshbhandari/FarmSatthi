<?php
$pageTitle = 'Edit Expense - FarmSaathi';
$currentModule = 'expenses';
require_once __DIR__ . '/../includes/header.php';

requirePermission('manager');

$conn = getDBConnection();
$errors = [];
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage("Invalid expense ID.", 'error');
    redirect('index.php');
}

// Verify record ownership
verifyRecordOwnership($conn, 'expenses', $id, 'index.php');

$stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    setFlashMessage("Expense not found.", 'error');
    redirect('index.php');
}

$expense = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitizeInput($_POST['category'] ?? '');
    $amount = sanitizeInput($_POST['amount'] ?? '');
    $expense_date = sanitizeInput($_POST['expense_date'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? 'cash');
    
    if ($error = validateRequired($category, 'Category')) $errors[] = $error;
    if ($error = validatePositive($amount, 'Amount')) $errors[] = $error;
    if ($error = validateDate($expense_date, 'Expense date')) $errors[] = $error;
    if ($error = validateRequired($description, 'Description')) $errors[] = $error;
    
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE expenses 
            SET category = ?, amount = ?, expense_date = ?, description = ?, payment_method = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sdsssi", $category, $amount, $expense_date, $description, $payment_method, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            setFlashMessage("Expense updated successfully!", 'success');
            redirect('index.php');
        } else {
            $errors[] = "Failed to update expense. Please try again.";
        }
        $stmt->close();
    }
} else {
    $category = $expense['category'];
    $amount = $expense['amount'];
    $expense_date = $expense['expense_date'];
    $description = $expense['description'];
    $payment_method = $expense['payment_method'];
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Expense</h2>
        <a href="index.php" class="btn btn-outline">← Back to Expenses</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?php echo $id; ?>" class="data-form">
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category *</label>
                <input 
                    type="text" 
                    id="category" 
                    name="category" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($category); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="amount">Amount ($) *</label>
                <input 
                    type="number" 
                    id="amount" 
                    name="amount" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($amount); ?>"
                    step="0.01"
                    min="0"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="expense_date">Expense Date *</label>
                <input 
                    type="date" 
                    id="expense_date" 
                    name="expense_date" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($expense_date); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method *</label>
                <select id="payment_method" name="payment_method" class="form-control" required>
                    <option value="cash" <?php echo $payment_method === 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="check" <?php echo $payment_method === 'check' ? 'selected' : ''; ?>>Check</option>
                    <option value="bank_transfer" <?php echo $payment_method === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="credit_card" <?php echo $payment_method === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-control" 
                rows="4"
                required
            ><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Expense</button>
            <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
