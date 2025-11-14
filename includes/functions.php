<?php
/**
 * Core Utility Functions
 */

/**
 * Sanitize user input
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate required field
 * @param mixed $value Field value
 * @param string $fieldName Field name for error message
 * @return string|null Error message or null if valid
 */
function validateRequired($value, $fieldName) {
    if (empty($value) && $value !== '0') {
        return $fieldName . " is required.";
    }
    return null;
}

/**
 * Validate email format
 * @param string $email Email address
 * @return string|null Error message or null if valid
 */
function validateEmail($email) {
    if (empty($email)) {
        return "Email is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }
    return null;
}

/**
 * Validate date format (YYYY-MM-DD)
 * @param string $date Date string
 * @param string $fieldName Field name for error message
 * @return string|null Error message or null if valid
 */
function validateDate($date, $fieldName = "Date") {
    if (empty($date)) {
        return $fieldName . " is required.";
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        return $fieldName . " must be in YYYY-MM-DD format.";
    }
    return null;
}

/**
 * Validate numeric value
 * @param mixed $value Value to validate
 * @param string $fieldName Field name for error message
 * @return string|null Error message or null if valid
 */
function validateNumeric($value, $fieldName) {
    if (empty($value) && $value !== '0' && $value !== 0) {
        return $fieldName . " is required.";
    }
    if (!is_numeric($value)) {
        return $fieldName . " must be a number.";
    }
    return null;
}

/**
 * Validate positive number
 * @param mixed $value Value to validate
 * @param string $fieldName Field name for error message
 * @return string|null Error message or null if valid
 */
function validatePositive($value, $fieldName) {
    $numericError = validateNumeric($value, $fieldName);
    if ($numericError) {
        return $numericError;
    }
    if ($value < 0) {
        return $fieldName . " must be a positive number.";
    }
    return null;
}

/**
 * Set flash message in session
 * @param string $message Message text
 * @param string $type Message type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message from session
 * @return array|null Array with 'message' and 'type' keys, or null if no message
 */
function getFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $message;
    }
    
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = 'alert-' . $flash['type'];
        echo '<div class="alert ' . $alertClass . '" id="flash-message">';
        echo htmlspecialchars($flash['message']);
        echo '<span class="close-alert" onclick="this.parentElement.style.display=\'none\';">&times;</span>';
        echo '</div>';
    }
}

/**
 * Redirect to a page
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    if (empty($date)) return '';
    return date('M d, Y', strtotime($date));
}

/**
 * Get pagination data
 * @param int $totalRecords Total number of records
 * @param int $currentPage Current page number
 * @param int $recordsPerPage Records per page
 * @return array Pagination data
 */
function getPagination($totalRecords, $currentPage = 1, $recordsPerPage = 20) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'records_per_page' => $recordsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
