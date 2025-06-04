-- Drop tables if they exist to avoid conflicts (optional, use with caution)
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS payments;

-- Create the payments table with a UNIQUE constraint on payment_id
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    gotra VARCHAR(100),
    address TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_id VARCHAR(100) NOT NULL UNIQUE, -- Added UNIQUE constraint
    payment_status ENUM('complete', 'failed') DEFAULT 'failed',
    payment_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create the cart_items table with a foreign key
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(100) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE CASCADE
) ENGINE=InnoDB;