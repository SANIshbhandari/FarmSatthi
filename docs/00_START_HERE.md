# 🌾 FarmSaathi - Complete System Documentation

## Welcome to FarmSaathi!

This is your **complete guide** to understanding every part of your Farm Management System. Start here to learn everything from scratch.

---

## 📚 Documentation Structure

Read these documents in order:

### 1. **START_HERE.md** (This File)
   - Overview of the entire system
   - What each folder does
   - How to navigate the documentation

### 2. **SYSTEM_OVERVIEW.md**
   - What FarmSaathi does
   - Main features
   - User roles and permissions
   - System architecture

### 3. **CODE_FLOW_EXPLAINED.md**
   - How the code works step-by-step
   - Request flow (what happens when you click something)
   - Authentication flow
   - Data flow

### 4. **CORE_FILES_EXPLAINED.md**
   - Every important file explained
   - What each file does
   - How files work together

### 5. **DATABASE_DOCUMENTATION.md**
   - Database structure
   - All tables explained
   - Relationships between tables
   - Sample queries

### 6. **MODULE_BY_MODULE_GUIDE.md**
   - Each module explained in detail
   - How each feature works
   - Code examples

---

## 🗂️ System Folder Structure

```
FarmSaathi/
│
├── 📁 auth/                    # Login, Signup, Session Management
│   ├── login.php              # User login page
│   ├── signup.php             # User registration page
│   ├── logout.php             # Logout functionality
│   └── session.php            # Session management functions
│
├── 📁 config/                  # Configuration Files
│   ├── config.php             # General settings (URLs, paths)
│   └── database.php           # Database connection settings
│
├── 📁 includes/                # Shared/Common Files
│   ├── header.php             # Top navigation, logo, menu
│   ├── footer.php             # Bottom footer
│   ├── functions.php          # Utility functions (currency, dates)
│   └── nepali_date.php        # Nepali date converter
│
├── 📁 dashboard/               # Dashboard Module
│   └── index.php              # Main dashboard page
│
├── 📁 crops/                   # Crop Management Module
│   ├── index.php              # List all crops
│   ├── add.php                # Add new crop
│   ├── edit.php               # Edit existing crop
│   ├── delete.php             # Delete crop
│   └── record_sale.php        # Record crop sale
│
├── 📁 livestock/               # Livestock Management Module
│   ├── index.php              # List all livestock
│   ├── add.php                # Add new livestock
│   ├── edit.php               # Edit existing livestock
│   └── delete.php             # Delete livestock
│
├── 📁 equipment/               # Equipment Management Module
│   ├── index.php              # List all equipment
│   ├── add.php                # Add new equipment
│   ├── edit.php               # Edit existing equipment
│   └── delete.php             # Delete equipment
│
├── 📁 employees/               # Employee Management Module
│   ├── index.php              # List all employees
│   ├── add.php                # Add new employee
│   ├── edit.php               # Edit existing employee
│   └── delete.php             # Delete employee
│
├── 📁 expenses/                # Expense Management Module
│   ├── index.php              # List all expenses
│   ├── add.php                # Add new expense
│   ├── edit.php               # Edit existing expense
│   └── delete.php             # Delete expense
│
├── 📁 inventory/               # Inventory Management Module
│   ├── index.php              # List all inventory items
│   ├── add.php                # Add new inventory item
│   ├── edit.php               # Edit existing item
│   └── delete.php             # Delete item
│
├── 📁 reports/                 # Reporting Module
│   ├── index.php              # Reports homepage
│   ├── crop_reports.php       # Crop reports navigation
│   ├── livestock_reports.php  # Livestock reports navigation
│   ├── finance_reports.php    # Finance reports navigation
│   ├── data_entry.php         # Quick data entry hub
│   └── lib/                   # Report library files
│       ├── report_generator.php
│       ├── report_filter.php
│       └── report_data.php
│
├── 📁 admin/                   # Admin Module
│   ├── users/                 # User management
│   │   ├── index.php          # List all users
│   │   ├── add.php            # Add new user
│   │   ├── edit.php           # Edit user
│   │   └── delete.php         # Delete user
│   └── activity/              # Activity logs
│       └── index.php          # View activity logs
│
├── 📁 assets/                  # Static Files
│   ├── css/                   # Stylesheets
│   │   └── style.css          # Main stylesheet
│   ├── js/                    # JavaScript files
│   │   └── main.js            # Main JavaScript
│   └── images/                # Images
│       └── logo.jpg           # Farm logo
│
├── 📁 database/                # Database Files
│   ├── schema.sql             # Main database structure
│   ├── seed.sql               # Sample data
│   ├── reporting_schema.sql   # Reporting tables
│   ├── reporting_seed.sql     # Reporting sample data
│   └── add_activity_log.sql   # Activity logging table
│
├── 📁 docs/                    # Documentation (This folder!)
│   ├── 00_START_HERE.md
│   ├── 01_SYSTEM_OVERVIEW.md
│   ├── 02_CODE_FLOW_EXPLAINED.md
│   ├── 03_CORE_FILES_EXPLAINED.md
│   ├── 04_DATABASE_DOCUMENTATION.md
│   └── 05_MODULE_BY_MODULE_GUIDE.md
│
├── 📄 index.php                # Main entry point
├── 📄 404.php                  # Error page
├── 📄 error.php                # Error handler
├── 📄 .htaccess                # Server configuration
└── 📄 README.md                # Quick readme

```

---

## 🎯 What Each Folder Does (Simple Explanation)

### 1. **auth/** - Who Can Use the System
- Handles login and signup
- Checks if you're logged in
- Manages your session (remembers you're logged in)

### 2. **config/** - System Settings
- Database connection details
- Website URL settings
- File paths

### 3. **includes/** - Shared Code
- Header (top menu that appears on every page)
- Footer (bottom section on every page)
- Common functions used everywhere
- Nepali date converter

### 4. **dashboard/** - Home Page After Login
- Shows summary of your farm
- Quick stats and alerts

### 5. **crops/** - Manage Your Crops
- Add new crops
- View all crops
- Edit crop details
- Delete crops
- Record crop sales

### 6. **livestock/** - Manage Your Animals
- Add new livestock
- View all animals
- Edit animal details
- Delete livestock records

### 7. **equipment/** - Manage Farm Equipment
- Add new equipment
- Track maintenance
- View all equipment
- Edit/delete equipment

### 8. **employees/** - Manage Farm Workers
- Add new employees
- Track salaries
- View all employees
- Edit/delete employee records

### 9. **expenses/** - Track Farm Expenses
- Record expenses
- Categorize spending
- View expense history
- Edit/delete expenses

### 10. **inventory/** - Track Farm Supplies
- Manage seeds, fertilizers, medicines
- Track stock levels
- Get low stock alerts
- Record stock in/out

### 11. **reports/** - View Farm Analytics
- Crop reports (production, growth, sales)
- Livestock reports (health, production, sales)
- Finance reports (income, expenses, profit/loss)
- Export to CSV

### 12. **admin/** - System Administration
- Manage users (add/edit/delete users)
- View activity logs
- User permissions

### 13. **assets/** - Design Files
- CSS (styling/colors/layout)
- JavaScript (interactive features)
- Images (logo, icons)

### 14. **database/** - Database Structure
- SQL files to create tables
- Sample data for testing

---

## 🔄 How the System Works (Simple Flow)

### When You Visit the Website:

1. **You go to:** `localhost/Farmwebsite`
2. **System checks:** Are you logged in?
   - ✅ **Yes** → Takes you to Dashboard
   - ❌ **No** → Takes you to Login page

### When You Login:

1. **You enter:** Username and password
2. **System checks:** Is this correct?
   - ✅ **Yes** → Creates session, takes you to Dashboard
   - ❌ **No** → Shows error message

### When You Add a Crop:

1. **You click:** "Add New Crop" button
2. **System shows:** Form to fill in crop details
3. **You fill:** Crop name, type, planting date, etc.
4. **You click:** "Save" button
5. **System:**
   - Validates your input
   - Saves to database
   - Shows success message
   - Takes you back to crops list

### When You View a Report:

1. **You click:** "Reports" menu
2. **You select:** Type of report (Crop/Livestock/Finance)
3. **You choose:** Specific report
4. **You set:** Filters (date range, category)
5. **System:**
   - Fetches data from database
   - Converts dates to Nepali format
   - Formats currency as Rs.
   - Shows report with charts/tables
   - Allows CSV export

---

## 🔐 User Roles Explained

### 1. **Admin**
- Can do everything
- Manage users
- View all modules
- Access admin panel

### 2. **Manager**
- Manage farm operations
- Add/edit/delete crops, livestock, equipment
- Record expenses
- View reports
- Cannot manage users

---

## 💾 Database Basics

### Main Tables:

1. **users** - Who can login
2. **crops** - All crop records
3. **livestock** - All animal records
4. **equipment** - All equipment records
5. **employees** - All employee records
6. **expenses** - All expense records
7. **inventory** - All stock items
8. **activity_log** - Who did what and when

### Reporting Tables:

9. **crop_sales** - Crop sales transactions
10. **crop_production** - Yield and costs
11. **crop_growth_monitoring** - Growth tracking
12. **livestock_health** - Health records
13. **livestock_production** - Production data
14. **livestock_sales** - Livestock sales
15. **income** - All income sources
16. **inventory_transactions** - Stock movements

---

## 🚀 Quick Start Guide

### For Developers:

1. **Read:** `01_SYSTEM_OVERVIEW.md` - Understand what the system does
2. **Read:** `02_CODE_FLOW_EXPLAINED.md` - Understand how code flows
3. **Read:** `03_CORE_FILES_EXPLAINED.md` - Understand each file
4. **Read:** `04_DATABASE_DOCUMENTATION.md` - Understand database
5. **Read:** `05_MODULE_BY_MODULE_GUIDE.md` - Deep dive into modules

### For Users:

1. **Read:** `README.md` - Quick overview
2. **Read:** `INSTALL.md` - How to install
3. **Read:** `QUICKSTART.md` - How to use
4. **Read:** `REPORTING_SYSTEM_GUIDE.md` - How to use reports

---

## 🎓 Learning Path

### Beginner Level:
1. Understand folder structure (this file)
2. Learn what each module does
3. Understand user roles

### Intermediate Level:
1. Understand code flow
2. Learn how authentication works
3. Understand database structure

### Advanced Level:
1. Understand all core files
2. Learn how reports are generated
3. Understand Nepali date conversion
4. Learn how to add new features

---

## 📞 Need Help?

### Common Questions:

**Q: Where do I start?**
A: Read this file, then `01_SYSTEM_OVERVIEW.md`

**Q: How do I add a new feature?**
A: Read `05_MODULE_BY_MODULE_GUIDE.md` to see how existing features work

**Q: How does authentication work?**
A: Read `02_CODE_FLOW_EXPLAINED.md` - Authentication Flow section

**Q: What does this file do?**
A: Check `03_CORE_FILES_EXPLAINED.md` - it explains every file

**Q: How is data stored?**
A: Check `04_DATABASE_DOCUMENTATION.md` - complete database guide

---

## 🎯 Next Steps

**Ready to learn more?**

👉 **Go to:** `01_SYSTEM_OVERVIEW.md`

This will give you a complete overview of what FarmSaathi does and how it's organized.

---

**Last Updated:** December 2024  
**Version:** 1.0  
**System:** FarmSaathi Farm Management System
