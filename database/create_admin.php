<?php
/**
 * Create Admin User Script
 * Run this file once to create an admin user in the database
 * Access: http://localhost/Farmwebsite/database/create_admin.php
 */

require_once __DIR__ . '/../config/database.php';

// Admin user details
$username = 'admin';
$password = 'admin123';
$email = 'admin@farmsaathi.com';
$role = 'admin';

try {
    $conn = getDBConnection();
    
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<h2>❌ Admin user already exists!</h2>";
        echo "<p>Username: <strong>$username</strong></p>";
        echo "<p>An admin user with this username or email already exists in the database.</p>";
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert admin user
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashedPassword, $email, $role);
    
    if ($stmt->execute()) {
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Admin Created - FarmSaathi</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
            .success-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h2 { color: #2d7a3e; }
            .credentials { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
            .credentials p { margin: 10px 0; }
            .btn { display: inline-block; padding: 10px 20px; background: #2d7a3e; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
            .btn:hover { background: #246330; }
            .warning { background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #ffc107; }
        </style></head><body>";
        echo "<div class='success-box'>";
        echo "<h2>✅ Admin User Created Successfully!</h2>";
        echo "<div class='credentials'>";
        echo "<p><strong>Username:</strong> $username</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Role:</strong> $role</p>";
        echo "</div>";
        echo "<div class='warning'>";
        echo "<strong>⚠️ Important Security Note:</strong><br>";
        echo "Please change the admin password after your first login!<br>";
        echo "Delete this file (create_admin.php) after creating the admin user.";
        echo "</div>";
        echo "<a href='../auth/login.php' class='btn'>Go to Login Page</a>";
        echo "</div></body></html>";
        $stmt->close();
    } else {
        echo "<h2>❌ Error creating admin user</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
        $stmt->close();
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Database Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
