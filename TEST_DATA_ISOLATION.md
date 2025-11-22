# Testing Data Isolation

## Issue: Crops not showing on dashboard

### Possible Causes:

1. **Database not migrated** - The `created_by` column might not exist in the crops table
2. **User ID mismatch** - The crops might be assigned to a different user
3. **Query error** - The isolation query might have syntax issues

### Solution Steps:

## Step 1: Check if database migration was run

Run this SQL to check if `created_by` column exists:
```sql
SHOW COLUMNS FROM crops LIKE 'created_by';
```

If it doesn't exist, run the migration:
```bash
mysql -u root -p farm_management < database/add_data_isolation.sql
```

## Step 2: Check existing crops data

Run this SQL to see which user owns the crops:
```sql
SELECT id, crop_name, created_by FROM crops;
```

## Step 3: Check current user ID

When logged in, check what user ID you're using:
```sql
SELECT id, username, role FROM users;
```

## Step 4: Verify the crops are being inserted

After adding a crop, check:
```sql
SELECT id, crop_name, created_by, status FROM crops ORDER BY id DESC LIMIT 5;
```

## Step 5: Manual fix if needed

If crops exist but have wrong `created_by`, update them:
```sql
-- Replace USER_ID with your actual user ID
UPDATE crops SET created_by = USER_ID WHERE created_by = 0 OR created_by IS NULL;
```

## Quick Test Query

To see if data isolation is working:
```sql
-- Replace USER_ID with your user ID
SELECT COUNT(*) as count 
FROM crops 
WHERE status = 'active' AND created_by = USER_ID;
```

This should match the number shown on your dashboard.

## Debug Mode

Add this temporarily to dashboard/index.php after line 50 to debug:
```php
// DEBUG: Check isolation
echo "<!-- DEBUG: Isolation WHERE = " . $isolationWhere . " -->";
echo "<!-- DEBUG: User ID = " . getCurrentUserId() . " -->";
echo "<!-- DEBUG: User Role = " . getCurrentUserRole() . " -->";
$debugResult = $conn->query("SELECT COUNT(*) as count FROM crops WHERE status = 'active' AND $isolationWhere");
echo "<!-- DEBUG: Active Crops Count = " . $debugResult->fetch_assoc()['count'] . " -->";
```

Then view page source to see the debug comments.
