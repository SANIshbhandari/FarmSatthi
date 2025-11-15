<?php
/**
 * Farm Management System - Entry Point
 * Redirects users to appropriate page based on authentication status
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/auth/session.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard
    redirect('dashboard/index.php');
} else {
    // Check if any users exist
    try {
        $conn = getDBConnection();
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $userCount = $result->fetch_assoc()['count'];
        
        if ($userCount == 0) {
            // No users exist, redirect to signup
            redirect('auth/signup.php');
        } else {
            // Users exist, redirect to login
            redirect('auth/login.php');
        }
    } catch (Exception $e) {
        // Database error, show setup page
        redirect('check_setup.php');
    }
}
?>
