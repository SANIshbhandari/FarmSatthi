-- Fix existing sales tables by adding missing columns
USE farm_management;

-- Add missing columns to livestock_sales if they don't exist
ALTER TABLE livestock_sales 
ADD COLUMN IF NOT EXISTS created_by INT AFTER id,
ADD COLUMN IF NOT EXISTS breed VARCHAR(50) AFTER animal_type,
ADD COLUMN IF NOT EXISTS buyer_contact VARCHAR(100) AFTER buyer_name,
ADD COLUMN IF NOT EXISTS notes TEXT AFTER sale_date;

-- Add foreign key for created_by if it doesn't exist
ALTER TABLE livestock_sales 
ADD CONSTRAINT fk_livestock_sales_created_by 
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add index for created_by
ALTER TABLE livestock_sales 
ADD INDEX IF NOT EXISTS idx_created_by (created_by);

-- Add missing columns to crop_sales if they don't exist
ALTER TABLE crop_sales 
ADD COLUMN IF NOT EXISTS created_by INT AFTER id,
ADD COLUMN IF NOT EXISTS production_cost DECIMAL(10,2) AFTER selling_price,
ADD COLUMN IF NOT EXISTS profit_loss DECIMAL(10,2) AFTER production_cost,
ADD COLUMN IF NOT EXISTS buyer_contact VARCHAR(100) AFTER buyer_name,
ADD COLUMN IF NOT EXISTS notes TEXT AFTER sale_date;

-- Add foreign key for created_by if it doesn't exist
ALTER TABLE crop_sales 
ADD CONSTRAINT fk_crop_sales_created_by 
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add index for created_by
ALTER TABLE crop_sales 
ADD INDEX IF NOT EXISTS idx_created_by (created_by);

-- Add missing columns to income if they don't exist
ALTER TABLE income 
ADD COLUMN IF NOT EXISTS created_by INT AFTER id;

-- Add foreign key for created_by if it doesn't exist
ALTER TABLE income 
ADD CONSTRAINT fk_income_created_by 
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add index for created_by
ALTER TABLE income 
ADD INDEX IF NOT EXISTS idx_created_by (created_by);
