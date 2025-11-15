# FarmSaathi - Complete System Documentation
## From Zero to Hero - Understanding Every Part

---

## 📚 Table of Contents

1. [System Overview](#system-overview)
2. [Directory Structure](#directory-structure)
3. [Core Files Explained](#core-files-explained)
4. [Configuration Files](#configuration-files)
5. [Authentication System](#authentication-system)
6. [Module Details](#module-details)
7. [Database Structure](#database-structure)
8. [Reporting System](#reporting-system)
9. [Helper Functions](#helper-functions)
10. [Security Features](#security-features)

---

## 1. System Overview

**FarmSaathi** is a complete Farm Management System built with PHP and MySQL.

### What Does It Do?
- Manages farm operations (crops, livestock, equipment)
- Tracks employees and expenses
- Manages inventory
- Generates comprehensive reports
- User authentication and authorization
- Activity logging

### Technology Stack
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** Apache with mod_rewrite

### User Roles
1. **Admin** - Full access, user management
2. **Manager** - Farm operations, no user management

---

## 2. Directory Structure

```
FarmSaathi/
├── .htaccess                 # Apache configuration & security
├── index.php                 # Main entry point
├── 404.php                   # Page not found handler
├── error.php                 # Error display page
│
├── config/                   # Configuration files
│   ├── config.php           # App settings & helper functions
│   └── database.php         # Database connection
│
├── includes/                 # Shared components
│   ├── header.php           # Page header & navigation
│   ├── footer.php           # Page footer
│   ├── functions.php        # Utility functions
│   └── nepali_date.php      # Nepali date converter
│
├── auth/                     # Authentication system
│   ├── login.php            # User login
│   ├── signup.php           # User registration
│   ├── logout.php           # User logout
│   └── session.php          # Session management
│
├── dashboard/                # Dashboard module
│   └── index.php            # Main dashboard
│
├── admin/                    # Admin-only features
│   ├── users/               # User management
│   └── activity/            # Activity logs
│
├── crops/                    # Crop management
│   ├── index.php            # List crops
│   ├── add.php              # Add new crop
│   ├── edit.php             # Edit crop
│   ├── delete.php           # Delete crop
│   └── record_sale.php      # Record crop sale
│
├── livestock/                # Livestock management
│   ├── index.php            # List livestock
│   ├── add.php              # Add livestock
│   ├── edit.php             # Edit livestock
│   └── delete.php           # Delete livestock
│
├── equipment/                # Equipment management
│   ├── index.php            # List equipment
│   ├── add.php              # Add equipment
│   ├── edit.php             # Edit equipment
│   └── delete.php           # Delete equipment
│
├── employees/                # Employee management
│   ├── index.php            # List employees
│   ├── add.php              # Add employee
│   ├── edit.php             # Edit employee
│   └── delete.php           # Delete employee
│
├── expenses/                 # Expense tracking
│   ├── index.php            # List expenses
│   ├── add.php              # Add expense
│   ├── edit.php             # Edit expense
│   └── delete.php           # Delete expense
│
├── inventory/                # Inventory management
│   ├── index.php            # List inventory
│   ├── add.php              # Add item
│   ├── edit.php             # Edit item
│   └── delete.php           # Delete item
│
├── reports/                  # Reporting system
│   ├── index.php            # Reports homepage
│   ├── lib/                 # Report libraries
│   ├── crop_reports.php     # Crop reports
│   ├── livestock_reports.php # Livestock reports
│   ├── finance_reports.php  # Finance reports
│   ├── data_entry.php       # Data entry hub
│   └── income_entry.php     # Income entry
│
├── database/                 # Database files
│   ├── schema.sql           # Main database schema
│   ├── seed.sql             # Sample data
│   ├── reporting_schema.sql # Reporting tables
│   └── reporting_seed.sql   # Reporting sample data
│
└── assets/                   # Static files
    ├── css/                 # Stylesheets
    ├── js/                  # JavaScript files
    └── images/              # Images & logos
```

---

See additional documentation files for detailed explanations:
- `CORE_FILES_EXPLAINED.md` - Entry points and core files
- `MODULE_DOCUMENTATION.md` - Each module explained
- `DATABASE_DOCUMENTATION.md` - Database structure
- `CODE_FLOW_EXPLAINED.md` - How code flows through the system
