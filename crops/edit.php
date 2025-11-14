<?php
$pageTitle = 'Edit Crop - FarmSaathi';
$currentModule = 'crops';
require_once __DIR__ . '/../includes/header.php';

requirePermission('manager');

$conn = getDBConnection();
$errors = [];
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage("Invalid crop ID.", 'error');
    redirect('index.php');
}

// Get existing crop data
$stmt = $conn->prepare("SELECT * FROM crops WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    setFlashMessage("Crop not found.", 'error');
    redirect('index.php');
}

$crop = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $crop_name = sanitizeInput($_POST['crop_name'] ?? '');
    $crop_type = sanitizeInput($_POST['crop_type'] ?? '');
    $planting_date = sanitizeInput($_POST['planting_date'] ?? '');
    $expected_harvest = sanitizeInput($_POST['expected_harvest'] ?? '');
    $field_location = sanitizeInput($_POST['field_location'] ?? '');
    $area_hectares = sanitizeInput($_POST['area_hectares'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validate inputs
    if ($error = validateRequired($crop_name, 'Crop name')) $errors[] = $error;
    if ($error = validateRequired($crop_type, 'Crop type')) $errors[] = $error;
    if ($error = validateDate($planting_date, 'Planting date')) $errors[] = $error;
    if ($error = validateDate($expected_harvest, 'Expected harvest date')) $errors[] = $error;
    if ($error = validateRequired($field_location, 'Field location')) $errors[] = $error;
    if ($error = validatePositive($area_hectares, 'Area')) $errors[] = $error;
    
    // Update if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE crops 
            SET crop_name = ?, crop_type = ?, planting_date = ?, expected_harvest = ?, 
                field_location = ?, area_hectares = ?, status = ?, notes = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssssdssi", $crop_name, $crop_type, $planting_date, $expected_harvest, $field_location, $area_hectares, $status, $notes, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            setFlashMessage("Crop updated successfully!", 'success');
            redirect('index.php');
        } else {
            $errors[] = "Failed to update crop. Please try again.";
        }
        $stmt->close();
    }
} else {
    // Pre-fill form with existing data
    $crop_name = $crop['crop_name'];
    $crop_type = $crop['crop_type'];
    $planting_date = $crop['planting_date'];
    $expected_harvest = $crop['expected_harvest'];
    $field_location = $crop['field_location'];
    $area_hectares = $crop['area_hectares'];
    $status = $crop['status'];
    $notes = $crop['notes'];
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Crop</h2>
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

    <form method="POST" action="edit.php?id=<?php echo $id; ?>" class="data-form">
        <div class="form-row">
            <div class="form-group">
                <label for="crop_name">Crop Name *</label>
                <input 
                    type="text" 
                    id="crop_name" 
                    name="crop_name" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($crop_name); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="crop_type">Crop Type *</label>
                <input 
                    type="text" 
                    id="crop_type" 
                    name="crop_type" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($crop_type); ?>"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="planting_date">Planting Date *</label>
                <input 
                    type="date" 
                    id="planting_date" 
                    name="planting_date" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($planting_date); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="expected_harvest">Expected Harvest Date *</label>
                <input 
                    type="date" 
                    id="expected_harvest" 
                    name="expected_harvest" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($expected_harvest); ?>"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="field_location">Field Location *</label>
                <input 
                    type="text" 
                    id="field_location" 
                    name="field_location" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($field_location); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="area_hectares">Area (Hectares) *</label>
                <input 
                    type="number" 
                    id="area_hectares" 
                    name="area_hectares" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($area_hectares); ?>"
                    step="0.01"
                    min="0"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" class="form-control" required>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="harvested" <?php echo $status === 'harvested' ? 'selected' : ''; ?>>Harvested</option>
                <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea 
                id="notes" 
                name="notes" 
                class="form-control" 
                rows="4"
            ><?php echo htmlspecialchars($notes); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Crop</button>
            <a href="index.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
