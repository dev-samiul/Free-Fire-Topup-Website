<?php
// 1. AUTO-FIX DATABASE: Add 'download_link' setting if missing
if(isset($conn)) {
    $conn->query("CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), value TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Check if 'fab_link' exists
    $chk = $conn->query("SELECT id FROM settings WHERE name='fab_link'");
    if($chk && $chk->num_rows == 0) {
        $conn->query("INSERT INTO settings (name, value) VALUES ('fab_link', 'https://wa.me/')");
    }

    // Check if 'download_link' exists
    $chk_dl = $conn->query("SELECT id FROM settings WHERE name='download_link'");
    if($chk_dl && $chk_dl->num_rows == 0) {
        $conn->query("INSERT INTO settings (name, value) VALUES ('download_link', '#')");
    }

    if (!function_exists('getSetting')) { 
        function getSetting($conn, $key) { 
            $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
            return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
        } 
    }

    $fab_link = getSetting($conn, 'fab_link');
    $download_link = getSetting($conn, 'download_link');
    
    // Use site_name from header if available, else fetch
    if(!isset($site_name)) $site_name = getSetting($conn, 'site_name');
    if(empty($site_name)) $site_name = "TopupBD";
} else {
    $fab_link = "#";
    $download_link = "#";
    $site_name = "TopupBD";
}

// User Avatar Logic
$user_avatar = 'res/images/default-avatar.png'; 
$is_logged_in = false;
if(isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $uid_bottom = (int)$_SESSION['user_id'];
    if(isset($conn)) {
        $u_res = $conn->query("SELECT name, avatar FROM users WHERE id=$uid_bottom");
        if($u_res && $u_res->num_rows > 0) {
            $u = $u_res->fetch_assoc();
            // Use saved avatar or generate one
            if(!empty($u['avatar'])) {
                $user_avatar = $u['avatar'];
            } elseif(!empty($u['name'])) {
                $user_avatar = "https://ui-avatars.com/api/?name=" . urlencode($u['name']) . "&background=random&color=fff";
            }
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet">

<style>
    /* 1. GRADIENT BROWSER LOAD BAR */
    #page-loader {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        height: 4px !important;
        width: 0 !important;
        z-index: 99999 !important;
        background: linear-gradient(90deg, #dc2626, #facc15, #2563eb) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
        transition: width 0.3s ease-out !important;
    }

    /* 2. SUPPORT BUTTON - FORCED RED */
    #support-btn {
        position: fixed !important;
        bottom: 128px !important;
        right: 16px !important;
        z-index: 9998 !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        transition: all 0.3s ease !important;
    }
    #support-label {
        background-color: #dc2626 !important;
        color: white !important;
        font-size: 12px !important;
        padding: 6px 12px !important;
        border-radius: 4px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        font-family: 'Noto Sans Bengali', sans-serif !important;
        letter-spacing: 0.025em !important;
    }
    #support-icon-box {
        width: 56px !important;
        height: 56px !important;
        background-color: #dc2626 !important;
        border-radius: 9999px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: white !important;
        font-size: 24px !important;
        transition: transform 0.2s !important;
    }
    #support-icon-box:hover { transform: scale(1.05) !important; }

    /* 3. DOWNLOAD APP POPUP */
    #app-download-popup {
        position: fixed;
        /* Mobile Default: Above TabBar */
        bottom: calc(85px + env(safe-area-inset-bottom)); 
        left: 10px;
        right: 10px;
        z-index: 10000;
        
        background-color: var(--primary-color, #dc2626);
        color: white;
        border-radius: 12px;
        padding: 10px 15px;
        
        display: none; 
        align-items: center;
        gap: 12px;
        
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
        animation: slideUpPopup 0.4s ease-out;
    }
    
    @keyframes slideUpPopup {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .dl-logo {
        width: 40px; height: 40px;
        background: white; border-radius: 8px; padding: 4px;
        object-fit: contain; flex-shrink: 0;
    }
    .dl-content { flex: 1; display: flex; flex-direction: column; }
    .dl-title { font-weight: 800; font-size: 14px; text-transform: uppercase; line-height: 1.2; }
    .dl-subtitle { font-size: 11px; opacity: 0.9; }
    .dl-btn {
        background: white; color: black; font-weight: 700;
        font-size: 13px; padding: 6px 14px; border-radius: 6px;
        text-decoration: none; white-space: nowrap;
    }
    .dl-close { font-size: 18px; cursor: pointer; opacity: 0.8; margin-left: 8px; }

    /* 4. BOTTOM NAV */
    #bottom-nav {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        width: 100% !important;
        background-color: #ffffff !important;
        border-top: 1px solid #e5e7eb !important;
        display: flex !important;
        justify-content: space-between !important;
        padding: 0 8px !important;
        align-items: center !important;
        z-index: 9999 !important;
        padding-bottom: env(safe-area-inset-bottom) !important;
        height: 74px !important;
        box-shadow: none !important;
        color: #000000 !important; 
        transition: all 0.3s ease !important;
    }

    .nav-link-item {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 4px !important;
        text-decoration: none !important;
        color: #000000 !important;
        transition: none !important;
    }
    
    .nav-link-item:hover { background: transparent !important; }
    
    .nav-icon-svg {
        width: 24px !important; height: 24px !important;
        color: #000000 !important; fill: none !important;
    }
    .nav-icon-svg path[fill="currentColor"] { fill: #000000 !important; }

    .nav-text-label {
        font-size: 11px !important;
        font-family: 'Bree Serif', serif !important; 
        font-weight: 400 !important;
        color: #000000 !important;
    }

    /* =========================================
       DESKTOP SUPPORT (Floating Dock Style)
       ========================================= */
    @media (min-width: 768px) {
        /* Center Navigation & make it floating */
        #bottom-nav {
            left: 0 !important;
            right: 0 !important;
            margin: 0 auto !important; /* Centering */
            width: 100% !important;
            max-width: 500px !important; /* Limit width */
            
            bottom: 20px !important; /* Float from bottom */
            border-radius: 20px !important; /* Rounded corners */
            border: 1px solid #e5e7eb !important;
            
            /* Enhanced Shadow for floating effect */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            padding-bottom: 0 !important; /* Reset safe area padding for floating */
        }

        /* Adjust Popup to float above the floating nav */
        #app-download-popup {
            left: 0 !important;
            right: 0 !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 480px !important; /* Slightly smaller than nav */
            bottom: 110px !important; /* (Nav Height 74 + Bottom 20 + Gap) */
        }

        /* Adjust Support Button position on desktop to not overlap content */
        #support-btn {
            bottom: 120px !important;
            right: 40px !important;
        }
    }
</style>

<div id="page-loader"></div>

<?php if(!empty($download_link) && $download_link != '#'): ?>
<div id="app-download-popup">
    <img src="res/logo.png" class="dl-logo" alt="Logo">
    <div class="dl-content">
        <span class="dl-title"><?php echo htmlspecialchars($site_name); ?></span>
        <span class="dl-subtitle">Add to home screen.</span>
    </div>
    <a href="<?php echo $download_link; ?>" class="dl-btn">Install</a>
    <i class="fa-solid fa-xmark dl-close" onclick="closeDownloadPopup()"></i>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dismissedTime = localStorage.getItem('hide_dl_popup_v1');
        const popup = document.getElementById('app-download-popup');
        
        if (popup) {
            let shouldShow = true;
            if (dismissedTime) {
                const now = new Date().getTime();
                // 30 Minutes Cooldown (30 * 60 * 1000)
                const cooldown = 30 * 60 * 1000; 
                
                if (now - dismissedTime < cooldown) {
                    shouldShow = false;
                }
            }
            if (shouldShow) popup.style.display = 'flex';
        }
    });

    function closeDownloadPopup() {
        const popup = document.getElementById('app-download-popup');
        if(popup) {
            popup.style.display = 'none';
            const now = new Date();
            localStorage.setItem('hide_dl_popup_v1', now.getTime());
        }
    }
</script>
<?php endif; ?>

<div id="support-btn">
    <div id="support-label">সাহায্য লাগবে ?</div>
    <a href="<?php echo $fab_link; ?>" target="_blank" id="support-icon-box">
        <i class="fa-solid fa-phone-volume rotate-[-10deg]"></i>
    </a>
</div>

<nav id="bottom-nav">
    <a href="index.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24" class="nav-icon-svg">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
        </div>
        <span class="nav-text-label">Home</span>
    </a>

    <a href="addmoney.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg class="nav-icon-svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M3 0V3H0V5H3V8H5V5H8V3H5V0H3M10 3V5H19V7H13C11.9 7 11 7.9 11 9V15C11 16.1 11.9 17 13 17H19V19H5V10H3V19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V16.72C21.59 16.37 22 15.74 22 15V9C22 8.26 21.59 7.63 21 7.28V5C21 3.9 20.1 3 19 3H10M13 9H20V15H13V9M16 10.5A1.5 1.5 0 0 0 14.5 12A1.5 1.5 0 0 0 16 13.5A1.5 1.5 0 0 0 17.5 12A1.5 1.5 0 0 0 16 10.5Z"></path>
            </svg>
        </div>
        <span class="nav-text-label">Add Money</span>
    </a>

    <a href="order.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" stroke="currentColor" viewBox="0 0 24 24" class="nav-icon-svg">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
        </div>
        <span class="nav-text-label">Orders</span>
    </a>

    <a href="mycode.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" class="nav-icon-svg">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
        </div>
        <span class="nav-text-label">Codes</span>
    </a>

    <a href="profile.php" class="nav-link-item spa-link">
        <div class="w-6 h-6 flex items-center justify-center">
            <?php if($is_logged_in): ?>
                <img src="<?php echo $user_avatar; ?>" alt="User" class="w-full h-full rounded-full border border-gray-300 object-cover block">
            <?php else: ?>
                <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="nav-icon-svg">
                    <path d="M20 21V19C20 17.3431 18.6569 16 17 16H7C5.34315 16 4 17.3431 4 19V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            <?php endif; ?>
        </div>
        <span class="nav-text-label">Profile</span>
    </a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('page-loader');

        // Initial Load
        loader.style.width = '100%';
        setTimeout(() => { loader.style.width = '0%'; }, 400);

        // --- SPA ENGINE (NO FADE) ---
        function handleNavigation(e) {
            const link = e.target.closest('a.spa-link');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href === '#' || (href.startsWith('http') && !href.includes(window.location.hostname))) return;

            e.preventDefault();

            // 1. Start Loader
            loader.style.opacity = '1';
            loader.style.width = '30%'; 

            // 2. Fetch Content
            fetch(href)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    loader.style.width = '60%'; 
                    return response.text();
                })
                .then(async (html) => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');

                    // 4. PRELOAD IMAGES
                    const images = newDoc.querySelectorAll('img');
                    const imagePromises = [];

                    images.forEach(img => {
                        if (img.src) {
                            const p = new Promise((resolve) => {
                                const i = new Image();
                                i.onload = resolve;
                                i.onerror = resolve; 
                                i.src = img.src;
                            });
                            imagePromises.push(p);
                        }
                    });

                    if(imagePromises.length > 0) {
                        const timeout = new Promise(resolve => setTimeout(resolve, 1500));
                        await Promise.race([Promise.all(imagePromises), timeout]);
                    }

                    // 6. SWAP CONTENT
                    loader.style.width = '90%';
                    document.title = newDoc.title;
                    document.body.innerHTML = newDoc.body.innerHTML;
                    
                    window.history.pushState({}, '', href);
                    window.scrollTo(0, 0);

                    // 7. Re-Execute Scripts
                    const scripts = document.body.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        if (!oldScript.src) newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    attachNavListeners();

                    // 9. Complete
                    loader.style.width = '100%';
                    setTimeout(() => { loader.style.width = '0%'; }, 300);
                })
                .catch(err => {
                    console.error('Nav Error:', err);
                    window.location.href = href; // Fallback
                });
        }

        function attachNavListeners() {
            document.removeEventListener('click', handleNavigation);
            document.addEventListener('click', handleNavigation);
        }

        attachNavListeners();
        window.addEventListener('popstate', () => window.location.reload());
    });
</script>

<div id="notifModal" class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 hidden">
    <div class="bg-white w-80 rounded-2xl shadow-2xl p-6 text-center transform scale-95 transition-all duration-300" id="notifContent">
        <div id="notifIcon" class="text-5xl mb-4"></div>
        <h3 id="notifTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
        <p id="notifMsg" class="text-sm text-gray-500 mb-6"></p>
        <button onclick="closeNotif()" class="bg-[#2B71AD] text-white w-full py-3 rounded-xl font-bold hover:opacity-90" style="background-color: var(--primary-color, #2B71AD);">Okay</button>
    </div>
</div>

<script>
    function showNotif(type, title, msg) {
        const modal = document.getElementById('notifModal');
        const content = document.getElementById('notifContent');
        const iconEl = document.getElementById('notifIcon');
        
        document.getElementById('notifTitle').innerText = title;
        document.getElementById('notifMsg').innerText = msg;
        
        if(type === 'success') iconEl.innerHTML = '<i class="fa-solid fa-circle-check text-green-500 text-5xl"></i>';
        else if(type === 'error') iconEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500 text-5xl"></i>';
        else iconEl.innerHTML = '<i class="fa-solid fa-circle-info text-5xl" style="color: var(--primary-color, #2B71AD);"></i>';
        
        modal.classList.remove('hidden');
        setTimeout(() => { content.classList.remove('scale-95'); content.classList.add('scale-100'); }, 10);
    }

    function closeNotif() {
        const modal = document.getElementById('notifModal');
        const content = document.getElementById('notifContent');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 200);
    }
</script>