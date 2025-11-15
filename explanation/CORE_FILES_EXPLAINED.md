# Core Files Explained - Line by Line

## 1. index.php (Main Entry Point)

**Location:** `/index.php`

**Purpose:** This is the FIRST file that runs when someone visits your website.

**What It Does:**
```php
<?php
session_start();                          // Start user session
require_once __DIR__ . '/config/database.php';  // Load database connection
require_once __DIR__ . '/includes/functions.php'; // Load helper functions
require_once __DIR__ . '/auth/session.php';     // Load session management

// Check if user is logged in
if (isLoggedIn()) {
    redirect('dashboard/index.php');      // Send logged-in users to dashboard
} else {
    redirect('auth/login.php');           // Send guests to login page
}
?>
```

**Flow:**
1. User visits `localhost/Farmwebsite`
2. System checks: Are you logged in?
3. YES → Go to Dashboard
4. NO → Go to Login Page

---

## 2. config/database.php (Database Connection)

**Purpose:** Connects your PHP code to MySQL database.

**What It Does:**
```php
<?php
function getDBConnection() {
    $host = 'localhost';           // Database server (usually localhost)
    $username = 'root';            // MySQL username
    $password = '';                // MySQL password
    $database = 'farm_management'; // Database name
    
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check if connection failed
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;  // Return the connection to use in other files
}
?>
```

**When Used:** Every time you need to read/write data from database.

---

## 3. config/config.php (App Configuration)

**Purpose:** Stores app-wide settings and helper functions.

**What It Does:**
```php
<?php
// Base URL of your application
define('BASE_URL', '/Farmwebsite/');

// Asset helper function
function asset($path) {
    return BASE_URL . 'assets/' . $path;
}

// URL helper function
function url($path) {
    return BASE_URL . $path;
}
?>
```

**Usage Examples:**
- `asset('css/style.css')` → `/Farmwebsite/assets/css/style.css`
- `url('crops/index.php')` → `/Farmwebsite/crops/index.php`

---

## 4. includes/header.php (Page Header)

**Purpose:** Common header for all pages (navigation, logo, user info).

**What It Does:**
1. Starts session
2. Loads all required files
3. Checks if user is logged in
4. Shows navigation menu
5. Displays user info

**Structure:**
```
┌─────────────────────────────────────┐
│  🌾 FarmSaathi    👤 Username (role) │
│                        [Logout]      │
├─────────────────────────────────────┤
│ Dashboard | Crops | Livestock | ... │
└─────────────────────────────────────┘
```

---

## 5. includes/footer.php (Page Footer)

**Purpose:** Common footer for all pages.

**What It Does:**
```html
</div>  <!-- Close main content -->
</main>

<footer>
    <p>&copy; 2024 Farm Management System</p>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
```

---

## 6. includes/functions.php (Helper Functions)

**Purpose:** Reusable functions used throughout the system.

**Key Functions:**

### sanitizeInput($data)
**What:** Cleans user input to prevent XSS attacks
**Example:**
```php
$username = sanitizeInput($_POST['username']);
// Removes HTML tags, trims spaces, escapes special characters
```

### formatCurrency($amount)
**What:** Formats numbers as Nepali Rupees
**Example:**
```php
echo formatCurrency(1500);  // Output: Rs. 1,500.00
```

### formatDate($date)
**What:** Converts English date to Nepali date
**Example:**
```php
echo formatDate('2024-01-15');  // Output: Poush 1, 2080 BS
```

### redirect($url)
**What:** Redirects user to another page
**Example:**
```php
redirect('dashboard/index.php');  // Sends user to dashboard
```

### setFlashMessage($message, $type)
**What:** Shows temporary messages to user
**Example:**
```php
setFlashMessage("Crop added successfully!", 'success');
// Shows green success message on next page
```

### logActivity($action, $module, $description)
**What:** Records user actions in database
**Example:**
```php
logActivity('create', 'crops', 'Added new wheat crop');
// Saves: User X created something in crops module
```

---

## 7. auth/session.php (Session Management)

**Purpose:** Manages user login/logout and permissions.

**Key Functions:**

### isLoggedIn()
**What:** Checks if user is logged in
**Returns:** true or false
```php
if (isLoggedIn()) {
    // User is logged in
} else {
    // User is NOT logged in
}
```

### requireLogin()
**What:** Forces user to login (redirects if not logged in)
```php
requireLogin();  // If not logged in, sends to login page
```

### hasPermission($role)
**What:** Checks if user has specific permission
```php
if (hasPermission('admin')) {
    // User is admin
}
```

### createSession($userId, $username, $role)
**What:** Creates login session after successful login
```php
createSession(1, 'john', 'manager');
// Logs in user with ID 1, username john, role manager
```

### destroySession()
**What:** Logs out user
```php
destroySession();  // Clears all session data
```

---

## 8. .htaccess (Server Configuration)

**Purpose:** Apache server rules and security settings.

**What It Does:**

1. **Security Headers**
   - Prevents clickjacking attacks
   - Blocks XSS attacks
   - Prevents MIME type sniffing

2. **Disable Directory Browsing**
   - Users can't see folder contents

3. **Protect Sensitive Files**
   - Blocks access to config files
   - Hides .git files

4. **PHP Settings**
   - Hides PHP errors from users
   - Logs errors to file

---

## 9. 404.php (Page Not Found)

**Purpose:** Shows error when page doesn't exist.

**When It Runs:**
- User visits non-existent page
- Typo in URL
- Deleted page

**What It Shows:**
```
┌─────────────────────────┐
│   404 - Page Not Found  │
│                         │
│   The page you are      │
│   looking for does not  │
│   exist.                │
│                         │
│   [Go to Dashboard]     │
└─────────────────────────┘
```

---

## 10. error.php (Error Display)

**Purpose:** Shows user-friendly error messages.

**When It Runs:**
- Database connection fails
- Permission denied
- System error occurs

**What It Shows:**
```
┌─────────────────────────┐
│   ⚠️ Error Occurred     │
│                         │
│   [Error message here]  │
│                         │
│   [Go Back]             │
└─────────────────────────┘
```

---

## Flow Diagram: How Everything Connects

```
User visits website
        ↓
    index.php (Entry Point)
        ↓
    Checks: Logged in?
        ↓
    YES ──────────────→ dashboard/index.php
        ↓                       ↓
    NO                    includes/header.php
        ↓                       ↓
    auth/login.php         [Page Content]
        ↓                       ↓
    User enters credentials  includes/footer.php
        ↓
    auth/session.php validates
        ↓
    Creates session
        ↓
    Redirects to dashboard
```

---

## Summary: What Happens When You Visit the Site

1. **index.php** runs first
2. Loads **config/database.php** (database connection)
3. Loads **includes/functions.php** (helper functions)
4. Loads **auth/session.php** (session management)
5. Checks if you're logged in
6. Redirects to appropriate page
7. Page loads **includes/header.php** (navigation)
8. Shows page content
9. Loads **includes/footer.php** (footer)

Every page follows this pattern!
