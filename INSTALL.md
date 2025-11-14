# Installation Guide - Farm Management System

## Quick Start Guide

Follow these steps to get your Farm Management System up and running.

## Prerequisites

Before you begin, ensure you have:

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7+ or MariaDB 10.2+
- **PHP Extensions**: mysqli, session, json
- **Browser**: Modern web browser (Chrome, Firefox, Safari, Edge)

## Step-by-Step Installation

### Step 1: Download and Extract

1. Download the Farm Management System files
2. Extract to your web server directory:
   - **XAMPP/WAMP**: `C:\xampp\htdocs\farm-management-system\`
   - **Linux**: `/var/www/html/farm-management-system/`
   - **macOS**: `/Applications/MAMP/htdocs/farm-management-system/`

### Step 2: Create Database

1. Open phpMyAdmin or MySQL command line
2. Create a new database:

```sql
CREATE DATABASE farm_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import the database schema:

**Using phpMyAdmin:**
- Select the `farm_management` database
- Click "Import" tab
- Choose `database/schema.sql`
- Click "Go"

**Using Command Line:**
```bash
mysql -u root -p farm_management < database/schema.sql
```

### Step 3: Configure Database Connection

1. Open `config/database.php` in a text editor
2. Update the database credentials:

```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'farm_management'); // Database name
```

3. Save the file

### Step 4: Set File Permissions (Linux/macOS only)

```bash
cd /var/www/html/farm-management-system
chmod -R 755 .
chmod -R 777 assets/
chown -R www-data:www-data .
```

For XAMPP/WAMP on Windows, no permission changes are needed.

### Step 5: Access the System

1. Start your web server (Apache/Nginx) and MySQL
2. Open your web browser
3. Navigate to: `http://localhost/farm-management-system/`
4. You'll be redirected to the registration page

### Step 6: Create Admin Account

1. Fill in the registration form:
   - **Username**: Choose a username (e.g., admin)
   - **Email**: Your email address
   - **Password**: Strong password (minimum 6 characters)
   - **Confirm Password**: Re-enter password

2. Click "Create Admin Account"
3. You'll be automatically logged in

### Step 7: (Optional) Load Sample Data

To test the system with sample data:

**Using phpMyAdmin:**
- Select the `farm_management` database
- Click "Import" tab
- Choose `database/seed.sql`
- Click "Go"

**Using Command Line:**
```bash
mysql -u root -p farm_management < database/seed.sql
```

This will add:
- 10 sample crops
- 10 sample livestock records
- 10 sample equipment items
- 8 sample employees
- 15 sample expenses
- 20 sample inventory items

## Verification

After installation, verify everything works:

1. **Login**: You should be able to log in with your admin account
2. **Dashboard**: Should display statistics (will be 0 if no sample data)
3. **Navigation**: All menu items should be accessible
4. **Add Record**: Try adding a crop or inventory item
5. **Reports**: Generate a report to verify database queries work

## Troubleshooting

### "Database connection failed"

**Solution:**
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `farm_management` exists
- Test connection: `mysql -u root -p` and enter password

### "Permission denied" errors

**Solution (Linux/macOS):**
```bash
sudo chmod -R 755 /var/www/html/farm-management-system
sudo chown -R www-data:www-data /var/www/html/farm-management-system
```

### "Page not found" or blank pages

**Solution:**
- Check Apache mod_rewrite is enabled: `sudo a2enmod rewrite`
- Restart Apache: `sudo service apache2 restart`
- Verify .htaccess file exists in root directory

### PHP errors displayed

**Solution:**
- For production, edit php.ini:
  ```ini
  display_errors = Off
  log_errors = On
  error_log = /var/log/php_errors.log
  ```
- Restart web server

### Session errors

**Solution:**
- Check PHP session directory is writable:
  ```bash
  sudo chmod 777 /var/lib/php/sessions
  ```

## Configuration Options

### Change Database Prefix

If you need to use a table prefix, edit `database/schema.sql` before importing:

```sql
CREATE TABLE IF NOT EXISTS fms_users (
-- Add 'fms_' prefix to all table names
```

### Enable HTTPS (Recommended for Production)

1. Obtain SSL certificate
2. Edit `.htaccess`, uncomment HTTPS redirect:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Change Session Timeout

Edit `auth/session.php`, line ~45:
```php
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Change 1800 (30 minutes) to desired timeout in seconds
```

## Server Requirements Details

### Minimum Requirements
- **CPU**: 1 GHz processor
- **RAM**: 512 MB
- **Disk Space**: 100 MB
- **PHP Memory Limit**: 128 MB

### Recommended Requirements
- **CPU**: 2 GHz dual-core processor
- **RAM**: 2 GB
- **Disk Space**: 500 MB
- **PHP Memory Limit**: 256 MB

### PHP Extensions Required
- mysqli
- session
- json
- mbstring
- openssl (for password hashing)

Check installed extensions:
```bash
php -m
```

## Production Deployment Checklist

Before deploying to production:

- [ ] Change database password to strong password
- [ ] Enable HTTPS/SSL
- [ ] Set `display_errors = Off` in php.ini
- [ ] Set up regular database backups
- [ ] Configure firewall rules
- [ ] Update `.htaccess` security headers
- [ ] Test all functionality
- [ ] Create admin user with strong password
- [ ] Document admin credentials securely
- [ ] Set up monitoring/logging
- [ ] Configure email notifications (if needed)

## Backup Strategy

### Database Backup

Create automated daily backups:

```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p farm_management > /backups/farm_db_$DATE.sql
# Keep only last 30 days
find /backups -name "farm_db_*.sql" -mtime +30 -delete
```

Add to crontab:
```bash
0 2 * * * /path/to/backup.sh
```

### File Backup

Backup the entire application directory weekly.

## Updating

To update to a new version:

1. Backup database and files
2. Download new version
3. Replace files (keep config/database.php)
4. Run any update SQL scripts
5. Clear browser cache
6. Test functionality

## Support

For issues:
1. Check error logs: `/var/log/apache2/error.log` or `C:\xampp\apache\logs\error.log`
2. Enable PHP error display temporarily for debugging
3. Check browser console for JavaScript errors
4. Verify all files were uploaded correctly

## Next Steps

After installation:
1. Create additional user accounts (Admin only)
2. Customize categories for your farm
3. Add your farm's data
4. Set up regular backups
5. Train staff on system usage

## Security Notes

- Change default database password
- Use strong passwords for all accounts
- Keep PHP and MySQL updated
- Regular security audits
- Monitor access logs
- Implement rate limiting for login attempts

---

**Installation Complete!** You're ready to start managing your farm operations.
