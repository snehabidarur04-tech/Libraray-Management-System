-- Library Management System Database
-- Run this SQL file in phpMyAdmin to create the database

CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address VARCHAR(255),
    role ENUM('admin', 'student', 'teacher') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Books Table
CREATE TABLE IF NOT EXISTS books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    publisher VARCHAR(100),
    category VARCHAR(50),
    quantity INT DEFAULT 1,
    available INT DEFAULT 1,
    price DECIMAL(10,2),
    description TEXT,
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Issue/Return Table
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    fine DECIMAL(10,2) DEFAULT 0.00,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, phone, role, status) VALUES 
('Admin', 'admin@library.com', '1234567890', 'admin', 'active');

-- Insert sample books
INSERT INTO books (isbn, title, author, publisher, category, quantity, available, price) VALUES 
('978-0-13-468599-1', 'Introduction to PHP', 'John Smith', 'Tech Press', 'Programming', 5, 5, 29.99),
('978-0-13-235088-4', 'MySQL Mastery', 'Jane Doe', 'DB Publishing', 'Database', 3, 3, 34.99),
('978-0-59-651798-1', 'Web Development Guide', 'Bob Wilson', 'Web Books', 'Web', 4, 4, 24.99),
('978-1-23-456789-0', 'JavaScript Essentials', 'Alice Brown', 'Code Press', 'Programming', 6, 6, 39.99),
('978-1-56-584709-2', 'CSS Design Patterns', 'Charlie Davis', 'Style Books', 'Design', 3, 3, 27.99);
