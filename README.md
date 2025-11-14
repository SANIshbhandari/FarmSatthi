# Farm Management System

A comprehensive web-based farm management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system helps farm owners and managers efficiently track and manage all aspects of their farming operations.

## Features

- **Dashboard**: Real-time statistics and alerts for farm operations
- **Crops Management**: Track planting schedules, harvest dates, and crop status
- **Livestock Management**: Monitor animal health, counts, and breeding information
- **Equipment Management**: Track machinery maintenance schedules and condition
- **Employee Management**: Manage staff information, roles, and employment details
- **Expense Tracking**: Monitor farm spending and maintain financial records
- **Inventory Management**: Track stock levels with automatic reorder alerts
- **Reports & Analytics**: Generate comprehensive reports for all modules
- **User Authentication**: Secure login system with role-based access control
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx web server
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### 1. Clone or Download

Download the project files to your web server directory (e.g., `/var/www/html/` or `htdocs/`).

### 2. Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE farm_management;
```

2. Import the database schema:
```bash
mysql -u your_username -p farm_management < database/schema.sql
```

3. (Optional) Import sample data for testing:
```bash
mysql -u your_username -p farm_management < database/seed.sql
```

### 3. Configure Database Connection

Edit `config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'farm_management');
```

### 4. Set Permissions

Ensure the web server has read/write permissions:

```bash
chmod -R 755 /path/to/farm-management-system
chown -R www-data:www-data /path/to/farm-management-system
```

### 5. Access the System

1. Open your web browser and navigate to: `http://your-domain.com/`
2. You'll be redirected to the registration page to create your admin account
3. Fill in the registration form to create the first admin user
4. After registration, you'll be automatically logged in

## Default Structure

```
farm-management-system/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── auth/
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   └── session.php
├── config/
│   └── database.php
├── crops/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── livestock/
├── equipment/
├── employees/
├── expenses/
├── inventory/
├── reports/
├── dashboard/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── functions.php
│   └── csrf.php
├── .htaccess
├── index.php
└── README.md
```

## User Roles

The system supports three user roles:

1. **Admin**: Full access including user management
2. **Manager**: Full access to all modules (create, read, update, delete)
3. **Viewer**: Read-only access to all modules

## Usage Guide

### Adding Records

1. Navigate to any module (Crops, Livestock, etc.)
2. Click the "+ Add New" button
3. Fill in the required fields
4. Click "Add" to save

### Editing Records

1. Find the record in the list
2. Click the "Edit" button
3. Modify the fields as needed
4. Click "Update" to save changes

### Searching and Filtering

- Use the search box to find records by name or keywords
- Use filter dropdowns to narrow results by status, category, etc.
- Click "Clear" to reset filters

### Generating Reports

1. Go to the Reports module
2. Select a report type (Crops, Livestock, Equipment, etc.)
3. Apply date filters if needed
4. View the generated report
5. Use the Print button to print the report

### Managing Users (Admin Only)

1. Click the "+ User" button in the header
2. Fill in the user details
3. Select the appropriate role
4. Click "Register User"

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF token support
- Session security with timeout
- Role-based access control
- Secure HTTP headers via .htaccess

## Troubleshooting

### Database Connection Error

- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check that the database exists

### Permission Denied

- Check file permissions (755 for directories, 644 for files)
- Ensure web server user has access

### Page Not Found

- Verify .htaccess is enabled
- Check Apache mod_rewrite is enabled

### Session Issues

- Ensure PHP session directory is writable
- Check PHP session settings in php.ini

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Backup Recommendations

### Database Backup

Create regular backups of your database:

```bash
mysqldump -u your_username -p farm_management > backup_$(date +%Y%m%d).sql
```

### File Backup

Backup the entire application directory regularly.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review PHP error logs
3. Check browser console for JavaScript errors

## License

This project is provided as-is for educational and commercial use.

## Version

Version 1.0.0 - November 2024

## Credits

Developed as a comprehensive farm management solution for modern agricultural operations.
