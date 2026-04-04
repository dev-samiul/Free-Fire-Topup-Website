<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">

<style>
    /* GLOBAL FONT */
    .font-lato { font-family: 'Lato', sans-serif; }

    /* CUSTOM SCROLLBAR */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #111; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #444; }

    /* DROPDOWN ANIMATION */
    .submenu {
        transition: max-height 0.4s ease-in-out, opacity 0.4s ease-in-out;
        opacity: 0;
    }
    .submenu.open {
        opacity: 1;
    }
    
    /* ICON ROTATION */
    .rotate-icon { transition: transform 0.3s ease; }
    .rotate-180 { transform: rotate(180deg); }
    
    /* FEATHER ICON SIZING */
    .feather { width: 20px; height: 20px; vertical-align: middle; }
    .feather-sm { width: 16px; height: 16px; vertical-align: middle; }
</style>

<aside id="adminSidebar" class="fixed inset-y-0 left-0 w-64 bg-[#111111] border-r border-[#222] transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50 md:static md:block flex flex-col flex-shrink-0 font-lato shadow-2xl md:shadow-none">
    
    <div class="h-20 flex items-center justify-center border-b border-[#222]">
        <img src="../res/logo.png" alt="Logo" class="h-10 w-auto object-contain opacity-90 hover:opacity-100 transition-opacity">
    </div>
    
    <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">
        
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);

        // --- PENDING ORDERS COUNT ---
        $pending_count = 0;
        if(isset($conn)) {
            $p_query = $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'");
            if($p_query) $pending_count = $p_query->fetch_row()[0];
        }

        /**
         * FUNCTION: SINGLE MENU ITEM
         */
        function navItem($url, $icon, $label, $badge = null) {
            global $current_page;
            $isActive = ($current_page == $url);
            
            // Active: Dark BG + Yellow Text
            $classes = $isActive 
                ? "bg-[#222] text-yellow-500 font-bold" 
                : "text-gray-400 hover:text-white hover:bg-[#1a1a1a] font-medium";

            echo '<a href="'.$url.'" class="flex items-center justify-between px-4 py-3 rounded-lg text-[15px] transition-all duration-200 mb-1 '.$classes.'">
                    <div class="flex items-center gap-3">
                        <i data-feather="'.$icon.'"></i> 
                        <span>'.$label.'</span>
                    </div>';
            
            if ($badge !== null && $badge > 0) {
                echo '<span class="text-[11px] font-bold px-2 py-0.5 rounded border border-yellow-600 text-yellow-500">'.$badge.'</span>';
            }

            echo '</a>';
        }

        /**
         * FUNCTION: DROPDOWN MENU
         */
        function navDropdown($title, $icon, $items) {
            global $current_page;
            
            // Check if active
            $isOpen = false;
            foreach($items as $item) {
                if($current_page == $item['url']) { $isOpen = true; break; }
            }

            $headerClass = $isOpen ? "text-white font-bold" : "text-gray-400 hover:text-white font-medium";
            $rotateClass = $isOpen ? "rotate-180" : "";
            $menuOpenClass = $isOpen ? "open" : "";
            
            // INLINE STYLE TO FORCE HIDE IF CLOSED
            $inlineStyle = $isOpen ? "max-height: 1000px;" : "max-height: 0px; overflow: hidden;";

            echo '<div class="group mb-1">
                    <button onclick="toggleSubmenu(this)" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-[15px] hover:bg-[#1a1a1a] transition-all duration-200 focus:outline-none '.$headerClass.'">
                        <div class="flex items-center gap-3">
                            <i data-feather="'.$icon.'"></i>
                            <span>'.$title.'</span>
                        </div>
                        <i data-feather="chevron-down" class="feather-sm opacity-70 rotate-icon '.$rotateClass.'"></i>
                    </button>
                    <div class="submenu pl-4 '.$menuOpenClass.' space-y-1" style="'.$inlineStyle.'">';
            
            foreach($items as $item) {
                $isSubActive = ($current_page == $item['url']);
                // Sub-item styles
                $subColor = $isSubActive ? "text-yellow-500 font-bold bg-[#1f1f1f]" : "text-gray-500 hover:text-gray-200 hover:bg-[#1a1a1a]";
                
                echo '<a href="'.$item['url'].'" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-[14px] transition-all '.$subColor.'">
                        <i data-feather="'.$item['icon'].'" class="feather-sm"></i>
                        <span>'.$item['label'].'</span>
                      </a>';
            }

            echo '</div></div>';
        }

        // ==========================================
        // MENU ITEMS
        // ==========================================

        // 1. Dashboard
        navItem('index.php', 'grid', 'Dashboard');

        // 2. Orders
        navItem('order.php', 'shopping-cart', 'Orders List', $pending_count);

        // 3. User (Renamed from User Management)
        navItem('user.php', 'users', 'User');

        // 4. Products Management (Dropdown)
        navDropdown('Products', 'layers', [
            ['url' => 'categories.php', 'label' => 'Categories', 'icon' => 'list'],
            ['url' => 'game.php', 'label' => 'Products', 'icon' => 'monitor'], // Renamed from Games List
            ['url' => 'product.php', 'label' => 'Variations', 'icon' => 'package'], // Renamed from Products & Topup
            ['url' => 'redeemcode.php', 'label' => 'Redeem Codes', 'icon' => 'gift'],
        ]);

        // 5. System Settings (Dropdown)
        navDropdown('Configuration', 'settings', [
            ['url' => 'sliders.php', 'label' => 'Banner Sliders', 'icon' => 'image'],
            ['url' => 'popup.php', 'label' => 'Announcements', 'icon' => 'bell'],
            ['url' => 'paymentmethod.php', 'label' => 'Payment Methods', 'icon' => 'credit-card'],
            ['url' => 'setting.php', 'label' => 'Website Settings', 'icon' => 'sliders'],
        ]);
        ?>

    </nav>
</aside>

<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 z-40 hidden md:hidden transition-opacity"></div>

<script>
    // --- INITIALIZE ICONS ---
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();
    });

    // --- SIDEBAR TOGGLE ---
    function toggleSidebar() {
        const sb = document.getElementById('adminSidebar');
        const ov = document.getElementById('sidebarOverlay');
        
        if (sb.classList.contains('-translate-x-full')) {
            sb.classList.remove('-translate-x-full');
            ov.classList.remove('hidden');
        } else {
            sb.classList.add('-translate-x-full');
            ov.classList.add('hidden');
        }
    }

    // --- DROPDOWN TOGGLE ---
    function toggleSubmenu(btn) {
        const submenu = btn.nextElementSibling;
        
        // Find the generated SVG arrow
        const icon = btn.querySelector('.rotate-icon'); 
        
        // Check current state based on max-height style
        const isClosed = submenu.style.maxHeight === '0px' || submenu.style.maxHeight === '';

        if (isClosed) {
            // OPEN
            submenu.style.maxHeight = '1000px'; // Allow content to show
            submenu.style.overflow = 'visible';
            submenu.classList.add('open');
            
            // Rotate Arrow
            if(icon) icon.classList.add('rotate-180');
            
            // Highlight Button
            btn.classList.add('text-white');
            btn.classList.remove('text-gray-400');
        } else {
            // CLOSE
            submenu.style.maxHeight = '0px';
            submenu.style.overflow = 'hidden';
            submenu.classList.remove('open');
            
            // Reset Arrow
            if(icon) icon.classList.remove('rotate-180');
            
            // Dim Button
            btn.classList.remove('text-white');
            btn.classList.add('text-gray-400');
        }
    }
</script>
