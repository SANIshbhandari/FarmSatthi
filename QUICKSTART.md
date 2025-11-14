# Quick Start Guide - Farm Management System

Get up and running in 5 minutes!

## Prerequisites

- XAMPP, WAMP, MAMP, or similar (includes Apache, PHP, MySQL)
- Web browser

## Installation Steps

### 1. Extract Files
Extract the farm-management-system folder to:
- **Windows**: `C:\xampp\htdocs\`
- **Mac**: `/Applications/MAMP/htdocs/`
- **Linux**: `/var/www/html/`

### 2. Create Database
1. Start Apache and MySQL
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Click "New" to create database
4. Name it: `farm_management`
5. Click "Import" tab
6. Choose file: `database/schema.sql`
7. Click "Go"

### 3. Configure
1. Open `config/database.php`
2. Update if needed (default works for XAMPP):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for XAMPP
define('DB_NAME', 'farm_management');
```

### 4. Access System
1. Open browser
2. Go to: `http://localhost/farm-management-system/`
3. Create your admin account
4. Start using the system!

## Optional: Load Sample Data

To test with sample data:
1. In phpMyAdmin, select `farm_management` database
2. Click "Import"
3. Choose `database/seed.sql`
4. Click "Go"

Now you have sample crops, livestock, equipment, employees, expenses, and inventory!

## Default Login (if you loaded sample data)

After loading sample data, create your admin account through the registration page.

## Quick Tour

### Dashboard
- View statistics at a glance
- See alerts for low inventory and maintenance
- Quick action buttons

### Add Records
1. Click any module (Crops, Livestock, etc.)
2. Click "+ Add New" button
3. Fill in the form
4. Click "Add"

### Search & Filter
- Use search box to find records
- Use dropdown filters to narrow results
- Click "Clear" to reset

### Generate Reports
1. Go to Reports module
2. Select report type
3. View analytics and data

## Troubleshooting

**Can't connect to database?**
- Make sure MySQL is running
- Check credentials in `config/database.php`

**Page not found?**
- Verify files are in correct directory
- Check Apache is running

**Blank page?**
- Check PHP error log
- Ensure PHP 7.4+ is installed

## Need Help?

Check the full documentation:
- `README.md` - Complete feature list
- `INSTALL.md` - Detailed installation guide
- `PROJECT_SUMMARY.md` - Technical details

## Next Steps

1. ✅ Create admin account
2. ✅ Add your farm data
3. ✅ Create additional users (Admin → + User button)
4. ✅ Set up regular backups
5. ✅ Customize for your needs

---

**You're all set!** Start managing your farm operations efficiently.
