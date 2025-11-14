<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/session.php';

requirePermission('manager');

$conn = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage("Invalid livestock ID.", 'error');
    redirect('index.php');
}

$stmt = $conn->prepare("DELETE FROM livestock WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        setFlashMessage("Livestock deleted successfully!", 'success');
    } else {
        setFlashMessage("Livestock not found.", 'error');
    }
} else {
    setFlashMessage("Failed to delete livestock. Please try again.", 'error');
}

$stmt->close();
redirect('index.php');
?>
