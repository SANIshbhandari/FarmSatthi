<?php
$pageTitle = 'Add Livestock - FarmSaathi';
$currentModule = 'livestock';
require_once __DIR__ . '/../includes/header.php';

requirePermission('manager');

$conn = getDBConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_type = sanitizeInput($_POST['animal_type'] ?? '');
    $breed = sanitizeInput($_POST['breed'] ?? '');
    $count = sanitizeInput($_POST['count'] ?? '');
    $age_months = sanitizeInput($_POST['age_months'] ?? '');
    $health_status = sanitizeInput($_POST['health_status'] ?? 'healthy');
    $purchase_date = sanitizeInput($_POST['purchase_date'] ?? '');
    $current_value = sanitizeInput($_POST['current_value'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    if ($error = validateRequired($animal_type, 'Animal type')) $errors[] = $error;
    if ($error = validateRequired($breed, 'Breed')) $errors[] = $error;
    if ($error = validatePositive($count, 'Count')) $errors[] = $error;
    if ($error = validatePositive($age_months, 'Age')) $errors[] = $error;
    if ($error = validateDate($purchase_date, 'Purchase date')) $errors[] = $error;
    if ($error = validatePositive($current_value, 'Current value')) $errors[] = $error;
    
    if (empty($errors)) {
        $createdBy = getCreatedByUserId();
        $stmt = $conn->prepare("
            INSERT INTO livestock (created_by, animal_type, breed, count, age_months, health_status, purchase_date, current_value, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issiissds", $createdBy, $animal_type, $breed, $count, $age_months, $health_status, $purchase_date, $current_value, $notes);
        
        if ($stmt->execute()) {
            $stmt->close();
            setFlashMessage("Livestock added successfully!", 'success');
            redirect('index.php');
        } else {
            $errors[] = "Failed to add livestock. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Add New Livestock</h2>
        <a href="index.php" class="btn btn-outline">← Back to Livestock</a>
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

    <form method="POST" action="add.php" class="data-form">
        <div class="form-row">
            <div class="form-group">
                <label for="animal_type">Animal Type *</label>
                <input 
                    type="text" 
                    id="animal_type" 
                    name="animal_type" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($animal_type ?? ''); ?>"
                    placeholder="e.g., Cattle, Sheep, Goat"
                    required
                >
            </div>

            <div class="form-group">
                <label for="breed">Breed *</label>
                <input 
                    type="text" 
                    id="breed" 
                    name="breed" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($breed ?? ''); ?>"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="count">Count *</label>
                <input 
                    type="number" 
                    id="count" 
                    name="count" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($count ?? ''); ?>"
                    min="1"
                    required
                >
            </div>

            <div class="form-group">
                <label for="age_months">Age (Months) *</label>
                <input 
                    type="number" 
                    id="age_months" 
                    name="age_months" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($age_months ?? ''); ?>"
                    min="0"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="health_status">Health Status *</label>
                <select id="health_status" name="health_status" class="form-control" required>
                    <option value="healthy" <?php echo ($health_status ?? 'healthy') === 'healthy' ? 'selected' : ''; ?>>Healthy</option>
                    <option value="sick" <?php echo ($health_status ?? '') === 'sick' ? 'selected' : ''; ?>>Sick</option>
                    <option value="under_treatment" <?php echo ($health_status ?? '') === 'under_treatment' ? 'selected' : ''; ?>>Under Treatment</option>
                    <option value="quarantine" <?php echo ($health_status ?? '') === 'quarantine' ? 'selected' : ''; ?>>Quarantine</option>
                </select>
            </div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date *</label>
                <input 
                    type="date" 
                    id="purchase_date" 
                    name="purchase_date" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($purchase_date ?? ''); ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="current_value">Current Value ($) *</label>
            <input 
                type="number" 
                id="current_value" 
                name="current_value" 
                class="form-control" 
                value="<?php echo htmlspecialchars($current_value ?? ''); ?>"
                step="0.01"
                min="0"
                required
            >
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea 
                id="notes" 
                name="notes" 
                class="form-control" 
                rows="4"
            ><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Livestock</button>
            <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
