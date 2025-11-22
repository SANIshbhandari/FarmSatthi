-- Data Isolation Migration
-- Adds created_by column to all data tables for user-based data isolation
-- Run this file: mysql -u root -p farm_management < database/add_data_isolation.sql

USE farm_management;

-- ============================================================================
-- STEP 1: Add created_by columns to all data tables
-- ============================================================================

ALTER TABLE crops ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE livestock ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE equipment ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE employees ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE expenses ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE inventory ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE income ADD COLUMN created_by INT NOT NULL DEFAULT 1 AFTER id;

-- ============================================================================
-- STEP 2: Add foreign key constraints
-- ============================================================================

ALTER TABLE crops ADD CONSTRAINT fk_crops_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE livestock ADD CONSTRAINT fk_livestock_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE equipment ADD CONSTRAINT fk_equipment_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE employees ADD CONSTRAINT fk_employees_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE expenses ADD CONSTRAINT fk_expenses_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE inventory ADD CONSTRAINT fk_inventory_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

ALTER TABLE income ADD CONSTRAINT fk_income_user 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT;

-- ============================================================================
-- STEP 3: Add indexes for performance
-- ============================================================================

ALTER TABLE crops ADD INDEX idx_created_by (created_by);
ALTER TABLE livestock ADD INDEX idx_created_by (created_by);
ALTER TABLE equipment ADD INDEX idx_created_by (created_by);
ALTER TABLE employees ADD INDEX idx_created_by (created_by);
ALTER TABLE expenses ADD INDEX idx_created_by (created_by);
ALTER TABLE inventory ADD INDEX idx_created_by (created_by);
ALTER TABLE income ADD INDEX idx_created_by (created_by);

-- ============================================================================
-- STEP 4: Update existing data (assign to first admin user)
-- ============================================================================

UPDATE crops SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;
UPDATE livestock SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;
UPDATE equipment SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;
UPDATE employees SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;
UPDATE expenses SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;
UPDATE inventory SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;
UPDATE income SET created_by = 1 WHERE created_by = 0 OR created_by IS NULL;

-- ============================================================================
-- VERIFICATION: Check migration results
-- ============================================================================

SELECT 'Migration completed successfully!' as 'Status';

SELECT 
    'Crops' as 'Table', 
    COUNT(*) as 'Total Records', 
    COUNT(DISTINCT created_by) as 'Unique Users',
    MIN(created_by) as 'Min User ID',
    MAX(created_by) as 'Max User ID'
FROM crops
UNION ALL
SELECT 'Livestock', COUNT(*), COUNT(DISTINCT created_by), MIN(created_by), MAX(created_by) FROM livestock
UNION ALL
SELECT 'Equipment', COUNT(*), COUNT(DISTINCT created_by), MIN(created_by), MAX(created_by) FROM equipment
UNION ALL
SELECT 'Employees', COUNT(*), COUNT(DISTINCT created_by), MIN(created_by), MAX(created_by) FROM employees
UNION ALL
SELECT 'Expenses', COUNT(*), COUNT(DISTINCT created_by), MIN(created_by), MAX(created_by) FROM expenses
UNION ALL
SELECT 'Inventory', COUNT(*), COUNT(DISTINCT created_by), MIN(created_by), MAX(created_by) FROM inventory
UNION ALL
SELECT 'Income', COUNT(*), COUNT(DISTINCT created_by), MIN(created_by), MAX(created_by) FROM income;

-- Show which users own data
SELECT 
    u.id,
    u.username,
    u.role,
    (SELECT COUNT(*) FROM crops WHERE created_by = u.id) as crops,
    (SELECT COUNT(*) FROM livestock WHERE created_by = u.id) as livestock,
    (SELECT COUNT(*) FROM equipment WHERE created_by = u.id) as equipment,
    (SELECT COUNT(*) FROM employees WHERE created_by = u.id) as employees,
    (SELECT COUNT(*) FROM expenses WHERE created_by = u.id) as expenses,
    (SELECT COUNT(*) FROM inventory WHERE created_by = u.id) as inventory
FROM users u
ORDER BY u.id;
