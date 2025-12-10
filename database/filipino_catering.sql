-- Filipino Catering Database Schema
-- For XAMPP MySQL

CREATE DATABASE IF NOT EXISTS filipino_catering;
USE filipino_catering;

-- Users Table (Customers, Staff, Admin, Super Admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'staff', 'admin', 'super_admin') DEFAULT 'customer',
    profile_image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email Verifications Table
CREATE TABLE email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
);

-- Password Resets Table
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);

-- Menu Categories
CREATE TABLE menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE SET NULL
);

-- Packages
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    min_pax INT DEFAULT 50,
    max_pax INT DEFAULT 500,
    image VARCHAR(255),
    inclusions TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Package Menu Items (which items are included in packages)
CREATE TABLE package_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT,
    menu_item_id INT,
    quantity INT DEFAULT 1,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Bookings
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT,
    package_id INT,
    event_type VARCHAR(100),
    event_date DATE NOT NULL,
    event_time TIME,
    venue_address TEXT NOT NULL,
    number_of_guests INT NOT NULL,
    special_requests TEXT,
    total_amount DECIMAL(12,2),
    status ENUM('new', 'pending', 'negotiating', 'approved', 'paid', 'preparing', 'completed', 'cancelled') DEFAULT 'new',
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    payment_method VARCHAR(50),
    assigned_staff_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_staff_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Custom Menu Selections for Bookings
CREATE TABLE booking_menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    menu_item_id INT,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Chat Messages
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    sender_id INT,
    message TEXT,
    image VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    proof_image VARCHAR(255),
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    notes TEXT,
    processed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    message TEXT,
    type VARCHAR(50),
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity Logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Promotions
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Super Admin (password: admin123)
INSERT INTO users (email, password, first_name, last_name, phone, role) VALUES 
('admin@filipinocatering.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Admin', '09123456789', 'super_admin');

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'Kusina ni Maria - Filipino Catering'),
('site_email', 'info@filipinocatering.com'),
('site_phone', '09123456789'),
('site_address', 'Manila, Philippines'),
('minimum_advance_booking', '3'),
('down_payment_percentage', '50');

-- Insert Sample Menu Categories
INSERT INTO menu_categories (name, description) VALUES 
('Pampagana (Appetizers)', 'Traditional Filipino appetizers and finger foods'),
('Sopas (Soups)', 'Warm and hearty Filipino soups'),
('Karne (Meat Dishes)', 'Classic Filipino meat dishes'),
('Seafood', 'Fresh and flavorful seafood dishes'),
('Gulay (Vegetables)', 'Healthy Filipino vegetable dishes'),
('Pancit & Rice', 'Noodles and rice dishes'),
('Panghimagas (Desserts)', 'Sweet Filipino desserts'),
('Inumin (Beverages)', 'Refreshing Filipino drinks');

-- Insert Sample Menu Items
INSERT INTO menu_items (category_id, name, description, price, is_featured) VALUES 
(1, 'Lumpiang Shanghai', 'Crispy Filipino spring rolls with pork filling', 350.00, 1),
(1, 'Tokwa\'t Baboy', 'Fried tofu and pork ears with soy vinegar sauce', 280.00, 0),
(1, 'Chicharon Bulaklak', 'Deep-fried pork mesentery, crispy and savory', 320.00, 0),
(2, 'Sinigang na Baboy', 'Pork in sour tamarind soup with vegetables', 450.00, 1),
(2, 'Bulalo', 'Beef shank and bone marrow soup', 550.00, 1),
(2, 'Tinolang Manok', 'Chicken ginger soup with papaya', 380.00, 0),
(3, 'Lechon Kawali', 'Crispy deep-fried pork belly', 480.00, 1),
(3, 'Adobong Baboy', 'Pork braised in soy sauce and vinegar', 380.00, 1),
(3, 'Caldereta', 'Beef stew in tomato-based sauce', 520.00, 1),
(3, 'Kare-Kare', 'Oxtail stew in peanut sauce', 580.00, 1),
(3, 'Bistek Tagalog', 'Filipino beef steak with onions', 450.00, 0),
(3, 'Menudo', 'Pork and liver stew with potatoes', 380.00, 0),
(4, 'Inihaw na Bangus', 'Grilled stuffed milkfish', 420.00, 1),
(4, 'Sinigang na Hipon', 'Shrimp in sour soup', 480.00, 0),
(4, 'Ginataang Hipon', 'Shrimp in coconut milk', 450.00, 0),
(4, 'Sweet and Sour Fish', 'Fried fish with sweet and sour sauce', 380.00, 0),
(5, 'Pinakbet', 'Mixed vegetables in shrimp paste', 280.00, 1),
(5, 'Ginataang Kalabasa', 'Squash in coconut milk', 250.00, 0),
(5, 'Laing', 'Taro leaves in coconut milk', 280.00, 0),
(5, 'Ensaladang Talong', 'Grilled eggplant salad', 220.00, 0),
(6, 'Pancit Canton', 'Stir-fried egg noodles with vegetables', 320.00, 1),
(6, 'Pancit Bihon', 'Rice noodles with meat and vegetables', 300.00, 0),
(6, 'Java Rice', 'Filipino garlic fried rice', 150.00, 0),
(6, 'Steamed Rice', 'Plain steamed rice', 80.00, 0),
(7, 'Leche Flan', 'Caramel custard dessert', 250.00, 1),
(7, 'Buko Pandan', 'Coconut and pandan gelatin dessert', 220.00, 1),
(7, 'Halo-Halo', 'Mixed shaved ice dessert', 180.00, 0),
(7, 'Bibingka', 'Rice cake with salted egg and cheese', 200.00, 0),
(8, 'Sago\'t Gulaman', 'Tapioca pearls and jelly drink', 80.00, 1),
(8, 'Buko Juice', 'Fresh coconut juice', 100.00, 0),
(8, 'Calamansi Juice', 'Filipino lemonade', 70.00, 0);

-- Insert Sample Packages
INSERT INTO packages (name, description, base_price, min_pax, max_pax, inclusions) VALUES 
('Fiesta Package', 'Perfect for birthday celebrations and small gatherings. Includes 5 main dishes, 2 desserts, rice, and drinks.', 399.00, 30, 100, 'Complete table setup, Serving utensils, Chafing dishes, Wait staff (1 per 25 guests)'),
('Handaan Package', 'Ideal for medium-sized events. Includes 7 main dishes, 2 soups, 3 desserts, rice, and drinks.', 549.00, 50, 200, 'Complete table setup, Serving utensils, Chafing dishes, Wait staff (1 per 20 guests), Basic decoration'),
('Salo-Salo Package', 'Our premium package for large celebrations. Includes 10 main dishes, 3 soups, 4 desserts, rice, and unlimited drinks.', 749.00, 100, 500, 'Complete table and venue setup, Serving utensils, Chafing dishes, Wait staff (1 per 15 guests), Premium decoration, Lechon (1 per 100 guests)'),
('Kasalan Package', 'Special wedding catering package with customizable menu.', 899.00, 100, 500, 'Complete venue styling, Premium table setup, Dedicated wedding coordinator, Champagne toast, Wedding cake, Wait staff, Full bar service'),
('Corporate Package', 'Professional catering for corporate events and meetings.', 499.00, 20, 300, 'Buffet setup, Name tags, Meeting supplies, Coffee break inclusions, Professional service');