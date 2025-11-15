-- Verification script for chart data
-- Run this to check if you have data for charts

USE farm_management;

-- Check Income Data
SELECT 'INCOME DATA' as 'Table', COUNT(*) as 'Record Count', SUM(amount) as 'Total Amount (Rs.)'
FROM income
UNION ALL
-- Check Expense Data
SELECT 'EXPENSE DATA', COUNT(*), SUM(amount)
FROM expenses;

-- Show Income by Source
SELECT 
    'Income by Source' as 'Report',
    source as 'Category',
    COUNT(*) as 'Count',
    SUM(amount) as 'Total (Rs.)'
FROM income
GROUP BY source;

-- Show Expenses by Category
SELECT 
    'Expenses by Category' as 'Report',
    category as 'Category',
    COUNT(*) as 'Count',
    SUM(amount) as 'Total (Rs.)'
FROM expenses
GROUP BY category;

-- Monthly Profit & Loss Summary
SELECT 
    DATE_FORMAT(date_col, '%Y-%m') as 'Month',
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as 'Income (Rs.)',
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as 'Expenses (Rs.)',
    SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as 'Net Profit (Rs.)'
FROM (
    SELECT income_date as date_col, amount, 'income' as type FROM income
    UNION ALL
    SELECT expense_date as date_col, amount, 'expense' as type FROM expenses
) combined
GROUP BY DATE_FORMAT(date_col, '%Y-%m')
ORDER BY Month DESC;
