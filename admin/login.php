<?php
// 1. Include Config (This file likely already starts the session)
include '../common/config.php';

// Safety check: Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ====================================================
// 2. SELF-HEALING: Ensure 'role' column exists in users
// ====================================================
if(isset($conn)) {
    try {
        $chk_role = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
        if($chk_role->num_rows == 0) {
            // Default everyone to user
            $conn->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
        }
    } catch(Exception $e) {}
}

// ====================================================
// 3. FETCH SETTINGS (Logo & Color)
// ====================================================
function getVal($conn, $key) {
    $q = $conn->query("SELECT value FROM settings WHERE name='$key'");
    return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
}

$site_logo = getVal($conn, 'site_logo');
if(empty($site_logo)) $site_logo = "res/logo.png"; 

// ====================================================
// 4. LOGIN LOGIC
// ====================================================
if(isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Check USERS table
    $sql = "SELECT * FROM users WHERE email='$email' OR phone='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify Password
        if(password_verify($password, $row['password'])) {
            
            // Set User Session
            $_SESSION['user_id'] = $row['id'];
            
            // CHECK ROLE
            if(isset($row['role']) && $row['role'] === 'admin') {
                $_SESSION['admin_id'] = $row['id'];
                header("Location: index.php"); // Go to Admin Panel
            } else {
                header("Location: ../index.php"); // Go to User Site
            }
            exit;
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Account not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            background-color: #050505; /* Deep Black Background */
            color: #ffffff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background-color: #111111; /* Slightly lighter card */
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
            border: 1px solid #222;
        }

        .site-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #fff;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #ccc;
        }
        .form-label span { color: #ef4444; }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            background-color: #1a1a1a;
            border: 1px solid #333;
            padding: 12px 15px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-color: #eab308; /* Accent Color */
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            font-size: 13px;
            color: #aaa;
        }
        
        .checkbox-group input {
            accent-color: #eab308;
            width: 16px;
            height: 16px;
            background: #1a1a1a;
            border: 1px solid #333;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            background-color: #f59e0b; /* Orange/Gold like screenshot */
            color: #000;
            font-weight: 700;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: opacity 0.2s;
        }

        .btn-login:hover { opacity: 0.9; }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="../<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" class="site-logo">
        
        <h1 class="page-title">Sign in</h1>

        <?php if(isset($error)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email address <span>*</span></label>
                <input type="text" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password <span>*</span></label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="passInput" class="form-input" required>
                    <i class="fa-solid fa-eye password-toggle" onclick="togglePass()"></i>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" name="login" class="btn-login">Sign in</button>
        </form>
    </div>

    <script>
        function togglePass() {
            const input = document.getElementById('passInput');
            const icon = document.querySelector('.password-toggle');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>
