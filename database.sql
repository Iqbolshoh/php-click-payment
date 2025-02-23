CREATE DATABASE IF NOT EXISTS payment;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    click_trans_id VARCHAR(100) NOT NULL,
    merchant_trans_id VARCHAR(100) NOT NULL,
    status ENUM('unpay', 'paid') DEFAULT 'unpay',
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    provider_trans_id VARCHAR(100) NOT NULL,
    internal_order_id VARCHAR(100) NOT NULL,
    status ENUM('unpay', 'paid') DEFAULT 'unpay',
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method ENUM('click', 'payme') DEFAULT 'click'
);