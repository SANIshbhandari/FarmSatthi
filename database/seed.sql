-- Farm Management System - Sample Data
-- This file contains sample data for testing and demonstration

USE farm_management;

-- Note: Initial admin user should be created through the registration page
-- This ensures proper password hashing

-- Sample Crops Data
INSERT INTO crops (crop_name, crop_type, planting_date, expected_harvest, field_location, area_hectares, status, notes) VALUES
('Winter Wheat', 'Wheat', '2024-10-15', '2025-06-20', 'North Field A', 25.50, 'active', 'High yield variety'),
('Corn Field 1', 'Corn', '2024-04-10', '2024-09-15', 'South Field B', 30.00, 'harvested', 'Good harvest'),
('Soybeans', 'Soybean', '2024-05-01', '2024-10-30', 'East Field C', 20.75, 'active', 'Organic certified'),
('Spring Barley', 'Barley', '2024-03-20', '2024-08-10', 'West Field D', 15.00, 'harvested', 'Used for feed'),
('Potatoes', 'Potato', '2024-04-15', '2024-09-20', 'North Field B', 10.50, 'active', 'Red variety'),
('Tomatoes', 'Tomato', '2024-05-10', '2024-08-30', 'Greenhouse 1', 2.00, 'active', 'Cherry tomatoes'),
('Lettuce', 'Lettuce', '2024-06-01', '2024-07-15', 'Greenhouse 2', 1.50, 'active', 'Mixed varieties'),
('Carrots', 'Carrot', '2024-04-20', '2024-09-10', 'East Field A', 5.00, 'active', 'Orange variety'),
('Onions', 'Onion', '2024-03-15', '2024-08-20', 'South Field A', 8.00, 'harvested', 'Yellow onions'),
('Pumpkins', 'Pumpkin', '2024-06-10', '2024-10-25', 'West Field A', 12.00, 'active', 'For market');

-- Sample Livestock Data
INSERT INTO livestock (animal_type, breed, count, age_months, health_status, purchase_date, current_value, notes) VALUES
('Cattle', 'Angus', 50, 24, 'healthy', '2023-01-15', 75000.00, 'Beef cattle'),
('Cattle', 'Holstein', 30, 36, 'healthy', '2022-06-20', 45000.00, 'Dairy cattle'),
('Sheep', 'Merino', 100, 18, 'healthy', '2023-03-10', 15000.00, 'Wool production'),
('Pigs', 'Yorkshire', 25, 12, 'healthy', '2023-09-05', 8750.00, 'Market ready soon'),
('Chickens', 'Rhode Island Red', 200, 8, 'healthy', '2024-02-01', 2000.00, 'Egg layers'),
('Goats', 'Boer', 15, 20, 'healthy', '2023-05-15', 4500.00, 'Meat goats'),
('Horses', 'Quarter Horse', 5, 60, 'healthy', '2020-08-10', 25000.00, 'Working horses'),
('Cattle', 'Hereford', 20, 30, 'under_treatment', '2022-11-20', 28000.00, 'Minor illness, recovering'),
('Sheep', 'Suffolk', 40, 24, 'healthy', '2022-12-05', 6000.00, 'Meat production'),
('Ducks', 'Pekin', 50, 6, 'healthy', '2024-03-15', 500.00, 'Egg and meat');

-- Sample Equipment Data
INSERT INTO equipment (equipment_name, type, purchase_date, last_maintenance, next_maintenance, `condition`, value, notes) VALUES
('John Deere 8R Tractor', 'Tractor', '2020-05-15', '2024-10-01', '2025-04-01', 'excellent', 250000.00, 'Primary field tractor'),
('Case IH Combine', 'Harvester', '2019-08-20', '2024-09-15', '2025-03-15', 'good', 350000.00, 'Grain harvester'),
('New Holland Baler', 'Baler', '2021-03-10', '2024-08-20', '2025-02-20', 'good', 45000.00, 'Round baler'),
('Kubota Utility Tractor', 'Tractor', '2022-06-05', '2024-11-01', '2025-05-01', 'excellent', 35000.00, 'Utility work'),
('Sprayer System', 'Sprayer', '2020-04-12', '2024-10-10', '2024-12-10', 'fair', 28000.00, 'Needs minor repairs'),
('Grain Trailer', 'Trailer', '2018-09-25', '2024-07-15', '2025-07-15', 'good', 15000.00, '30-ton capacity'),
('Fertilizer Spreader', 'Spreader', '2021-02-18', '2024-09-01', '2025-03-01', 'good', 12000.00, 'Broadcast spreader'),
('Irrigation System', 'Irrigation', '2019-05-30', '2024-06-15', '2025-06-15', 'excellent', 85000.00, 'Center pivot'),
('Pickup Truck', 'Vehicle', '2022-01-10', '2024-10-20', '2025-04-20', 'excellent', 45000.00, 'Ford F-250'),
('ATV', 'Vehicle', '2023-03-15', '2024-09-10', '2025-03-10', 'good', 12000.00, 'Farm utility vehicle');

-- Sample Employee Data
INSERT INTO employees (name, role, phone, email, salary, hire_date, status, notes) VALUES
('John Smith', 'Farm Manager', '555-0101', 'john.smith@farm.com', 65000.00, '2020-01-15', 'active', 'Experienced manager'),
('Mary Johnson', 'Assistant Manager', '555-0102', 'mary.j@farm.com', 52000.00, '2020-06-01', 'active', 'Great with livestock'),
('Robert Brown', 'Equipment Operator', '555-0103', 'rbrown@farm.com', 45000.00, '2021-03-10', 'active', 'Certified operator'),
('Sarah Davis', 'Field Worker', '555-0104', 'sdavis@farm.com', 35000.00, '2021-08-20', 'active', 'Crop specialist'),
('Michael Wilson', 'Field Worker', '555-0105', 'mwilson@farm.com', 35000.00, '2022-01-15', 'active', 'Reliable worker'),
('Jennifer Martinez', 'Livestock Manager', '555-0106', 'jmartinez@farm.com', 48000.00, '2020-09-01', 'active', 'Veterinary background'),
('David Anderson', 'Maintenance Tech', '555-0107', 'danderson@farm.com', 42000.00, '2021-05-10', 'active', 'Equipment specialist'),
('Lisa Taylor', 'Office Administrator', '555-0108', 'ltaylor@farm.com', 40000.00, '2020-03-15', 'active', 'Handles paperwork');

-- Sample Expense Data
INSERT INTO expenses (category, amount, expense_date, description, payment_method) VALUES
('Feed', 5500.00, '2024-11-01', 'Cattle feed - 10 tons', 'bank_transfer'),
('Fuel', 3200.00, '2024-11-05', 'Diesel fuel for equipment', 'credit_card'),
('Seeds', 8500.00, '2024-10-15', 'Winter wheat seeds', 'check'),
('Fertilizer', 6200.00, '2024-10-20', 'NPK fertilizer - 5 tons', 'bank_transfer'),
('Maintenance', 1850.00, '2024-11-03', 'Tractor oil change and service', 'credit_card'),
('Veterinary', 950.00, '2024-11-08', 'Cattle health check and vaccinations', 'check'),
('Utilities', 1200.00, '2024-11-01', 'Electricity and water', 'bank_transfer'),
('Insurance', 2500.00, '2024-11-01', 'Monthly farm insurance', 'bank_transfer'),
('Supplies', 450.00, '2024-11-06', 'General farm supplies', 'cash'),
('Feed', 4800.00, '2024-10-15', 'Chicken and pig feed', 'bank_transfer'),
('Repairs', 2200.00, '2024-10-25', 'Barn roof repair', 'check'),
('Seeds', 3500.00, '2024-09-20', 'Vegetable seeds for greenhouse', 'credit_card'),
('Labor', 12000.00, '2024-11-01', 'Seasonal worker wages', 'bank_transfer'),
('Equipment', 850.00, '2024-11-04', 'New tools and equipment', 'credit_card'),
('Marketing', 600.00, '2024-11-02', 'Farmers market fees', 'cash');

-- Sample Inventory Data
INSERT INTO inventory (item_name, category, quantity, unit, reorder_level) VALUES
('Cattle Feed', 'Feed', 2500.00, 'kg', 500.00),
('Chicken Feed', 'Feed', 800.00, 'kg', 200.00),
('Fertilizer NPK', 'Fertilizer', 1200.00, 'kg', 300.00),
('Diesel Fuel', 'Fuel', 5000.00, 'liters', 1000.00),
('Gasoline', 'Fuel', 800.00, 'liters', 200.00),
('Wheat Seeds', 'Seeds', 150.00, 'kg', 50.00),
('Corn Seeds', 'Seeds', 200.00, 'kg', 50.00),
('Pesticide A', 'Chemicals', 45.00, 'liters', 10.00),
('Herbicide B', 'Chemicals', 30.00, 'liters', 10.00),
('Veterinary Supplies', 'Medical', 25.00, 'units', 10.00),
('Work Gloves', 'Safety', 50.00, 'pairs', 20.00),
('Safety Boots', 'Safety', 15.00, 'pairs', 5.00),
('Baling Twine', 'Supplies', 100.00, 'rolls', 20.00),
('Fence Wire', 'Supplies', 500.00, 'meters', 100.00),
('Hand Tools', 'Tools', 30.00, 'pieces', 10.00),
('Irrigation Pipes', 'Equipment', 200.00, 'meters', 50.00),
('Greenhouse Film', 'Supplies', 80.00, 'meters', 20.00),
('Organic Compost', 'Fertilizer', 3000.00, 'kg', 500.00),
('Mineral Supplements', 'Feed', 150.00, 'kg', 30.00),
('Cleaning Supplies', 'Supplies', 25.00, 'units', 10.00);

-- Display success message
SELECT 'Sample data inserted successfully!' as Message;
