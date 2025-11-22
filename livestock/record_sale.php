<?php
$pageTitle = 'Record Livestock Sale - FarmSaathi';
$currentModule = 'livestock';
require_once __DIR__ . '/../includes/header.php';

requireLogin();
$conn = getDBConnection();
$errors = [];

$livestock_id = intval($_GET['livestock_id'] ?? 0);

// Get livestock details
if ($livestock_id > 0) {
    $isolationWhere = getDataIsolationWhere();
    $stmt = $conn->prepare("SELECT * FROM livestock WHERE id = ? AND $isolationWhere");
    $stmt->bind_param("i", $livestock_id);
    $stmt->execute();
    $livestock = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$livestock) {
        setFlashMessage("Livestock not found.", 'error');
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $livestock_id = intval($_POST['livestock_id']);
    $animal_type = sanitizeInput($_POST['animal_type']);
    $breed = sanitizeInput($_POST['breed']);
    $quantity = intval($_POST['quantity']);
    $selling_price = floatval($_POST['selling_price']);
    $purchase_cost = floatval($_POST['purchase_cost']);
    $buyer_name = sanitizeInput($_POST['buyer_name']);
    $buyer_contact = sanitizeInput($_POST['buyer_contact']);
    $sale_date = sanitizeInput($_POST['sale_date']);
    $notes = sanitizeInput($_POST['notes']);
    
    // Validation
    if (empty($animal_type)) $errors[] = "Animal type is required.";
    if ($quantity <= 0) $errors[] = "Quantity must be greater than 0.";
    if ($selling_price <= 0) $errors[] = "Selling price must be greater than 0.";
    if ($purchase_cost < 0) $errors[] = "Purchase cost cannot be negative.";
    if (empty($buyer_name)) $errors[] = "Buyer name is required.";
    if (empty($sale_date)) $errors[] = "Sale date is required.";
    
    if (empty($errors)) {
        $profit_loss = $selling_price - $purchase_cost;
        $createdBy = getCreatedByUserId();
        
        // Insert livestock sale
        $stmt = $conn->prepare("
            INSERT INTO livestock_sales 
            (created_by, livestock_id, animal_type, breed, quantity, selling_price, purchase_cost, profit_loss, buyer_name, buyer_contact, sale_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt === false) {
            $errors[] = "Database error: " . $conn->error;
            $errors[] = "The livestock_sales table may not exist. Please run database/create_sales_tables.sql";
        } else {
            $stmt->bind_param("iissdddsssss", $createdBy, $livestock_id, $animal_type, $breed, $quantity, $selling_price, $purchase_cost, $profit_loss, $buyer_name, $buyer_contact, $sale_date, $notes);
        }
        
        if ($stmt && $stmt->execute()) {
            $sale_id = $stmt->insert_id;
            $stmt->close();
            
            // Record income
            $stmt = $conn->prepare("INSERT INTO income (created_by, source, reference_id, amount, income_date, description) VALUES (?, 'livestock_sales', ?, ?, ?, ?)");
            if ($stmt) {
                $description = "Sale of $quantity $animal_type ($breed) to $buyer_name";
                $stmt->bind_param("iidss", $createdBy, $sale_id, $selling_price, $sale_date, $description);
                $stmt->execute();
                $stmt->close();
            }
            
            logActivity('create', 'livestock', "Recorded sale of $quantity $animal_type");
            setFlashMessage("Livestock sale recorded successfully!", 'success');
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
        <h2>Record Livestock Sale</h2>
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

    <form method="POST" action="record_sale.php" class="data-form">
        <input type="hidden" name="livestock_id" value="<?php echo $livestock_id; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label for="animal_type">Animal Type *</label>
                <input type="text" id="animal_type" name="animal_type" class="form-control" 
                    value="<?php echo htmlspecialchars($livestock['animal_type'] ?? $_POST['animal_type'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="breed">Breed *</label>
                <input type="text" id="breed" name="breed" class="form-control" 
                    value="<?php echo htmlspecialchars($livestock['breed'] ?? $_POST['breed'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantity">Quantity (Number of Animals) *</label>
                <input type="number" id="quantity" name="quantity" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['quantity'] ?? ''); ?>" min="1" required>
                <small style="color: #666;">Available: <?php echo $livestock['count'] ?? 0; ?> animals</small>
            </div>
            
            <div class="form-group">
                <label for="sale_date">Sale Date *</label>
                <input type="date" id="sale_date" name="sale_date" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['sale_date'] ?? date('Y-m-d')); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="purchase_cost">Purchase Cost (Total) *</label>
                <input type="number" step="0.01" id="purchase_cost" name="purchase_cost" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['purchase_cost'] ?? ''); ?>" required>
                <small style="color: #666;">What you paid for these animals</small>
            </div>
            
            <div class="form-group">
                <label for="selling_price">Selling Price (Total) *</label>
                <input type="number" step="0.01" id="selling_price" name="selling_price" class="form-control" 
                    value="<?php echo htmlspecialchars($_POST['selling_price'] ?? ''); ?>" required>
                <small style="color: #666;">What you're selling them for</small>
            </div>
        </div>

        <div class="form-group">
            <label>Profit/Loss</label>
            <input type="text" id="profit_loss_display" class="form-control" readonly value="Rs. 0.00" 
                style="font-weight: bold; font-size: 1.1em;">
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
                    value="<?php echo htmlspecialchars($_POST['buyer_contact'] ?? ''); ?>" 
                    placeholder="Phone or email">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="3" 
                placeholder="Additional notes about this sale..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Record Sale</button>
            <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<script>
function calculateProfitLoss() {
    const purchaseCost = parseFloat(document.getElementById('purchase_cost').value) || 0;
    const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
    const profitLoss = sellingPrice - purchaseCost;
    
    const displayElement = document.getElementById('profit_loss_display');
    displayElement.value = 'Rs. ' + profitLoss.toFixed(2);
    
    // Change color based on profit or loss
    if (profitLoss > 0) {
        displayElement.style.color = '#28a745'; // Green for profit
    } else if (profitLoss < 0) {
        displayElement.style.color = '#dc3545'; // Red for loss
    } else {
        displayElement.style.color = '#666'; // Gray for break-even
    }
}

document.getElementById('purchase_cost').addEventListener('input', calculateProfitLoss);
document.getElementById('selling_price').addEventListener('input', calculateProfitLoss);

// Calculate on page load if values exist
calculateProfitLoss();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
