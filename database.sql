CREATE DATABASE IF NOT EXISTS click;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    click_trans_id VARCHAR(100) NOT NULL,
    merchant_trans_id VARCHAR(100) NOT NULL,
    status ENUM('unpay', 'paid') DEFAULT 'unpay'
);