-- Add Admin User to FarmSaathi Database
-- This script creates an admin user with the following credentials:
-- Username: admin
-- Password: admin123
-- Email: admin@farmsaathi.com
-- Role: admin

USE farm_management;

-- Insert admin user
-- Password 'admin123' is hashed using PHP's password_hash() with PASSWORD_DEFAULT
INSERT INTO users (username, password, email, role, created_at) 
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin@farmsaathi.com',
    'admin',
    NOW()
);

-- Verify the admin user was created
SELECT id, username, email, role, created_at FROM users WHERE role = 'admin';
