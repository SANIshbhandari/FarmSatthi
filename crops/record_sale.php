<?php
$pageTitle = 'Record Crop Sale - FarmSaathi';
$currentModule = 'crops';
require_once __DIR__ . '/../includes/header.php';

requireLogin();
$conn = getDBConnection();
$errors = [];

$crop_id = intval($_GET['crop_id'] ?? 0);

// Get crop details
if ($crop_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM crops WHERE id = ?");
    $stmt->bind_param("i", $crop_id);
    $stmt->execute();
    $crop = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$crop) {
        setFlashMessage("Crop not found.", 'error');
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_id = intval($_POST['crop_id']);
    $crop_name = sanitizeInput($_POST['crop_name']);
    $quantity_sold = floatval($_POST['quantity_sold']);
    $unit = sanitizeInput($_POST['unit']);
    $rate_per_unit = floatval($_POST['rate_per_unit']);
    $buyer_name = sanitizeInput($_POST['buyer_name']);
    $buyer_contact = sanitizeInput($_POST['buyer_contact']);
    $sale_date = sanitizeInput($_POST['sale_date']);
    
    // Validation
    if (empty($crop_name)) $errors[] = "Crop name is required.";
    if ($quantity_sold <= 0) $errors[] = "Quantity must be greater than 0.";
    if ($rate_per_unit <= 0) $errors[] = "Rate per unit must be greater than 0.";
    if (empty($buyer_name)) $errors[] = "Buyer name is required.";
    if (empty($sale_date)) $errors[] = "Sale date is required.";
    
    if (empty($errors)) {
        $total_price = $quantity_sold * $rate_per_unit;
        
        // Insert crop sale
        $stmt = $conn->prepare("INSERT INTO crop_sales (crop_id, crop_name, quantity_sold, unit, rate_per_unit, total_price, buyer_name, buyer_contact, sale_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            $errors[] = "Database error: " . $conn->error;
            $errors[] = "The crop_sales table may not exist. Please run database/create_sales_tables.sql";
        } else {
            $stmt->bind_param("isdsddsss", $crop_id, $crop_name, $quantity_sold, $unit, $rate_per_unit, $total_price, $buyer_name, $buyer_contact, $sale_date);
        }
        
        if ($stmt && $stmt->execute()) {
            $sale_id = $stmt->insert_id;
            $stmt->close();
            
            // Record income
            $stmt = $conn->prepare("INSERT INTO income (source, reference_id, amount, income_date, description) VALUES ('crop_sales', ?, ?, ?, ?)");
            if ($stmt) {
                $description = "Sale of $crop_name to $buyer_name";
                $stmt->bind_param("idss", $sale_id, $total_price, $sale_date, $description);
                $stmt->execute();
                $stmt->close();
            }
            
            logActivity('create', 'crops', "Recorded sale of $crop_name");
            setFlashMessage("Crop sale recorded successfully!", 'success');
            redirect('index.php');
        } else {
            if ($stmt) {
                $errors[] = "Failed to record sale: " . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Record Crop Sale</h2>
        <a href="index.php" class="btn btn-outline">← Back to Crops</a>
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

    <form method="POST" action="record_sale.php" class="data-form">
        <input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label for="crop_name">Crop Name *</label>
                <input type="text" id="crop_name" name="crop_name" class="form-control" 
                    value="<?php echo htmlspecialchars($crop['crop_name'] ?? $_POST['crop_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="sale_date">Sale Date *</label>
                <input type="date" id="sale_date" name="sale_date" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['sale_date'] ?? date('Y-m-d')); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantity_sold">Quantity Sold *</label>
                <input type="number" step="0.01" id="quantity_sold" name="quantity_sold" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['quantity_sold'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="unit">Unit *</label>
                <select id="unit" name="unit" class="form-control" required>
                    <option value="kg" <?php echo ($_POST['unit'] ?? 'kg') === 'kg' ? 'selected' : ''; ?>>Kilograms (kg)</option>
                    <option value="tons" <?php echo ($_POST['unit'] ?? '') === 'tons' ? 'selected' : ''; ?>>Tons</option>
                    <option value="quintals" <?php echo ($_POST['unit'] ?? '') === 'quintals' ? 'selected' : ''; ?>>Quintals</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="rate_per_unit">Rate per Unit (₹) *</label>
                <input type="number" step="0.01" id="rate_per_unit" name="rate_per_unit" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['rate_per_unit'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Total Price</label>
                <input type="text" id="total_price_display" class="form-control" readonly value="₹ 0.00">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buyer_name">Buyer Name *</label>
                <input type="text" id="buyer_name" name="buyer_name" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['buyer_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="buyer_contact">Buyer Contact</label>
                <input type="text" id="buyer_contact" name="buyer_contact" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['buyer_contact'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Record Sale</button>
            <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<script>
function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity_sold').value) || 0;
    const rate = parseFloat(document.getElementById('rate_per_unit').value) || 0;
    const total = quantity * rate;
    document.getElementById('total_price_display').value = '₹ ' + total.toFixed(2);
}

document.getElementById('quantity_sold').addEventListener('input', calculateTotal);
document.getElementById('rate_per_unit').addEventListener('input', calculateTotal);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
