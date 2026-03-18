-- FoodZone Database Setup
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS foodzone;
USE foodzone;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(10) DEFAULT '🍽️'
);

-- Food items table
CREATE TABLE IF NOT EXISTS foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    emoji VARCHAR(10) DEFAULT '🍕',
    rating DECIMAL(2,1) DEFAULT 4.5,
    prep_time INT DEFAULT 20,
    is_popular TINYINT(1) DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','preparing','out_for_delivery','delivered') DEFAULT 'pending',
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (food_id) REFERENCES foods(id)
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (food_id) REFERENCES foods(id)
);

-- Seed categories
INSERT INTO categories (name, icon) VALUES
('Burgers', '🍔'),
('Pizza', '🍕'),
('Sushi', '🍣'),
('Pasta', '🍝'),
('Desserts', '🍰'),
('Drinks', '🥤');

-- Seed food items
INSERT INTO foods (category_id, name, description, price, emoji, rating, prep_time, is_popular) VALUES
(1, 'Classic Smash Burger', 'Double smash patty, cheddar, pickles, special sauce', 12.99, '🍔', 4.8, 15, 1),
(1, 'BBQ Bacon Stack', 'Smoky BBQ sauce, crispy bacon, caramelized onions', 14.99, '🥩', 4.7, 18, 1),
(1, 'Mushroom Swiss', 'Sautéed mushrooms, Swiss cheese, garlic aioli', 13.49, '🍄', 4.6, 16, 0),
(2, 'Margherita', 'Fresh tomato, mozzarella, basil, olive oil', 11.99, '🍕', 4.9, 20, 1),
(2, 'Pepperoni Feast', 'Double pepperoni, mozzarella, tomato sauce', 13.99, '🔥', 4.8, 22, 1),
(2, 'Veggie Supreme', 'Bell peppers, olives, mushrooms, red onion', 12.49, '🌿', 4.5, 20, 0),
(3, 'Salmon Nigiri Set', '6-piece premium salmon nigiri with wasabi', 16.99, '🍣', 4.9, 10, 1),
(3, 'Dragon Roll', 'Shrimp tempura, avocado, eel sauce', 15.99, '🐉', 4.7, 12, 1),
(4, 'Carbonara', 'Guanciale, egg yolk, pecorino, black pepper', 13.99, '🍝', 4.8, 18, 1),
(4, 'Pesto Genovese', 'Basil pesto, pine nuts, parmesan, cherry tomato', 12.99, '🌱', 4.6, 16, 0),
(5, 'Tiramisu', 'Classic Italian dessert with mascarpone', 7.99, '🍰', 4.9, 5, 1),
(5, 'Lava Cake', 'Warm chocolate lava cake, vanilla ice cream', 8.49, '🍫', 4.8, 12, 1),
(6, 'Mango Lassi', 'Fresh mango, yogurt, cardamom', 4.99, '🥭', 4.7, 5, 0),
(6, 'Fresh Lemonade', 'Squeezed lemon, mint, sparkling water', 3.99, '🍋', 4.6, 5, 0);
