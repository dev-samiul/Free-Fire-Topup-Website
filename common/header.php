<?php 
// 1. Include Config
include_once __DIR__ . '/config.php'; 

// ====================================================
// SELF-HEALING: Fix 'avatar' column missing error
// ====================================================
if(isset($conn) && $conn) {
    $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if($colCheck && $colCheck->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    }
}

// ====================================================
// SETTINGS FETCH LOGIC
// ====================================================
if (!function_exists('getSetting')) { 
    function getSetting($conn, $key) { 
        if(!$conn) return ''; 
        $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
        return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
    } 
}

// ====================================================
// COLOR & BRANDING LOGIC
// ====================================================

// Helper: Hex to RGB for transparency/opacity support
if (!function_exists('hex2rgb')) {
    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return "$r, $g, $b";
    }
}

// Default Values
$site_name = "TopupBD";
$site_title = "TopupBD - Best Game Topup";
$site_logo_path = "res/logo.png";
$main_color = "#DC2626"; // Default Red
$meta_desc = "Best gaming topup site in Bangladesh.";
$meta_keywords = "topup, game, diamond, free fire";
$favicon_url = "res/logo.png"; // Default to logo
$og_image = "res/logo.png";
$twitter_image = "res/logo.png";
$pwa_icon = "res/logo.png";
$per_page = 10;

// Fetch from Database
if(isset($conn) && $conn) {
    // Basic Info
    $db_name = getSetting($conn, 'site_name');
    $db_title = getSetting($conn, 'home_title'); // Specific Home Title
    $db_logo = getSetting($conn, 'site_logo');
    $db_color = getSetting($conn, 'site_color'); 
    
    // SEO & Meta
    $db_desc = getSetting($conn, 'meta_desc');
    $db_keys = getSetting($conn, 'keywords');
    $db_og = getSetting($conn, 'og_image');
    $db_tw = getSetting($conn, 'twitter_image');
    
    // Icons
    $db_fav = getSetting($conn, 'favicon_url');
    $db_pwa = getSetting($conn, 'pwa_icon');
    
    // Config
    $db_page = getSetting($conn, 'paginate_per_page');

    if(!empty($db_name)) $site_name = $db_name;
    if(!empty($db_title)) $site_title = $db_title;
    if(!empty($db_logo)) $site_logo_path = $db_logo;
    if(!empty($db_color)) $main_color = $db_color;
    
    if(!empty($db_desc)) $meta_desc = $db_desc;
    if(!empty($db_keys)) $meta_keywords = $db_keys;
    
    // Fallback logic for images
    if(!empty($db_fav)) $favicon_url = $db_fav; else $favicon_url = $site_logo_path;
    if(!empty($db_og)) $og_image = $db_og; else $og_image = $site_logo_path;
    if(!empty($db_tw)) $twitter_image = $db_tw; else $twitter_image = $site_logo_path;
    if(!empty($db_pwa)) $pwa_icon = $db_pwa; else $pwa_icon = $site_logo_path;
    
    if(!empty($db_page)) $per_page = (int)$db_page;
}

// Prepare Variables
$main_rgb = hex2rgb($main_color);
$logo_url = $site_logo_path . "?v=" . time(); 
$favicon_full = $favicon_url . "?v=" . time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="twitter:image" content="<?php echo $twitter_image; ?>">

    <link rel="icon" type="image/png" href="<?php echo $favicon_full; ?>">
    <link rel="apple-touch-icon" href="<?php echo $pwa_icon; ?>">
    
    <meta name="theme-color" content="<?php echo $main_color; ?>">
    <meta name="msapplication-navbutton-color" content="<?php echo $main_color; ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="<?php echo $main_color; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Lato:wght@400;700&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* =========================================
           GLOBAL COLOR VARIABLES (FROM DB)
           ========================================= */
        :root, body {
            --primary-color: <?php echo $main_color; ?>;
            --primary-rgb: <?php echo $main_rgb; ?>;
            --primary-dark: <?php echo $main_color; ?>dd;
        }

        /* GLOBAL SCROLLBAR */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* HEADER: High Transparency Glassmorphism */
        #main-header-secure {
            background-color: rgba(255, 255, 255, 0.1) !important; 
            backdrop-filter: blur(12px) !important; 
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(203, 213, 225, 0.4) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 50 !important; 
            width: 100% !important;
            height: 70px !important;
        }

        /* SIDEBAR Z-Index */
        #user-sidebar, .sidebar, .offcanvas, .drawer, .mobile-menu { z-index: 9999999 !important; }
        .sidebar-backdrop, .drawer-overlay, .offcanvas-backdrop { z-index: 9999998 !important; }

        /* BIGGER LOGO */
        #secure-logo-img {
            height: 70px !important; 
            width: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* BALANCE PILL STYLES */
        .secure-balance-box {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
            border: 1px solid #ffffff !important;
            border-radius: 50px !important; 
            height: 38px !important; 
            padding: 0 8px !important; 
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important; 
            min-width: 60px !important;
            text-decoration: none !important;
            box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.3) !important; 
        }

        .secure-balance-text {
            font-family: 'Noto Serif Bengali', serif !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            padding-top: 2px; 
        }

        /* --- MINI AUTH BUTTONS --- */
        .btn-auth-login {
            background: transparent;
            color: var(--primary-color);
            
            /* Border 2px */
            border: 2px solid var(--primary-color);
            
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 6px; 
            
            /* Font Lato, Weight Normal */
            font-weight: 400;
            font-family: 'Lato', sans-serif;
            
            text-transform: uppercase;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-auth-login:hover { background-color: rgba(var(--primary-rgb), 0.1); } 

        .btn-auth-register {
            background: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
            
            padding: 4px 12px; 
            font-size: 12px;
            border-radius: 6px; 
            
            /* Font Lato, Weight Normal */
            font-weight: 400;
            font-family: 'Lato', sans-serif;
            
            text-transform: uppercase;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-auth-register:hover { opacity: 0.9; }

        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
    </style>
</head>
<body class="bg-[#f0f5f9] text-gray-800 pb-24">

<header id="main-header-secure">
    <div class="container mx-auto px-4 flex justify-between items-center h-full gap-5">
        
        <div class="flex items-center">
            <a href="index.php" class="flex items-center">
                <img id="secure-logo-img" src="<?php echo $logo_url; ?>" alt="<?php echo htmlspecialchars($site_name); ?>">
            </a>
        </div>

        <div class="flex items-center gap-3">
            <?php if(isset($_SESSION['user_id'])): 
                $u_data = ['balance' => 0, 'name' => 'User', 'avatar' => ''];
                $uid = $_SESSION['user_id'];

                if(isset($conn) && $conn) {
                    $u_res = $conn->query("SELECT balance, name, avatar FROM users WHERE id=$uid");
                    if($u_res && $u_res->num_rows > 0) {
                        $u_data = $u_res->fetch_assoc();
                    }
                }
                
                $balStr = number_format($u_data['balance'], 0);
                $digits = strlen(str_replace(',', '', $balStr));
                
                $fSize = '15px'; 
                if($digits > 4) $fSize = '13px'; 
                if($digits > 6) $fSize = '11px'; 

                $avatarUrl = !empty($u_data['avatar']) ? $u_data['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($u_data['name']) . "&background=f3f4f6&color=333&length=1&font-size=0.5&bold=true";
            ?>
                <div class="secure-balance-box" style="font-size: <?php echo $fSize; ?> !important;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="opacity-90">
                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                    </svg>
                    <span class="secure-balance-text"><?php echo $balStr; ?>&#2547;</span>
                </div>

                <button onclick="toggleUserSidebar()" class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden relative focus:outline-none border border-gray-100" style="z-index: 10001;">
                    <img src="<?php echo $avatarUrl; ?>" class="w-full h-full object-cover block">
                </button>

            <?php else: ?>
                <div class="flex items-center gap-2">
                    <a href="login.php" class="btn-auth-login">
                        Login
                    </a>
                    <a href="login.php?action=signup" class="btn-auth-register">
                        Register
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php include 'sidebar.php'; ?>