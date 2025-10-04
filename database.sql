-- ===========================================
-- Drop and recreate database
-- ===========================================
DROP DATABASE IF EXISTS medicine_inventory;
CREATE DATABASE IF NOT EXISTS medicine_inventory;
USE medicine_inventory;

-- ===========================================
-- USERS TABLE
-- ===========================================
DROP TABLE IF EXISTS users;

CREATE TABLE users (
                       id INT PRIMARY KEY AUTO_INCREMENT,
                       name VARCHAR(255) NOT NULL,
                       username VARCHAR(100) UNIQUE NOT NULL,
                       email VARCHAR(255) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       phone VARCHAR(20),
                       profile_pic VARCHAR(255),
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_username ON users(username);

-- ===========================================
-- CATEGORIES TABLE
-- ===========================================
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            name VARCHAR(255) NOT NULL,
                            description VARCHAR(255),
                            user_id INT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                            CONSTRAINT fk_category_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                            CONSTRAINT unique_category_per_user UNIQUE (user_id, name)
);

-- Indexes
CREATE INDEX idx_category_name ON categories(name);
CREATE INDEX idx_user_categories ON categories(user_id);

-- ===========================================
-- MEDICINES TABLE
-- ===========================================
DROP TABLE IF EXISTS medicines;

CREATE TABLE medicines (
                           id INT PRIMARY KEY AUTO_INCREMENT,
                           name VARCHAR(255) NOT NULL,
                           category VARCHAR(255),           -- added back for PHP compatibility
                           category_id INT,                 -- keep relational link for future
                           box_id VARCHAR(255),
                           quality VARCHAR(255),
                           image VARCHAR(255) DEFAULT 'default.png',
                           quantity INT,
                           expiry_date DATE,
                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                           CONSTRAINT fk_medicine_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
#  OPTIONAL
# -- ===========================================
# -- SAMPLE DATA (optional for testing)
# -- ===========================================
#
# INSERT INTO categories (name, description, user_id)
# VALUES ('Pain Relief', 'Medicines for pain and fever', 1),
#        ('Antibiotics', 'Medicines for bacterial infections', 1);
#
# INSERT INTO medicines (name, category, category_id, box_id, quality, quantity, expiry_date)
# VALUES
#     ('Paracetamol', 'Pain Relief', 1, 'BOX001', 'High', 100, '2026-12-31'),
#     ('Amoxicillin', 'Antibiotics', 2, 'BOX002', 'Medium', 50, '2025-10-10');
