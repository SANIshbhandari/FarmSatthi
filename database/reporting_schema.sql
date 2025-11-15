-- Advanced Reporting System Database Schema Extensions
-- This file adds tables needed for comprehensive farm reporting

USE farm_management;

-- Crop Sales Table
CREATE TABLE IF NOT EXISTS crop_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    quantity_sold DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'kg',
    rate_per_unit DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    buyer_name VARCHAR(100) NOT NULL,
    buyer_contact VARCHAR(20),
    sale_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    INDEX idx_sale_date (sale_date),
    INDEX idx_crop_id (crop_id),
    INDEX idx_crop_name (crop_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crop Production Details Table
CREATE TABLE IF NOT EXISTS crop_production (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    expected_yield DECIMAL(10,2),
    actual_yield DECIMAL(10,2),
    yield_unit VARCHAR(20) DEFAULT 'kg',
    production_cost DECIMAL(10,2) DEFAULT 0,
    profit DECIMAL(10,2),
    harvest_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    INDEX idx_crop_id (crop_id),
    INDEX idx_harvest_date (harvest_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crop Growth Monitoring Table
CREATE TABLE IF NOT EXISTS crop_growth_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    monitoring_date DATE NOT NULL,
    growth_stage ENUM('seedling', 'vegetative', 'flowering', 'fruiting', 'harvest') NOT NULL,
    watering_frequency VARCHAR(50),
    fertilizer_used VARCHAR(200),
    pesticide_used VARCHAR(200),
    disease_notes TEXT,
    health_status ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    INDEX idx_crop_id (crop_id),
    INDEX idx_monitoring_date (monitoring_date),
    INDEX idx_growth_stage (growth_stage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Livestock Health Records Table
CREATE TABLE IF NOT EXISTS livestock_health (
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
    veterinarian_name VARCHAR(100),
    next_checkup_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE CASCADE,
    INDEX idx_livestock_id (livestock_id),
    INDEX idx_checkup_date (checkup_date),
    INDEX idx_vaccination_date (vaccination_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Livestock Production Table
CREATE TABLE IF NOT EXISTS livestock_production (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Livestock Mortality Table
CREATE TABLE IF NOT EXISTS livestock_mortality (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livestock_id INT,
    animal_id VARCHAR(50),
    animal_type VARCHAR(50) NOT NULL,
    death_date DATE NOT NULL,
    cause TEXT,
    age_at_death_months INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL,
    INDEX idx_death_date (death_date),
    INDEX idx_animal_type (animal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Livestock Sales Table
CREATE TABLE IF NOT EXISTS livestock_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livestock_id INT,
    animal_type VARCHAR(50) NOT NULL,
    breed VARCHAR(50),
    quantity INT NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    purchase_cost DECIMAL(10,2),
    profit_loss DECIMAL(10,2),
    buyer_name VARCHAR(100) NOT NULL,
    buyer_contact VARCHAR(20),
    sale_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL,
    INDEX idx_sale_date (sale_date),
    INDEX idx_animal_type (animal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Income Table
CREATE TABLE IF NOT EXISTS income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source ENUM('crop_sales', 'livestock_sales', 'miscellaneous') NOT NULL,
    reference_id INT,
    amount DECIMAL(10,2) NOT NULL,
    income_date DATE NOT NULL,
    description TEXT,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'online') DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source),
    INDEX idx_income_date (income_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory Transactions Table
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inventory_id INT NOT NULL,
    transaction_type ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    purpose VARCHAR(200),
    related_module ENUM('crops', 'livestock', 'equipment', 'other') DEFAULT 'other',
    transaction_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE,
    INDEX idx_inventory_id (inventory_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_transaction_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
