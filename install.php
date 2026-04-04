<?php
// install.php
// Database Connection Configuration
$host = "localhost";
$user = "root";
$pass = ""; // XAMPP এর জন্য পাসওয়ার্ড খালি
$dbname = "dream_topup"; // ডাটাবেস নাম পরিবর্তন করা হয়েছে

// Create Connection
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "<br>Please check your server settings in install.php");
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
    
    // SQL Queries for All Tables
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            phone VARCHAR(20),
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255),
            balance DECIMAL(10,2) DEFAULT 0.00,
            avatar VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50),
            password VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE,
            value TEXT
        )",
        "CREATE TABLE IF NOT EXISTS sliders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image VARCHAR(255),
            link VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS games (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            type ENUM('uid','voucher') DEFAULT 'uid',
            description TEXT,
            image VARCHAR(255),
            category_id INT DEFAULT 0,
            status ENUM('active','inactive') DEFAULT 'active'
        )",
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            priority INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )",
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT,
            name VARCHAR(100),
            price DECIMAL(10,2),
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS payment_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50),
            logo VARCHAR(255),
            qr_image VARCHAR(255),
            number VARCHAR(50),
            description TEXT,
            short_desc VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            game_id INT,
            product_id INT,
            amount DECIMAL(10,2),
            status ENUM('pending','completed','cancelled') DEFAULT 'pending',
            player_id VARCHAR(100),
            transaction_id VARCHAR(100),
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS redeem_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT,
            product_id INT,
            code VARCHAR(100),
            status ENUM('active','used','expired') DEFAULT 'active',
            order_id INT DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS deposits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            amount DECIMAL(10,2),
            method VARCHAR(50),
            wallet_number VARCHAR(50),
            trx_id VARCHAR(100),
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    // Execute Table Queries
    echo "<div style='font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9;'>";
    echo "<h2 style='color: #333; text-align: center;'>System Installation</h2>";
    echo "<hr>";
    
    $success = true;
    foreach ($queries as $q) {
        if (!$conn->query($q)) {
            echo "<p style='color:red'>Error creating table: " . $conn->error . "</p>";
            $success = false;
        }
    }
    
    if($success) {
        echo "<p style='color:green'>✔ Database Tables Created Successfully.</p>";
    }

    // Default Admin (admin / admin123)
    $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO admins (id, username, password) VALUES (1, 'admin', '$adminPass')");
    echo "<p style='color:green'>✔ Default Admin Account Created (Username: admin, Password: admin123).</p>";
    
    // Default Settings
    $settings = [
        ['site_name', 'TopupBD'],
        ['site_title', 'TopupBD - Best Game Topup'],
        ['site_logo', 'res/logo.png'],
        ['site_color', '#DC2626'],
        ['meta_desc', 'Best gaming topup site in Bangladesh.'],
        ['keywords', 'topup, game, diamond, free fire'],
        ['home_notice', 'Welcome to TopupBD! Best prices for all games.'],
        ['fab_link', 'https://wa.me/'],
        ['download_link', '#'],
        ['facebook', '#'],
        ['instagram', '#'],
        ['youtube', '#'],
        ['telegram_link', '#'],
        ['contact_email', 'admin@topupbd.com'],
        ['whatsapp_number', '']
    ];
    
    foreach($settings as $setting) {
        $conn->query("INSERT IGNORE INTO settings (name, value) VALUES ('{$setting[0]}', '{$setting[1]}')");
    }
    echo "<p style='color:green'>✔ Default Settings Inserted.</p>";

    // Create Required Folders
    $folders = ['res', 'res/images', 'res/backgrounds', 'uploads'];
    foreach($folders as $folder) {
        if (!file_exists($folder)) {
            if (mkdir($folder, 0777, true)) {
                echo "<p style='color:green'>✔ $folder folder created successfully.</p>";
            } else {
                echo "<p style='color:red'>✘ Failed to create $folder folder.</p>";
            }
        } else {
            echo "<p style='color:orange'>✔ $folder folder already exists.</p>";
        }
    }
    
    // Create default avatar
    if(!file_exists('res/images/default-avatar.png')) {
        // You can copy a default avatar here or create a simple one
        echo "<p style='color:orange'>Please add a default avatar at res/images/default-avatar.png</p>";
    }

    echo "<hr>";
    echo "<div style='text-align:center; margin-top:20px;'>
            <h1 style='color:green;'>Installation Complete!</h1>
            <p>Your system is ready to use.</p>
            <div style='display:flex; gap:10px; justify-content:center; margin-top:20px;'>
                <a href='index.php' style='padding:12px 25px; background:blue; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Go to Website</a>
                <a href='login.php' style='padding:12px 25px; background:green; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>User Login</a>
            </div>
            <div style='margin-top:15px;'>
                <a href='admin/index.php' style='padding:12px 25px; background:black; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Admin Panel</a>
            </div>
            <p style='color:red; font-size: 12px; margin-top: 20px;'>Warning: Please delete or rename 'install.php' after installation for security.</p>
          </div>";
    echo "</div>";

} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>