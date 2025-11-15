# Advanced Reporting System - Design Document

## Overview

The Advanced Reporting System extends the existing basic reporting functionality in FarmSaathi to provide comprehensive, categorized reports across all farm management modules. The system will be built as an enhancement to the existing `reports/index.php` file, adding new report types, advanced filtering, and export capabilities.

### Design Goals

1. **Modularity**: Each report type should be self-contained and reusable
2. **Performance**: Reports should load quickly using optimized SQL queries
3. **Extensibility**: New report types should be easy to add
4. **User Experience**: Intuitive navigation and filtering with clear data visualization
5. **Data Accuracy**: All calculations must be precise and based on current database state

## Architecture

### High-Level Structure

```
reports/
├── index.php (Main report selector and router)
├── crop_reports.php (Crop-specific reports)
├── livestock_reports.php (Livestock-specific reports)
├── finance_reports.php (Financial reports)
├── inventory_reports.php (Inventory reports - enhanced)
├── master_reports.php (Combined/master reports)
└── lib/
    ├── report_generator.php (Core report generation logic)
    ├── pdf_export.php (PDF export functionality)
    └── excel_export.php (Excel export functionality)
```

### Database Schema Extensions

The current schema supports most reporting needs, but we need to add tables for tracking sales and production:

```sql
-- Crop Sales Table (NEW)
CREATE TABLE crop_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    quantity_sold DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    rate_per_unit DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    buyer_name VARCHAR(100) NOT NULL,
    sale_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    INDEX idx_sale_date (sale_date),
    INDEX idx_crop_id (crop_id)
);

-- Crop Production Details Table (NEW)
CREATE TABLE crop_production (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    expected_yield DECIMAL(10,2),
    actual_yield DECIMAL(10,2),
    yield_unit VARCHAR(20) DEFAULT 'kg',
    production_cost DECIMAL(10,2),
    profit DECIMAL(10,2),
    harvest_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    INDEX idx_crop_id (crop_id)
);

-- Crop Growth Monitoring Table (NEW)
CREATE TABLE crop_growth_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    monitoring_date DATE NOT NULL,
    growth_stage ENUM('seedling', 'vegetative', 'flowering', 'fruiting', 'harvest') NOT NULL,
    watering_frequency VARCHAR(50),
    fertilizer_used VARCHAR(200),
    pesticide_used VARCHAR(200),
    disease_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    INDEX idx_crop_id (crop_id),
    INDEX idx_monitoring_date (monitoring_date)
);

-- Livestock Health Records Table (NEW)
CREATE TABLE livestock_health (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livestock_id INT NOT NULL,
    animal_id VARCHAR(50),
    breed VARCHAR(50),
    age_months INT,
    weight_kg DECIMAL(10,2),
    vaccination_date DATE,
    vaccination_type VARCHAR(100),
    disease_history TEXT,
    medication_treatment TEXT,
    checkup_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE,
    INDEX idx_livestock_id (livestock_id),
    INDEX idx_checkup_date (checkup_date)
);

-- Livestock Production Table (NEW)
CREATE TABLE livestock_production (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livestock_id INT NOT NULL,
    animal_id VARCHAR(50),
    production_date DATE NOT NULL,
    milk_production DECIMAL(10,2) DEFAULT 0,
    egg_production INT DEFAULT 0,
    meat_production DECIMAL(10,2) DEFAULT 0,
    feed_consumption DECIMAL(10,2),
    production_unit VARCHAR(20) DEFAULT 'liters',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE,
    INDEX idx_livestock_id (livestock_id),
    INDEX idx_production_date (production_date)
);

-- Livestock Mortality Table (NEW)
CREATE TABLE livestock_mortality (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livestock_id INT NOT NULL,
    animal_id VARCHAR(50),
    death_date DATE NOT NULL,
    cause TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL,
    INDEX idx_death_date (death_date)
);

-- Livestock Sales Table (NEW)
CREATE TABLE livestock_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livestock_id INT,
    animal_type VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    purchase_cost DECIMAL(10,2),
    profit_loss DECIMAL(10,2),
    buyer_name VARCHAR(100) NOT NULL,
    sale_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL,
    INDEX idx_sale_date (sale_date),
    INDEX idx_animal_type (animal_type)
);

-- Income Table (NEW)
CREATE TABLE income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source ENUM('crop_sales', 'livestock_sales', 'miscellaneous') NOT NULL,
    reference_id INT,
    amount DECIMAL(10,2) NOT NULL,
    income_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source),
    INDEX idx_income_date (income_date)
);

-- Inventory Transactions Table (NEW)
CREATE TABLE inventory_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inventory_id INT NOT NULL,
    transaction_type ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    purpose VARCHAR(200),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE,
    INDEX idx_inventory_id (inventory_id),
    INDEX idx_transaction_date (transaction_date)
);
```

## Components and Interfaces

### 1. Report Generator Core (`lib/report_generator.php`)

**Purpose**: Centralized logic for building SQL queries, applying filters, and formatting report data.

**Key Functions**:

```php
class ReportGenerator {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Build WHERE clause from filters
     * @param array $filters Associative array of filter conditions
     * @return array ['clause' => string, 'params' => array, 'types' => string]
     */
    public function buildWhereClause($filters) {
        // Implementation
    }
    
    /**
     * Execute query with parameters
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param string $types Parameter types
     * @return mysqli_result
     */
    public function executeQuery($query, $params = [], $types = '') {
        // Implementation
    }
    
    /**
     * Format report data for display
     * @param mysqli_result $result Query result
     * @param array $formatRules Formatting rules for columns
     * @return array Formatted data
     */
    public function formatReportData($result, $formatRules = []) {
        // Implementation
    }
    
    /**
     * Calculate aggregates (sum, avg, count, etc.)
     * @param array $data Report data
     * @param array $aggregateRules Rules for aggregation
     * @return array Calculated aggregates
     */
    public function calculateAggregates($data, $aggregateRules) {
        // Implementation
    }
}
```

### 2. Crop Reports Module (`crop_reports.php`)

**Report Types**:

#### A. Crop Production Report
- **Data Source**: `crops` + `crop_production` tables
- **Key Metrics**: Expected vs actual yield, production cost, profit per crop
- **Filters**: Date range, crop type, field location, status

#### B. Crop Growth Monitoring Report
- **Data Source**: `crops` + `crop_growth_monitoring` tables
- **Key Metrics**: Growth stages, input usage, disease tracking
- **Filters**: Date range, crop name, growth stage

#### C. Crop Sales Report
- **Data Source**: `crop_sales` table
- **Key Metrics**: Quantity sold, revenue, buyer analysis
- **Filters**: Date range, crop name, buyer

**Sample Query Structure**:
```sql
-- Crop Production Report
SELECT 
    c.crop_name,
    c.field_location,
    c.planting_date,
    cp.harvest_date,
    cp.expected_yield,
    cp.actual_yield,
    cp.yield_unit,
    cp.production_cost,
    cp.profit,
    ROUND((cp.actual_yield / cp.expected_yield * 100), 2) as yield_percentage
FROM crops c
LEFT JOIN crop_production cp ON c.id = cp.crop_id
WHERE c.planting_date BETWEEN ? AND ?
ORDER BY c.planting_date DESC;
```

### 3. Livestock Reports Module (`livestock_reports.php`)

**Report Types**:

#### A. Livestock Health Report
- **Data Source**: `livestock` + `livestock_health` tables
- **Key Metrics**: Vaccination status, disease history, treatment records
- **Filters**: Date range, animal type, health status

#### B. Livestock Production Report
- **Data Source**: `livestock` + `livestock_production` + `livestock_mortality` tables
- **Key Metrics**: Milk/egg/meat production, feed consumption, mortality rate
- **Filters**: Date range, animal type, production type
- **Aggregation**: Daily, weekly, monthly summaries

#### C. Livestock Sales Report
- **Data Source**: `livestock_sales` table
- **Key Metrics**: Sales revenue, profit/loss, buyer analysis
- **Filters**: Date range, animal type, buyer

**Sample Query Structure**:
```sql
-- Livestock Production Report (Monthly)
SELECT 
    l.animal_type,
    lp.animal_id,
    DATE_FORMAT(lp.production_date, '%Y-%m') as month,
    SUM(lp.milk_production) as total_milk,
    SUM(lp.egg_production) as total_eggs,
    SUM(lp.meat_production) as total_meat,
    SUM(lp.feed_consumption) as total_feed,
    COUNT(DISTINCT lp.production_date) as production_days
FROM livestock l
INNER JOIN livestock_production lp ON l.id = lp.livestock_id
WHERE lp.production_date BETWEEN ? AND ?
GROUP BY l.animal_type, lp.animal_id, month
ORDER BY month DESC, l.animal_type;
```

### 4. Finance Reports Module (`finance_reports.php`)

**Report Types**:

#### A. Income Report
- **Data Source**: `income` table + aggregated sales data
- **Key Metrics**: Income by source, total income, income trends
- **Filters**: Date range, income source

#### B. Expense Report
- **Data Source**: `expenses` table
- **Key Metrics**: Expenses by category, total expenses, expense trends
- **Filters**: Date range, category, payment method

#### C. Profit & Loss Report
- **Data Source**: `income` + `expenses` tables
- **Key Metrics**: Total income, total expenses, net profit/loss, monthly trends
- **Filters**: Date range
- **Visualization**: Monthly trend chart data

**Sample Query Structure**:
```sql
-- Profit & Loss Report (Monthly)
SELECT 
    DATE_FORMAT(date_col, '%Y-%m') as month,
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
    SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_profit
FROM (
    SELECT income_date as date_col, amount, 'income' as type FROM income
    UNION ALL
    SELECT expense_date as date_col, amount, 'expense' as type FROM expenses
) combined
WHERE date_col BETWEEN ? AND ?
GROUP BY month
ORDER BY month DESC;
```

### 5. Inventory Reports Module (`inventory_reports.php`)

**Report Types**:

#### A. Stock Summary Report
- **Data Source**: `inventory` table
- **Key Metrics**: Current stock, reorder alerts, stock value
- **Filters**: Category, low stock only

#### B. Stock In/Out Report
- **Data Source**: `inventory` + `inventory_transactions` tables
- **Key Metrics**: Transactions, usage patterns, remaining quantity
- **Filters**: Date range, item name, transaction type, purpose

**Sample Query Structure**:
```sql
-- Stock In/Out Report
SELECT 
    i.item_name,
    i.category,
    it.transaction_type,
    it.quantity,
    it.purpose,
    it.transaction_date,
    i.quantity as current_quantity,
    i.unit
FROM inventory i
INNER JOIN inventory_transactions it ON i.id = it.inventory_id
WHERE it.transaction_date BETWEEN ? AND ?
ORDER BY it.transaction_date DESC, i.item_name;
```

### 6. Master Reports Module (`master_reports.php`)

**Report Types**:

#### A. Overall Farm Performance Report
- **Data Source**: All tables
- **Key Metrics**: 
  - Total crop production (sum of actual yields)
  - Total livestock production (milk, eggs, meat)
  - Total income
  - Total expenses
  - Net farm profit
  - Inventory status (items in stock, low stock count)
  - Active alerts (low stock, maintenance due, health issues)
- **Filters**: Date range

#### B. Month-wise Summary Report
- **Data Source**: All tables aggregated by month
- **Key Metrics**: Monthly breakdown of all farm activities
- **Filters**: Month/year selection, comparison mode
- **Visualization**: Comparative charts across months

**Sample Query Structure**:
```sql
-- Overall Farm Performance
SELECT 
    (SELECT COALESCE(SUM(actual_yield), 0) FROM crop_production WHERE harvest_date BETWEEN ? AND ?) as total_crop_production,
    (SELECT COALESCE(SUM(milk_production + egg_production + meat_production), 0) FROM livestock_production WHERE production_date BETWEEN ? AND ?) as total_livestock_production,
    (SELECT COALESCE(SUM(amount), 0) FROM income WHERE income_date BETWEEN ? AND ?) as total_income,
    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date BETWEEN ? AND ?) as total_expenses,
    (SELECT COUNT(*) FROM inventory WHERE quantity <= reorder_level) as low_stock_count,
    (SELECT COUNT(*) FROM equipment WHERE next_maintenance <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)) as maintenance_due_count,
    (SELECT COUNT(*) FROM livestock WHERE health_status IN ('sick', 'under_treatment', 'quarantine')) as health_issues_count;
```

## Data Models

### Report Filter Model
```php
class ReportFilter {
    public $dateFrom;
    public $dateTo;
    public $category;
    public $status;
    public $customFilters = [];
    
    public function isValid() {
        // Validation logic
    }
    
    public function toArray() {
        // Convert to array for query building
    }
}
```

### Report Data Model
```php
class ReportData {
    public $title;
    public $filters;
    public $headers = [];
    public $rows = [];
    public $summary = [];
    public $metadata = [];
    
    public function toHTML() {
        // Generate HTML table
    }
    
    public function toArray() {
        // Convert to array for export
    }
}
```

## Error Handling

### Error Scenarios

1. **Database Connection Failure**
   - Display user-friendly error message
   - Log error details to server log
   - Provide retry option

2. **Invalid Filter Parameters**
   - Validate all user inputs
   - Display specific validation errors
   - Preserve valid filter values

3. **No Data Found**
   - Display "No data available" message
   - Suggest adjusting filters
   - Show filter summary

4. **Export Failure**
   - Catch export exceptions
   - Display error message
   - Log failure details

5. **Query Timeout**
   - Implement query timeout limits
   - Suggest narrowing date range
   - Consider pagination for large datasets

## Testing Strategy

### Unit Tests
- Test filter validation logic
- Test SQL query building
- Test data formatting functions
- Test calculation accuracy

### Integration Tests
- Test report generation end-to-end
- Test export functionality
- Test filter combinations
- Test with various data volumes

### Performance Tests
- Test report generation with 1000+ records
- Test export with large datasets
- Measure query execution times
- Optimize slow queries

### User Acceptance Tests
- Verify report accuracy with sample data
- Test all filter combinations
- Verify export file formats
- Test on different browsers

## UI/UX Design

### Report Navigation
```
[Report Categories]
├── Crop Reports ▼
│   ├── Production Report
│   ├── Growth Monitoring
│   └── Sales Report
├── Livestock Reports ▼
│   ├── Health Report
│   ├── Production Report
│   └── Sales Report
├── Finance Reports ▼
│   ├── Income Report
│   ├── Expense Report
│   └── Profit & Loss
├── Inventory Reports ▼
│   ├── Stock Summary
│   └── Stock In/Out
└── Master Reports ▼
    ├── Farm Performance
    └── Monthly Summary
```

### Filter Panel Design
- Collapsible filter section
- Date range picker
- Category dropdowns
- Clear filters button
- Apply filters button

### Report Display
- Summary cards at top (key metrics)
- Data table with sorting
- Pagination for large datasets
- Export buttons (PDF, Excel)
- Print-friendly layout

## Performance Optimization

1. **Database Indexing**: Ensure all date and foreign key columns are indexed
2. **Query Optimization**: Use JOINs efficiently, avoid N+1 queries
3. **Caching**: Cache frequently accessed report data (optional)
4. **Pagination**: Limit results to 50-100 rows per page
5. **Lazy Loading**: Load report data only when selected

## Security Considerations

1. **SQL Injection Prevention**: Use prepared statements for all queries
2. **Access Control**: Verify user permissions before displaying reports
3. **Input Validation**: Sanitize all filter inputs
4. **Export Security**: Validate file paths, prevent directory traversal
5. **Session Management**: Ensure user is authenticated before report access
