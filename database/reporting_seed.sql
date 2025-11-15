-- Sample data for Advanced Reporting System
-- This file provides test data for all reporting tables

USE farm_management;

-- Sample Crop Production Data
INSERT INTO crop_production (crop_id, expected_yield, actual_yield, yield_unit, production_cost, profit, harvest_date) VALUES
(1, 5000, 4800, 'kg', 15000, 33000, '2024-11-01'),
(2, 3000, 3200, 'kg', 12000, 20000, '2024-10-15');

-- Sample Crop Growth Monitoring Data
INSERT INTO crop_growth_monitoring (crop_id, monitoring_date, growth_stage, watering_frequency, fertilizer_used, pesticide_used, disease_notes, health_status) VALUES
(1, '2024-09-15', 'vegetative', 'Daily', 'NPK 20-20-20', 'None', 'Healthy growth', 'excellent'),
(1, '2024-10-01', 'flowering', 'Daily', 'Phosphorus boost', 'Fungicide applied', 'Minor leaf spot detected', 'good'),
(2, '2024-08-20', 'seedling', 'Twice daily', 'Starter fertilizer', 'None', 'Good germination', 'excellent');

-- Sample Crop Sales Data
INSERT INTO crop_sales (crop_id, crop_name, quantity_sold, unit, rate_per_unit, total_price, buyer_name, buyer_contact, sale_date) VALUES
(1, 'Wheat', 4000, 'kg', 12.00, 48000, 'ABC Traders', '9876543210', '2024-11-05'),
(1, 'Wheat', 800, 'kg', 12.00, 9600, 'XYZ Mills', '9876543211', '2024-11-06'),
(2, 'Rice', 3000, 'kg', 15.00, 45000, 'Rice Distributors Ltd', '9876543212', '2024-10-20');

-- Sample Livestock Health Records
INSERT INTO livestock_health (livestock_id, animal_id, breed, age_months, weight_kg, vaccination_date, vaccination_type, disease_history, medication_treatment, checkup_date, veterinarian_name, next_checkup_date) VALUES
(1, 'COW-001', 'Holstein', 36, 550, '2024-10-01', 'FMD Vaccine', 'None', 'Routine checkup', '2024-10-15', 'Dr. Sharma', '2025-01-15'),
(1, 'COW-002', 'Holstein', 24, 480, '2024-10-01', 'FMD Vaccine', 'Mastitis treated in Aug', 'Antibiotics completed', '2024-10-15', 'Dr. Sharma', '2025-01-15'),
(2, 'GOAT-001', 'Boer', 18, 45, '2024-09-20', 'PPR Vaccine', 'None', 'Healthy', '2024-10-10', 'Dr. Kumar', '2025-01-10');

-- Sample Livestock Production Data
INSERT INTO livestock_production (livestock_id, animal_id, production_date, milk_production, egg_production, meat_production, feed_consumption, production_unit, notes) VALUES
(1, 'COW-001', '2024-11-01', 25.5, 0, 0, 15, 'liters', 'Good milk quality'),
(1, 'COW-001', '2024-11-02', 26.0, 0, 0, 15, 'liters', 'Good milk quality'),
(1, 'COW-001', '2024-11-03', 24.8, 0, 0, 15, 'liters', 'Slight decrease'),
(1, 'COW-002', '2024-11-01', 22.0, 0, 0, 14, 'liters', 'Normal production'),
(1, 'COW-002', '2024-11-02', 23.5, 0, 0, 14, 'liters', 'Improving'),
(2, 'GOAT-001', '2024-11-01', 1.5, 0, 0, 3, 'liters', 'Good quality milk');

-- Sample Livestock Sales Data
INSERT INTO livestock_sales (livestock_id, animal_type, breed, quantity, selling_price, purchase_cost, profit_loss, buyer_name, buyer_contact, sale_date, notes) VALUES
(2, 'Goat', 'Boer', 2, 25000, 18000, 7000, 'Local Butcher', '9876543220', '2024-10-25', 'Sold for meat'),
(3, 'Chicken', 'Broiler', 50, 15000, 8000, 7000, 'Poultry Shop', '9876543221', '2024-11-01', 'Bulk sale');

-- Sample Livestock Mortality Data
INSERT INTO livestock_mortality (livestock_id, animal_id, animal_type, death_date, cause, age_at_death_months) VALUES
(3, 'CHICK-045', 'Chicken', '2024-10-20', 'Disease outbreak', 3);

-- Sample Income Data
INSERT INTO income (source, reference_id, amount, income_date, description, payment_method) VALUES
('crop_sales', 1, 48000, '2024-11-05', 'Wheat sale to ABC Traders', 'bank_transfer'),
('crop_sales', 2, 9600, '2024-11-06', 'Wheat sale to XYZ Mills', 'cash'),
('crop_sales', 3, 45000, '2024-10-20', 'Rice sale to distributors', 'bank_transfer'),
('livestock_sales', 1, 25000, '2024-10-25', 'Goat sale', 'cash'),
('livestock_sales', 2, 15000, '2024-11-01', 'Chicken bulk sale', 'cash'),
('miscellaneous', NULL, 5000, '2024-10-15', 'Equipment rental income', 'cash'),
('miscellaneous', NULL, 3000, '2024-11-10', 'Consultation fees', 'online');

-- Sample Expense Data
INSERT INTO expenses (category, amount, expense_date, description, payment_method) VALUES
('Feed', 5500, '2024-11-01', 'Cattle feed - 10 tons', 'bank_transfer'),
('Fuel', 3200, '2024-11-05', 'Diesel fuel for equipment', 'credit_card'),
('Seeds', 8000, '2024-10-01', 'Wheat seeds for planting', 'bank_transfer'),
('Fertilizer', 12000, '2024-10-10', 'NPK fertilizer bulk purchase', 'bank_transfer'),
('Labor', 15000, '2024-10-15', 'Harvest labor wages', 'cash'),
('Veterinary', 2500, '2024-10-20', 'Livestock health checkup', 'cash'),
('Maintenance', 4500, '2024-10-25', 'Tractor repair and maintenance', 'credit_card'),
('Utilities', 3000, '2024-11-01', 'Electricity and water bills', 'bank_transfer'),
('Pesticides', 2800, '2024-10-05', 'Crop protection chemicals', 'cash'),
('Insurance', 6000, '2024-10-01', 'Farm insurance premium', 'bank_transfer');

-- Sample Inventory Transactions Data
INSERT INTO inventory_transactions (inventory_id, transaction_type, quantity, purpose, related_module, transaction_date, notes) VALUES
(1, 'in', 500, 'New stock purchase', 'other', '2024-10-01', 'Bulk purchase for season'),
(1, 'out', 50, 'Used for wheat crop', 'crops', '2024-10-05', 'Applied to field A'),
(1, 'out', 30, 'Used for rice crop', 'crops', '2024-10-10', 'Applied to field B'),
(2, 'in', 200, 'Stock replenishment', 'other', '2024-09-25', 'Regular purchase'),
(2, 'out', 25, 'Livestock feed supplement', 'livestock', '2024-10-15', 'Added to cattle feed'),
(3, 'in', 100, 'New medicine stock', 'other', '2024-10-20', 'Veterinary supplies'),
(3, 'out', 5, 'Treatment for sick animals', 'livestock', '2024-10-22', 'Used for goat treatment');
