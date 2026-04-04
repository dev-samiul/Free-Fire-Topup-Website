<?php
// add_admin_only.php
// এই ফাইলটি run করলে শুধু নতুন admin add হবে

// Database connection
$host = 'localhost';
$dbname = 'dream_topup';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // New admin credentials
    $new_username = 'sameulislam369';
    $new_password = 'Sameul@1234';
    $new_email = 'sameulislam369@gmail.com';
    $new_name = 'Sameul Islam';
    $new_phone = '01700000000';
    
    // Hash the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    echo "Generated Hash: " . $hashed_password . "<br>";
    echo "----------------------------------------<br>";
    
    // Check if admin already exists in admins table
    $check_admin = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $check_admin->execute([$new_username]);
    
    if ($check_admin->rowCount() == 0) {
        // Add to admins table
        $insert_admin = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $insert_admin->execute([$new_username, $hashed_password]);
        echo "✓ Admin added to admins table successfully!<br>";
    } else {
        echo "⚠ Admin already exists in admins table<br>";
    }
    
    // Check if user exists in users table
    $check_user = $conn->prepare("SELECT id, role FROM users WHERE email = ?");
    $check_user->execute([$new_email]);
    
    if ($check_user->rowCount() == 0) {
        // Add to users table
        $insert_user = $conn->prepare("INSERT INTO users (name, phone, email, password, balance, role, status, created_at) 
                                      VALUES (?, ?, ?, ?, 0.00, 'admin', 'active', NOW())");
        $insert_user->execute([$new_name, $new_phone, $new_email, $hashed_password]);
        echo "✓ User added to users table with admin role!<br>";
    } else {
        $user = $check_user->fetch(PDO::FETCH_ASSOC);
        if ($user['role'] != 'admin') {
            // Update to admin role
            $update_user = $conn->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
            $update_user->execute([$new_email]);
            echo "✓ Existing user updated to admin role!<br>";
        } else {
            echo "⚠ User already has admin role<br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>✅ Admin Login Details:</h3>";
    echo "<p><strong>Username:</strong> sameulislam369</p>";
    echo "<p><strong>Email:</strong> sameulislam369@gmail.com</p>";
    echo "<p><strong>Password:</strong> Sameul@1234</p>";
    echo "<p><strong>Password Hash:</strong> " . $hashed_password . "</p>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>