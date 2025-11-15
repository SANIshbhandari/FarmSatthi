# Database Documentation - Every Table Explained

## Database Name: `farm_management`

---

## Core Tables (Main System)

### 1. users (User Accounts)

**Purpose:** Stores user login information

**Columns:**
- `id` - Unique user ID (auto-increment)
- `username` - Login username (unique)
- `password` - Hashed password (never stored as plain text)
- `email` - User email (unique)
- `role` - User role (admin/manager)
- `last_login` - Last login timestamp
- `created_at` - Account creation date

**Example Data:**
```
id | username | password      | email           | role    | last_login
1  | admin    | $2y$10$...   | admin@farm.com  | admin   | 2024-01-15
2  | john     | $2y$10$...   | john@farm.com   | manager | 2024-01-14
```

**Relationships:**
- Used by: activity_log (tracks who did what)

---

### 2. crops (Crop Management)

**Purpose:** Stores information about planted crops

**Columns:**
- `id` - Unique crop ID
- `crop_name` - Name of crop (e.g., "Wheat Field A")
- `crop_type` - Type of crop (e.g., "Wheat", "Rice")
- `planting_date` - When crop was planted
- `expected_harvest` - Expected harvest date
- `field_location` - Where crop is planted
- `area_hectares` - Size of field in hectares
- `status` - Current status (active/harvested/failed)
- `notes` - Additional notes
- `created_at` - Record creation date

**Example Data:**
```
id | crop_name    | crop_type | planting_date | area_hectares | status
1  | Wheat Field A| Wheat     | 2023-11-01    | 5.5          | active
2  | Rice Paddy 1 | Rice      | 2023-07-15    | 3.2          | harvested
```

**Relationships:**
- Referenced by: crop_sales, crop_production, crop_growth_monitoring

---

### 3. livestock (Animal Management)

**Purpose:** Stores livestock information

**Columns:**
- `id` - Unique livestock ID
- `animal_type` - Type (Cow/Buffalo/Goat/Chicken)
- `breed` - Breed name
- `count` - Number of animals
- `age_months` - Age in months
- `health_status` - Health (healthy/sick/under_treatment/quarantine)
- `purchase_date` - When purchased
- `current_value` - Current market value (Rs.)
- `notes` - Additional notes
- `created_at` - Record creation date

**Example Data:**
```
id | animal_type | breed    | count | age_months | health_status | current_value
1  | Cow         | Holstein | 5     | 36         | healthy       | 250000
2  | Goat        | Boer     | 10    | 18         | healthy       | 80000
```

**Relationships:**
- Referenced by: livestock_health, livestock_production, livestock_sales

---

### 4. equipment (Equipment Tracking)

**Purpose:** Tracks farm equipment and maintenance

**Columns:**
- `id` - Unique equipment ID
- `equipment_name` - Name of equipment
- `type` - Type (Tractor/Plow/Harvester/etc.)
- `purchase_date` - Purchase date
- `last_maintenance` - Last maintenance date
- `next_maintenance` - Next maintenance due date
- `condition` - Condition (excellent/good/fair/poor/needs_repair)
- `value` - Current value (Rs.)
- `notes` - Additional notes
- `created_at` - Record creation date

**Example Data:**
```
id | equipment_name | type     | next_maintenance | condition | value
1  | Tractor JD-500 | Tractor  | 2024-02-01      | good      | 500000
2  | Plow Set       | Plow     | 2024-03-15      | fair      | 25000
```

---

### 5. employees (Employee Management)

**Purpose:** Stores employee information

**Columns:**
- `id` - Unique employee ID
- `name` - Employee name
- `role` - Job role
- `phone` - Contact phone
- `email` - Email address
- `salary` - Monthly salary (Rs.)
- `hire_date` - Date hired
- `status` - Employment status (active/inactive/terminated)
- `notes` - Additional notes
- `created_at` - Record creation date

**Example Data:**
```
id | name        | role         | phone      | salary | status
1  | Ram Kumar   | Farm Worker  | 9841234567 | 15000  | active
2  | Sita Devi   | Supervisor   | 9851234567 | 25000  | active
```

---

### 6. expenses (Expense Tracking)

**Purpose:** Records all farm expenses

**Columns:**
- `id` - Unique expense ID
- `category` - Expense category
- `amount` - Amount spent (Rs.)
- `expense_date` - Date of expense
- `description` - What was purchased/paid for
- `payment_method` - How paid (cash/check/bank_transfer/credit_card)
- `created_at` - Record creation date

**Example Data:**
```
id | category   | amount | expense_date | description
1  | Seeds      | 5000   | 2024-01-10  | Wheat seeds for Field A
2  | Fertilizer | 8000   | 2024-01-12  | NPK fertilizer
```

**Categories:**
- Seeds
- Fertilizer
- Pesticide
- Labor
- Equipment Maintenance
- Fuel
- Utilities
- Other

---

### 7. inventory (Inventory Management)

**Purpose:** Tracks stock of supplies

**Columns:**
- `id` - Unique item ID
- `item_name` - Name of item
- `category` - Category (Seeds/Fertilizer/Feed/Medicine/Tools)
- `quantity` - Current quantity
- `unit` - Unit of measurement (kg/liters/pieces)
- `reorder_level` - Minimum quantity before reorder
- `last_updated` - Last update timestamp

**Example Data:**
```
id | item_name      | category   | quantity | unit   | reorder_level
1  | Wheat Seeds    | Seeds      | 50       | kg     | 20
2  | NPK Fertilizer | Fertilizer | 100      | kg     | 30
3  | Cattle Feed    | Feed       | 200      | kg     | 50
```

**Low Stock Alert:** When `quantity <= reorder_level`

---

### 8. activity_log (Activity Tracking)

**Purpose:** Logs all user actions for audit trail

**Columns:**
- `id` - Unique log ID
- `user_id` - Who performed action
- `username` - Username (for quick reference)
- `action` - Action type (create/update/delete/view)
- `module` - Which module (crops/livestock/etc.)
- `description` - What was done
- `ip_address` - User's IP address
- `created_at` - When action occurred

**Example Data:**
```
id | user_id | username | action | module    | description
1  | 1       | admin    | create | crops     | Added new wheat crop
2  | 2       | john     | update | livestock | Updated cow health status
3  | 1       | admin    | delete | expenses  | Deleted expense #45
```

---

## Reporting Tables (Advanced Reporting System)

### 9. crop_sales (Crop Sales Records)

**Purpose:** Records crop sales transactions

**Columns:**
- `id` - Sale ID
- `crop_id` - Which crop was sold
- `crop_name` - Crop name
- `quantity_sold` - Amount sold
- `unit` - Unit (kg/tons/quintals)
- `rate_per_unit` - Price per unit (Rs.)
- `total_price` - Total sale amount (Rs.)
- `buyer_name` - Who bought it
- `buyer_contact` - Buyer phone
- `sale_date` - Sale date
- `created_at` - Record creation

**Auto-Calculation:** `total_price = quantity_sold × rate_per_unit`

---

### 10. crop_production (Production Tracking)

**Purpose:** Tracks crop yield and costs

**Columns:**
- `id` - Record ID
- `crop_id` - Which crop
- `expected_yield` - Expected harvest (kg)
- `actual_yield` - Actual harvest (kg)
- `yield_unit` - Unit (kg/tons)
- `production_cost` - Total cost (Rs.)
- `profit` - Profit/loss (Rs.)
- `harvest_date` - Harvest date
- `notes` - Additional notes

**Calculations:**
- Yield percentage = (actual_yield / expected_yield) × 100
- Profit = (sales revenue) - production_cost

---

### 11. crop_growth_monitoring (Growth Tracking)

**Purpose:** Monitors crop growth stages

**Columns:**
- `id` - Record ID
- `crop_id` - Which crop
- `monitoring_date` - Observation date
- `growth_stage` - Stage (seedling/vegetative/flowering/fruiting/harvest)
- `watering_frequency` - How often watered
- `fertilizer_used` - Fertilizers applied
- `pesticide_used` - Pesticides applied
- `disease_notes` - Disease observations
- `health_status` - Overall health (excellent/good/fair/poor)

---

### 12. livestock_health (Health Records)

**Purpose:** Tracks animal health checkups

**Columns:**
- `id` - Record ID
- `livestock_id` - Which animal group
- `animal_id` - Individual animal ID
- `breed` - Breed
- `age_months` - Age
- `weight_kg` - Weight
- `vaccination_date` - Vaccination date
- `vaccination_type` - Vaccine name
- `disease_history` - Past diseases
- `medication_treatment` - Current treatment
- `checkup_date` - Checkup date
- `veterinarian_name` - Vet name
- `next_checkup_date` - Next checkup due

---

### 13. livestock_production (Production Records)

**Purpose:** Tracks daily/monthly production

**Columns:**
- `id` - Record ID
- `livestock_id` - Which animal group
- `animal_id` - Individual animal
- `production_date` - Date
- `milk_production` - Milk (liters)
- `egg_production` - Eggs (count)
- `meat_production` - Meat (kg)
- `feed_consumption` - Feed used (kg)
- `production_unit` - Unit
- `notes` - Notes

**Aggregation:** Can sum by day/week/month

---

### 14. livestock_mortality (Death Records)

**Purpose:** Tracks animal deaths

**Columns:**
- `id` - Record ID
- `livestock_id` - Which group
- `animal_id` - Individual animal
- `animal_type` - Type
- `death_date` - Date of death
- `cause` - Cause of death
- `age_at_death_months` - Age when died

---

### 15. livestock_sales (Livestock Sales)

**Purpose:** Records livestock sales

**Columns:**
- `id` - Sale ID
- `livestock_id` - Which group
- `animal_type` - Type sold
- `breed` - Breed
- `quantity` - Number sold
- `selling_price` - Sale price (Rs.)
- `purchase_cost` - Original cost (Rs.)
- `profit_loss` - Profit/loss (Rs.)
- `buyer_name` - Buyer
- `buyer_contact` - Contact
- `sale_date` - Sale date

**Calculation:** `profit_loss = selling_price - purchase_cost`

---

### 16. income (Income Records)

**Purpose:** Tracks all income sources

**Columns:**
- `id` - Income ID
- `source` - Source (crop_sales/livestock_sales/miscellaneous)
- `reference_id` - Link to sale record
- `amount` - Amount (Rs.)
- `income_date` - Date
- `description` - Description
- `payment_method` - How received

**Auto-Created:** When crop/livestock sale is recorded

---

### 17. inventory_transactions (Stock Movements)

**Purpose:** Tracks inventory in/out

**Columns:**
- `id` - Transaction ID
- `inventory_id` - Which item
- `transaction_type` - Type (in/out)
- `quantity` - Amount
- `purpose` - Why (crop activity/livestock activity/other)
- `related_module` - Which module used it
- `transaction_date` - Date
- `notes` - Notes

**Updates:** Automatically updates inventory quantity

---

## Database Relationships

```
users
  └─→ activity_log (logs user actions)

crops
  ├─→ crop_sales (sales of crops)
  ├─→ crop_production (yield data)
  └─→ crop_growth_monitoring (growth tracking)

livestock
  ├─→ livestock_health (health records)
  ├─→ livestock_production (production data)
  ├─→ livestock_mortality (death records)
  └─→ livestock_sales (sales)

inventory
  └─→ inventory_transactions (stock movements)

crop_sales ──┐
livestock_sales ──┼─→ income (all income sources)
miscellaneous ──┘
```

---

## Indexes (For Fast Queries)

Every table has indexes on:
- Primary key (id)
- Foreign keys
- Date columns (for date range queries)
- Status/type columns (for filtering)

This makes queries fast even with thousands of records!

---

## Summary

**Total Tables:** 17

**Core System:** 8 tables
**Reporting System:** 9 tables

**Total Columns:** ~150 columns across all tables

All tables use:
- `id` as primary key (auto-increment)
- `created_at` for tracking when record was created
- Proper data types (INT, VARCHAR, DECIMAL, DATE, ENUM, TEXT)
- Foreign keys for relationships
- Indexes for performance
