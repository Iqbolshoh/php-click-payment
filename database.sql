CREATE DATABASE IF NOT EXISTS click_payment;

USE click_payment;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,                      -- Payment ID
    amount DECIMAL(10, 2) NOT NULL,                         -- Payment amount
    api_trans_id VARCHAR(100) NOT NULL,                     -- External API transaction ID (e.g., Click/Payme)
    system_trans_id VARCHAR(100) NOT NULL,                  -- Internal system transaction ID
    method ENUM('click', 'payme') DEFAULT 'click',          -- Payment method
    status ENUM('unpay', 'paid') DEFAULT 'unpay',           -- Payment status
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP                -- Payment date and time
);



CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    click_trans_id VARCHAR(100) NOT NULL,
    merchant_trans_id VARCHAR(100) NOT NULL,
    status ENUM('unpay', 'paid') DEFAULT 'unpay',
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);