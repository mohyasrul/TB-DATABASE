-- Database Sistem Pemesanan Makanan Online
-- Created: June 14, 2025

CREATE DATABASE food_best;
USE food_best;

-- Tabel Users (untuk customers dan admin)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Categories untuk kategori makanan
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Menu Makanan
CREATE TABLE menu_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Tabel Keranjang Belanja
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_item (user_id, item_id)
);

-- Tabel Orders (Pesanan)
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Tabel Order Items (Detail pesanan)
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE
);

-- Tabel Order Status History (untuk tracking perubahan status)
CREATE TABLE order_status_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'),
    new_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by INT,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert data default categories
INSERT INTO categories (category_name, description) VALUES
('Makanan Utama', 'Nasi, mie, dan makanan utama lainnya'),
('Minuman', 'Minuman segar dan hangat'),
('Snack', 'Makanan ringan dan cemilan'),
('Dessert', 'Makanan penutup dan manis-manis');

-- Insert default admin user
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@foodbest.com', MD5('admin123'), 'Administrator', 'admin');

-- Insert sample menu items
INSERT INTO menu_items (item_name, description, price, category_id, is_available) VALUES
('Nasi Goreng Spesial', 'Nasi goreng dengan telur, ayam, dan sayuran', 25000, 1, TRUE),
('Mie Ayam Bakso', 'Mie ayam dengan bakso dan pangsit', 20000, 1, TRUE),
('Es Teh Manis', 'Teh manis dingin segar', 5000, 2, TRUE),
('Jus Jeruk', 'Jus jeruk segar tanpa gula', 8000, 2, TRUE),
('Keripik Singkong', 'Keripik singkong renyah', 10000, 3, TRUE),
('Es Krim Vanilla', 'Es krim vanilla premium', 15000, 4, TRUE);

-- TRIGGER untuk mencatat perubahan status pesanan
DELIMITER //
CREATE TRIGGER order_status_change_trigger
    AFTER UPDATE ON orders
    FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO order_status_history (order_id, old_status, new_status, changed_at)
        VALUES (NEW.order_id, OLD.status, NEW.status, NOW());
    END IF;
END//
DELIMITER ;

-- VIEW untuk laporan penjualan harian
CREATE VIEW daily_sales_report AS
SELECT 
    DATE(order_date) as sale_date,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value
FROM orders 
WHERE status NOT IN ('cancelled')
GROUP BY DATE(order_date)
ORDER BY sale_date DESC;

-- VIEW untuk laporan penjualan bulanan
CREATE VIEW monthly_sales_report AS
SELECT 
    YEAR(order_date) as year,
    MONTH(order_date) as month,
    MONTHNAME(order_date) as month_name,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value
FROM orders 
WHERE status NOT IN ('cancelled')
GROUP BY YEAR(order_date), MONTH(order_date)
ORDER BY year DESC, month DESC;

-- VIEW untuk laporan penjualan tahunan
CREATE VIEW yearly_sales_report AS
SELECT 
    YEAR(order_date) as year,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value
FROM orders 
WHERE status NOT IN ('cancelled')
GROUP BY YEAR(order_date)
ORDER BY year DESC;

-- VIEW untuk menu items dengan kategori
CREATE VIEW menu_with_category AS
SELECT 
    mi.item_id,
    mi.item_name,
    mi.description,
    mi.price,
    mi.image_url,
    mi.is_available,
    c.category_name,
    mi.created_at
FROM menu_items mi
LEFT JOIN categories c ON mi.category_id = c.category_id;

-- VIEW untuk order details dengan item info
CREATE VIEW order_details_view AS
SELECT 
    o.order_id,
    o.user_id,
    u.full_name as customer_name,
    u.phone as customer_phone,
    o.order_date,
    o.total_amount,
    o.status,
    o.delivery_address,
    oi.item_id,
    mi.item_name,
    oi.quantity,
    oi.price,
    oi.subtotal
FROM orders o
JOIN users u ON o.user_id = u.user_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN menu_items mi ON oi.item_id = mi.item_id;

-- STORED PROCEDURE untuk checkout (memindahkan dari cart ke order)
DELIMITER //
CREATE PROCEDURE CheckoutCart(
    IN p_user_id INT,
    IN p_delivery_address TEXT,
    IN p_phone VARCHAR(20),
    IN p_notes TEXT
)
BEGIN
    DECLARE v_order_id INT;
    DECLARE v_total_amount DECIMAL(10,2) DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Hitung total amount dari cart
    SELECT SUM(c.quantity * mi.price) INTO v_total_amount
    FROM cart c
    JOIN menu_items mi ON c.item_id = mi.item_id
    WHERE c.user_id = p_user_id;
    
    -- Buat order baru
    INSERT INTO orders (user_id, total_amount, delivery_address, phone, notes)
    VALUES (p_user_id, v_total_amount, p_delivery_address, p_phone, p_notes);
    
    SET v_order_id = LAST_INSERT_ID();
    
    -- Pindahkan items dari cart ke order_items
    INSERT INTO order_items (order_id, item_id, quantity, price, subtotal)
    SELECT v_order_id, c.item_id, c.quantity, mi.price, (c.quantity * mi.price)
    FROM cart c
    JOIN menu_items mi ON c.item_id = mi.item_id
    WHERE c.user_id = p_user_id;
    
    -- Hapus cart setelah checkout
    DELETE FROM cart WHERE user_id = p_user_id;
    
    COMMIT;
    
    SELECT v_order_id as order_id, v_total_amount as total_amount;
END//
DELIMITER ;

-- STORED PROCEDURE untuk update status order
DELIMITER //
CREATE PROCEDURE UpdateOrderStatus(
    IN p_order_id INT,
    IN p_new_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'),
    IN p_changed_by INT,
    IN p_notes TEXT
)
BEGIN
    DECLARE v_old_status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled');
    
    -- Get current status
    SELECT status INTO v_old_status FROM orders WHERE order_id = p_order_id;
    
    -- Update order status
    UPDATE orders SET status = p_new_status WHERE order_id = p_order_id;
    
    -- Insert into history (trigger will also handle this, but this is manual entry)
    INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, notes)
    VALUES (p_order_id, v_old_status, p_new_status, p_changed_by, p_notes);
END//
DELIMITER ;

-- INDEXES untuk performa yang lebih baik
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_menu_category ON menu_items(category_id);
CREATE INDEX idx_menu_available ON menu_items(is_available);
