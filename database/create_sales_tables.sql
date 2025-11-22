-- Create missing tables for sales tracking
USE farm_management;

-- Livestock Sales Table
CREATE TABLE IF NOT EXISTS livestock_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    created_by INT,
    livestock_id INT,
    animal_type VARCHAR(50) NOT NULL,
    breed VARCHAR(50),
    quantity INT NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    purchase_cost DECIMAL(10,2),
    profit_loss DECIMAL(10,2),
    buyer_name VARCHAR(100) NOT NULL,
    buyer_contact VARCHAR(100),
    sale_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES livestock(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sale_date (sale_date),
    INDEX idx_animal_type (animal_type),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crop Sales Table
CREATE TABLE IF NOT EXISTS crop_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    created_by INT,
    crop_id INT,
    crop_name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    production_cost DECIMAL(10,2),
    profit_loss DECIMAL(10,2),
    buyer_name VARCHAR(100) NOT NULL,
    buyer_contact VARCHAR(100),
    sale_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sale_date (sale_date),
    INDEX idx_crop_name (crop_name),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Income Table
CREATE TABLE IF NOT EXISTS income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    created_by INT,
    source ENUM('crop_sales', 'livestock_sales', 'miscellaneous') NOT NULL,
    reference_id INT,
    amount DECIMAL(10,2) NOT NULL,
    income_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_source (source),
    INDEX idx_income_date (income_date),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
