-- Converted for PostgreSQL

CREATE TABLE brands (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE expos (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'Active' CHECK (status IN ('Active', 'Finished'))
);

INSERT INTO expos (id, name, start_date, end_date, status) VALUES
(1, 'Main Launch Event', '2025-08-11', NULL, 'Finished');

CREATE TABLE suppliers (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  contact_person VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL
);

CREATE TABLE product_types (
  id SERIAL PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE variants (
  id SERIAL PRIMARY KEY,
  type_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  FOREIGN KEY (type_id) REFERENCES product_types(id) ON DELETE CASCADE
);

CREATE TABLE products (
  id SERIAL PRIMARY KEY,
  category_id INT DEFAULT NULL,
  type_id INT DEFAULT NULL,
  variant_id INT DEFAULT NULL,
  brand_id INT DEFAULT NULL,
  supplier_id INT DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  mrp DECIMAL(10,2) DEFAULT 0.00,
  sales_price DECIMAL(10,2) NOT NULL,
  supplier_price DECIMAL(10,2) DEFAULT 0.00,
  min_stock INT DEFAULT 0,
  requires_serial BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (type_id) REFERENCES product_types(id) ON DELETE SET NULL,
  FOREIGN KEY (variant_id) REFERENCES variants(id) ON DELETE SET NULL,
  FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

CREATE TABLE stock (
  id SERIAL PRIMARY KEY,
  product_id INT NOT NULL UNIQUE,
  quantity INT NOT NULL DEFAULT 0,
  last_updated TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE serial_numbers (
  id SERIAL PRIMARY KEY,
  product_id INT NOT NULL,
  serial_no VARCHAR(255) NOT NULL UNIQUE,
  status VARCHAR(50) DEFAULT 'Available' CHECK (status IN ('Available', 'In Expo', 'Sold', 'Removed')),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE expo_stock (
  id SERIAL PRIMARY KEY,
  expo_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  serial_numbers_json JSONB DEFAULT NULL,
  FOREIGN KEY (expo_id) REFERENCES expos(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE expo_bills (
  id SERIAL PRIMARY KEY,
  expo_id INT NOT NULL,
  bill_no VARCHAR(50) NOT NULL UNIQUE,
  bill_date TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (expo_id) REFERENCES expos(id) ON DELETE CASCADE
);

CREATE TABLE expo_bill_items (
  id SERIAL PRIMARY KEY,
  bill_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price_per_item DECIMAL(10,2) NOT NULL,
  serial_no_list_json JSONB DEFAULT NULL,
  FOREIGN KEY (bill_id) REFERENCES expo_bills(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Trigger to auto-update last_updated in stock table
CREATE OR REPLACE FUNCTION update_last_updated_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.last_updated = NOW(); 
   RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_stock_modtime
BEFORE UPDATE ON stock
FOR EACH ROW
EXECUTE FUNCTION update_last_updated_column();
