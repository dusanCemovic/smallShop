DROP DATABASE IF EXISTS creatim;
CREATE DATABASE IF NOT EXISTS creatim CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE creatim;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(30) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    supplier_email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE TABLE IF NOT EXISTS subscription_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    includes_physical_magazine TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(100) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    status ENUM('pending','paid','cancelled','processing','completed') NOT NULL DEFAULT 'pending',
    articles JSON NULL CHECK (JSON_VALID(articles)),
    subscription_package_id INT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_package_id) REFERENCES subscription_packages(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) NOT NULL,
    to_phone VARCHAR(30) NOT NULL,
    content TEXT NOT NULL,
    success TINYINT(1) NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

