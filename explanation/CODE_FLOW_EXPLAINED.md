# Code Flow Explained - How Everything Works Together

## Complete User Journey Examples

---

## Journey 1: User Visits Website (First Time)

### Step-by-Step Flow:

```
1. User types: localhost/Farmwebsite
   ↓
2. Apache server receives request
   ↓
3. .htaccess applies security rules
   ↓
4. index.php runs
   ↓
5. index.php loads:
   - config/database.php (database connection)
   - includes/functions.php (helper functions)
   - auth/session.php (session management)
   ↓
6. Checks: isLoggedIn()?
   Answer: NO (first time visitor)
   ↓
7. Redirects to: auth/login.php
   ↓
8. login.php loads:
   - includes/header.php (but skips navigation - not logged in)
   - Shows login form
   - includes/footer.php
   ↓
9. User sees login page
```

**Files Involved:**
1. `.htaccess`
2. `index.php`
3. `config/database.php`
4. `includes/functions.php`
5. `auth/session.php`
6. `auth/login.php`

---

## Journey 2: User Logs In

### Step-by-Step Flow:

```
1. User enters username & password
   ↓
2. Clicks "Login" button
   ↓
3. Form submits to: auth/login.php (POST request)
   ↓
4. login.php receives POST data:
   $_POST['username'] = 'john'
   $_POST['password'] = 'password123'
   ↓
5. Sanitizes input:
   $username = sanitizeInput($_POST['username'])
   ↓
6. Calls: authenticateUser($username, $password)
   ↓
7. authenticateUser() in auth/session.php:
   - Queries database: SELECT * FROM users WHERE username = ?
   - Gets user record
   - Verifies password: password_verify($password, $user['password'])
   ↓
8. If password correct:
   - Updates last_login in database
   - Calls: createSession($user['id'], $user['username'], $user['role'])
   ↓
9. createSession() does:
   - session_regenerate_id() (security)
   - $_SESSION['user_id'] = 1
   - $_SESSION['username'] = 'john'
   - $_SESSION['role'] = 'manager'
   - $_SESSION['logged_in'] = true
   ↓
10. Sets flash message: "Welcome back, john!"
    ↓
11. Redirects to: dashboard/index.php
    ↓
12. User sees dashboard
```

**Database Queries:**
1. `SELECT * FROM users WHERE username = 'john'`
2. `UPDATE users SET last_login = NOW() WHERE id = 1`

**Session Data Created:**
```php
$_SESSION = [
    'user_id' => 1,
    'username' => 'john',
    'role' => 'manager',
    'logged_in' => true,
    'last_activity' => 1705334400
]
```

---

## Journey 3: User Adds a New Crop

### Step-by-Step Flow:

```
1. User clicks "Crops" in navigation
   ↓
2. Goes to: crops/index.php
   ↓
3. crops/index.php:
   - Loads includes/header.php
   - Checks: requireLogin() ✓
   - Queries: SELECT * FROM crops
   - Displays crops in table
   - Shows "Add New Crop" button
   ↓
4. User clicks "Add New Crop"
   ↓
5. Goes to: crops/add.php
   ↓
6. crops/add.php:
   - Loads includes/header.php
   - Checks: requireLogin() ✓
   - Shows empty form
   ↓
7. User fills form:
   - Crop Name: "Wheat Field A"
   - Crop Type: "Wheat"
   - Planting Date: "2024-01-15"
   - Expected Harvest: "2024-05-15"
   - Field Location: "North Field"
   - Area: "5.5"
   - Notes: "Premium variety"
   ↓
8. User clicks "Add Crop"
   ↓
9. Form submits to: crops/add.php (POST)
   ↓
10. crops/add.php receives POST data
    ↓
11. Sanitizes all inputs:
    $crop_name = sanitizeInput($_POST['crop_name'])
    $crop_type = sanitizeInput($_POST['crop_type'])
    ... etc
    ↓
12. Validates:
    - Is crop_name empty? NO ✓
    - Is crop_type empty? NO ✓
    - Is planting_date valid? YES ✓
    - Is area a positive number? YES ✓
    ↓
13. All valid! Prepares SQL:
    INSERT INTO crops (crop_name, crop_type, planting_date, 
    expected_harvest, field_location, area_hectares, notes, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ↓
14. Executes query with prepared statement
    ↓
15. Gets new crop ID: $crop_id = 5
    ↓
16. Logs activity:
    logActivity('create', 'crops', 'Added new crop: Wheat Field A')
    ↓
17. logActivity() does:
    INSERT INTO activity_log (user_id, username, action, 
    module, description, ip_address)
    VALUES (1, 'john', 'create', 'crops', 
    'Added new crop: Wheat Field A', '192.168.1.100')
    ↓
18. Sets flash message:
    setFlashMessage("Crop added successfully!", 'success')
    ↓
19. Redirects to: crops/index.php
    ↓
20. crops/index.php shows:
    - Green success message at top
    - New crop in the table
```

**Database Queries:**
1. `INSERT INTO crops (...) VALUES (...)`
2. `INSERT INTO activity_log (...) VALUES (...)`

**Files Involved:**
1. `crops/index.php`
2. `crops/add.php`
3. `includes/header.php`
4. `includes/functions.php` (sanitizeInput, setFlashMessage, logActivity)
5. `auth/session.php` (requireLogin)
6. `config/database.php` (getDBConnection)

---

## Journey 4: User Records a Crop Sale

### Step-by-Step Flow:

```
1. User goes to: crops/index.php
   ↓
2. Finds crop in table
   ↓
3. Clicks "💰 Record Sale" button
   ↓
4. Goes to: crops/record_sale.php?crop_id=5
   ↓
5. record_sale.php:
   - Gets crop_id from URL: $_GET['crop_id'] = 5
   - Queries: SELECT * FROM crops WHERE id = 5
   - Loads crop data
   - Shows form pre-filled with crop name
   ↓
6. User fills sale form:
   - Crop Name: "Wheat Field A" (auto-filled)
   - Sale Date: "2024-05-20"
   - Quantity Sold: "4000"
   - Unit: "kg"
   - Rate per Unit: "12.00"
   - Buyer Name: "ABC Traders"
   - Buyer Contact: "9841234567"
   ↓
7. JavaScript calculates:
   Total Price = 4000 × 12.00 = Rs. 48,000.00
   (Shows in real-time as user types)
   ↓
8. User clicks "Record Sale"
   ↓
9. Form submits (POST)
   ↓
10. record_sale.php validates:
    - Quantity > 0? YES ✓
    - Rate > 0? YES ✓
    - Buyer name not empty? YES ✓
    ↓
11. Calculates total:
    $total_price = 4000 × 12.00 = 48000
    ↓
12. Inserts into crop_sales:
    INSERT INTO crop_sales (crop_id, crop_name, quantity_sold,
    unit, rate_per_unit, total_price, buyer_name, buyer_contact,
    sale_date)
    VALUES (5, 'Wheat Field A', 4000, 'kg', 12.00, 48000,
    'ABC Traders', '9841234567', '2024-05-20')
    ↓
13. Gets sale_id: $sale_id = 1
    ↓
14. Records income:
    INSERT INTO income (source, reference_id, amount, 
    income_date, description)
    VALUES ('crop_sales', 1, 48000, '2024-05-20',
    'Sale of Wheat Field A to ABC Traders')
    ↓
15. Logs activity:
    logActivity('create', 'crops', 'Recorded sale of Wheat Field A')
    ↓
16. Sets success message
    ↓
17. Redirects to: crops/index.php
```

**Database Queries:**
1. `SELECT * FROM crops WHERE id = 5`
2. `INSERT INTO crop_sales (...) VALUES (...)`
3. `INSERT INTO income (...) VALUES (...)`
4. `INSERT INTO activity_log (...) VALUES (...)`

**Result:**
- Sale recorded in `crop_sales` table
- Income recorded in `income` table
- Activity logged
- User sees success message

---

## Journey 5: User Views a Report

### Step-by-Step Flow:

```
1. User clicks "Reports" in navigation
   ↓
2. Goes to: reports/index.php
   ↓
3. Sees two sections:
   - Advanced Reports (new)
   - Basic Reports (old)
   ↓
4. User clicks "Crop Reports"
   ↓
5. Goes to: reports/crop_reports.php
   ↓
6. Sees three report types:
   - Production Report
   - Growth Monitoring
   - Sales Report
   ↓
7. User clicks "Sales Report"
   ↓
8. Goes to: reports/crop_reports.php?report=sales
   ↓
9. crop_reports.php:
   - Gets report type: $reportType = 'sales'
   - Includes: crop_sales_report.php
   ↓
10. crop_sales_report.php:
    - Builds SQL query:
      SELECT crop_name, quantity_sold, unit, rate_per_unit,
      total_price, buyer_name, sale_date
      FROM crop_sales
      ORDER BY sale_date DESC
    ↓
11. Executes query
    ↓
12. Calculates summary:
    - Total Sales: COUNT(*)
    - Total Quantity: SUM(quantity_sold)
    - Total Revenue: SUM(total_price)
    - Average Rate: AVG(rate_per_unit)
    ↓
13. Gets top buyers:
    SELECT buyer_name, COUNT(*) as purchases,
    SUM(total_price) as total_spent
    FROM crop_sales
    GROUP BY buyer_name
    ORDER BY total_spent DESC
    LIMIT 5
    ↓
14. Displays:
    - Summary cards at top
    - Top 5 buyers table
    - All sales in detailed table
    - Export to CSV button
    ↓
15. User sees report with:
    - Total Sales: 3
    - Total Revenue: Rs. 102,600.00
    - All sales listed
    - Dates in Nepali format
```

**Database Queries:**
1. `SELECT * FROM crop_sales ORDER BY sale_date DESC`
2. `SELECT SUM(...) FROM crop_sales` (summary)
3. `SELECT buyer_name, ... GROUP BY buyer_name` (top buyers)

**Date Conversion:**
- Database: `2024-05-20`
- Display: `Jestha 7, 2081 BS` (Nepali date)

---

## Journey 6: Admin Views Activity Log

### Step-by-Step Flow:

```
1. Admin user logs in
   ↓
2. Clicks "User Activity" in navigation
   ↓
3. Goes to: admin/activity/index.php
   ↓
4. admin/activity/index.php:
   - Checks: requirePermission('admin')
   - If not admin: redirects to dashboard
   - If admin: continues
   ↓
5. Queries activity log:
   SELECT al.*, u.email
   FROM activity_log al
   LEFT JOIN users u ON al.user_id = u.id
   ORDER BY al.created_at DESC
   LIMIT 20
   ↓
6. Displays table:
   - Username
   - Action (create/update/delete)
   - Module (crops/livestock/etc.)
   - Description
   - IP Address
   - Timestamp (in Nepali date)
   ↓
7. Shows filters:
   - Filter by user
   - Filter by module
   - Filter by date range
   ↓
8. Admin can see:
   - Who did what
   - When they did it
   - From which IP address
```

**Security:**
- Only admins can access
- Regular managers get redirected
- All actions are logged (audit trail)

---

## How Security Works Throughout

### 1. Every Page Load:

```php
// At top of every page
requireLogin();  // Redirects if not logged in
```

### 2. Admin Pages:

```php
// At top of admin pages
requirePermission('admin');  // Redirects if not admin
```

### 3. Input Sanitization:

```php
// For every user input
$input = sanitizeInput($_POST['field']);
// Removes HTML, trims spaces, escapes special chars
```

### 4. SQL Injection Prevention:

```php
// Never do this:
$query = "SELECT * FROM users WHERE username = '$username'";  // DANGEROUS!

// Always do this:
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

### 5. Password Security:

```php
// Storing password:
$hashed = password_hash($password, PASSWORD_DEFAULT);
// Stores: $2y$10$abcd... (never plain text!)

// Verifying password:
if (password_verify($entered_password, $stored_hash)) {
    // Correct!
}
```

### 6. Session Security:

```php
// After login:
session_regenerate_id(true);  // Prevents session fixation
$_SESSION['user_id'] = $user_id;

// Session timeout (30 minutes):
if (time() - $_SESSION['last_activity'] > 1800) {
    destroySession();  // Auto-logout
}
```

---

## Summary: The Big Picture

### When User Visits Any Page:

```
1. .htaccess (security rules)
   ↓
2. Page PHP file (e.g., crops/index.php)
   ↓
3. includes/header.php
   ├─ config/database.php (DB connection)
   ├─ includes/functions.php (helpers)
   ├─ includes/nepali_date.php (date converter)
   └─ auth/session.php (login check)
   ↓
4. Check: Is user logged in?
   YES → Continue
   NO → Redirect to login
   ↓
5. Check: Does user have permission?
   YES → Continue
   NO → Redirect to dashboard
   ↓
6. Page content (forms, tables, etc.)
   ↓
7. includes/footer.php
```

### Data Flow:

```
User Input (Form)
   ↓
Sanitize (clean input)
   ↓
Validate (check rules)
   ↓
Database (save/update/delete)
   ↓
Log Activity (audit trail)
   ↓
Flash Message (success/error)
   ↓
Redirect (show result)
```

### Every Module Follows Same Pattern:

1. **List** (index.php) - Show all records
2. **Add** (add.php) - Create new record
3. **Edit** (edit.php) - Update record
4. **Delete** (delete.php) - Remove record

### Security at Every Step:

1. Login required
2. Permission check
3. Input sanitization
4. SQL injection prevention
5. Activity logging
6. Session management

---

## That's How Everything Works!

Every page, every action, every database query follows these patterns. Once you understand one module, you understand them all!
