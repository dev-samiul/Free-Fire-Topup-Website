<?php 
// Include Configuration
include 'common/config.php'; 
include 'common/header.php'; 

// ====================================================
// AUTOMATIC DATABASE UPDATE LOGIC
// ====================================================
if(isset($conn)) {
    // 1. Create Categories Table & Add Status
    $conn->query("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        priority INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure status column exists in categories
    $chk_cat_stat = $conn->query("SHOW COLUMNS FROM categories LIKE 'status'");
    if($chk_cat_stat && $chk_cat_stat->num_rows == 0) {
        $conn->query("ALTER TABLE categories ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }

    // 2. Add category_id to games table
    $chk_col = $conn->query("SHOW COLUMNS FROM games LIKE 'category_id'");
    if($chk_col && $chk_col->num_rows == 0) {
        $conn->query("ALTER TABLE games ADD COLUMN category_id INT DEFAULT 0");
    }

    // 3. Add status column to games table if not exists
    $chk_status = $conn->query("SHOW COLUMNS FROM games LIKE 'status'");
    if($chk_status && $chk_status->num_rows == 0) {
        $conn->query("ALTER TABLE games ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }

    // 4. Add Popup Settings
    $settings_to_add = [
        'popup_image' => '',
        'popup_link' => '#',
        'popup_btn_text' => 'See Offer',
        'popup_text' => ''
    ];
    
    foreach($settings_to_add as $key => $default) {
        $chk_set = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($chk_set && $chk_set->num_rows == 0) {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$default')");
        }
    }

    // 5. Create sliders table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS sliders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        link VARCHAR(255)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// ====================================================
// FETCH DATA
// ====================================================

// Fetch Notice
$notice_text = ""; 
if(function_exists('getSetting') && isset($conn)) {
    $notice_text = getSetting($conn, 'home_notice');
}

// Fetch Popup Data
$popup_img = isset($conn) ? getSetting($conn, 'popup_image') : '';
$popup_link = isset($conn) ? getSetting($conn, 'popup_link') : '#';
$popup_btn = isset($conn) ? getSetting($conn, 'popup_btn_text') : 'See Offer';
$popup_text = isset($conn) ? getSetting($conn, 'popup_text') : ''; 

// Fetch Latest 5 Completed Orders
$latest_orders = [];
if(isset($conn)) {
    $order_query = $conn->query("
        SELECT o.id, u.name AS user_name, u.avatar AS user_image, p.name AS product_name, o.amount 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN products p ON o.product_id = p.id
        WHERE o.status = 'completed'
        ORDER BY o.id DESC
        LIMIT 5
    ");
    if($order_query){
        while($row = $order_query->fetch_assoc()){ $latest_orders[] = $row; }
    }
}

// Set default user image if not exists
$default_user_img = 'res/images/default-avatar.png';
if(!file_exists($default_user_img)) {
    // Create directory if not exists
    if(!file_exists('res/images')) {
        mkdir('res/images', 0777, true);
    }
}

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Lato:wght@400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* =========================================
       COLOR CONFIGURATION
       ========================================= */
    :root {
        --primary-color: <?php echo isset($main_color) ? $main_color : '#DC2626'; ?>;
        --primary-color-dark: <?php echo isset($main_color) ? $main_color . 'dd' : '#b91c1c'; ?>;
    }

    /* GLOBAL FONT - REST OF SITE USES BREE SERIF */
    body { 
        font-family: 'Bree Serif', serif; 
        background-image: url('res/backgrounds/bg.png');
        background-repeat: repeat;
        background-size: 100% auto; 
        background-attachment: scroll; 
        background-position: top center;
    }
    
    .sharp-edge { border-radius: 0px !important; }

    .slider-aspect {
        aspect-ratio: 2 / 1;
        width: 100%;
        overflow: hidden;
        position: relative;
    }

    /* NOTICE STYLES */
    .notice-box {
        background-color: var(--primary-color); 
        color: white;
        border-radius: 0px; 
        padding: 10px 15px; 
        position: relative;
        margin-bottom: 10px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .notice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    
    /* Notice Title uses Sharp Font (Lato) */
    .notice-title { 
        font-size: 18px; 
        font-family: 'Lato', sans-serif; 
        font-weight: 900; 
        letter-spacing: 0.5px;
    }
    
    .notice-close { font-size: 18px; cursor: pointer; opacity: 0.9; transition: opacity 0.2s; }
    .notice-close:hover { opacity: 1; transform: scale(1.1); }
    
    /* Notice Content is BOLD */
    .notice-content { 
        font-size: 13px; 
        font-family: 'Lato', 'Noto Serif Bengali', sans-serif; 
        line-height: 1.4; 
        opacity: 1; 
        font-weight: 700; /* Bold */
    }

    /* DIVIDER */
    .divider-container {
        display: flex; align-items: center; text-align: center; margin: 20px 0 10px 0; 
    }
    .divider-line {
        flex: 1; height: 2px;
        background: linear-gradient(to left, #FFD700, var(--primary-color)); 
    }
    .divider-line:first-child {
        background: linear-gradient(to right, #FFD700, var(--primary-color));
    }
    .divider-text {
        font-size: 1.4rem; padding: 0 1rem; color: #000000; font-weight: 700; text-transform: lowercase;
    }
    .divider-text::first-letter { text-transform: uppercase; }

    /* SLIDER */
    #slider { display: flex; transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); cursor: grab; }
    #slider:active { cursor: grabbing; }
    .game-card-img { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border: 1px solid #e2e8f0; }
    .dot-active { background-color: #000000 !important; }
    .dot-inactive { background-color: #d1d5db !important; }

    /* --- POPUP UI --- */
    #homePopupOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 10005; display: none; 
        align-items: center; justify-content: center;
        padding: 15px;
    }
    
    .popup-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 400px;
        width: 100%;
        animation: popupPop 0.25s ease-out;
        position: relative; 
    }

    @keyframes popupPop {
        0% { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .popup-card {
        background: #fff;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        box-shadow: none !important;
        padding-bottom: 30px; 
    }

    .popup-img-box { width: 100%; background: #000; }
    .popup-img-box img { display: block; width: 100%; height: auto; }

    .popup-body { padding: 20px 20px 0px 20px; text-align: center; }
    
    .popup-text-content {
        font-size: 14px;
        color: #333;
        line-height: 1.5;
        margin-bottom: 15px;
        font-family: 'Lato', 'Noto Serif Bengali', sans-serif;
    }

    .popup-action-btn {
        background: var(--primary-color);
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        display: inline-block;
        font-size: 14px;
        text-decoration: none;
        border: none;
        box-shadow: 0 2px 5px rgba(220, 38, 38, 0.4);
    }

    .popup-bottom-close {
        position: absolute;
        bottom: -20px; 
        left: 50%;
        transform: translateX(-50%);
        background: var(--primary-color);
        color: white;
        border: 2px solid #fff;
        padding: 8px 30px;
        border-radius: 50px;
        font-weight: 900;
        font-size: 14px;
        cursor: pointer;
        font-family: 'Lato', sans-serif;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transition: transform 0.2s, bottom 0.2s;
        z-index: 10;
    }
    .popup-bottom-close:hover { transform: translateX(-50%) scale(1.05); }

    /* --- LATEST ORDERS SECTION --- */
    .latest-orders-container {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
    }
    .latest-orders-title {
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 20px;
        color: #000;
        margin-bottom: 5px;
    }
    .latest-orders-subtitle {
        font-family: 'Noto Serif Bengali', sans-serif;
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }
    .latest-orders-subtitle span { color: var(--primary-color); font-weight: 700; }
    
    .order-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .order-user-info {
        display: flex;
        align-items: center;
        width: 100%;
        overflow: hidden;
        padding-right: 10px;
    }

    .order-user-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f4f6;
        flex-shrink: 0;
        margin-right: 12px;
        border: none;
    }

    .order-text-group {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        min-width: 0;
    }

    .order-user-name {
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #0f0f0f;
        line-height: 1.2;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .order-details {
        font-family: 'Lato', sans-serif;
        font-size: 12px;
        color: #606060;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    .order-status {
        background: #22c55e;
        color: white;
        padding: 4px 12px;
        border-radius: 16px;
        font-family: 'Lato', sans-serif;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        margin-left: auto;
        flex-shrink: 0;
    }

    @media (max-width: 480px) {
        .latest-orders-container { padding: 15px 10px; }
        .order-card { padding: 10px; }
        .order-user-img { width: 35px; height: 35px; margin-right: 10px; }
        .order-user-name { font-size: 13px; }
        .order-details { font-size: 11px; }
        .order-status { font-size: 10px; padding: 3px 8px; }
    }

</style>

<?php if(!empty($popup_img) || !empty($popup_text)): ?>
<div id="homePopupOverlay">
    <div class="popup-wrapper">
        
        <div class="popup-card">
            <?php if(!empty($popup_img)): ?>
            <div class="popup-img-box">
                <img src="<?php echo $popup_img; ?>" alt="Offer">
            </div>
            <?php endif; ?>

            <div class="popup-body">
                <?php if(!empty($popup_text)): ?>
                <div class="popup-text-content">
                    <?php echo nl2br(htmlspecialchars($popup_text)); ?>
                </div>
                <?php endif; ?>

                <?php if(!empty($popup_btn)): ?>
                <a href="<?php echo $popup_link; ?>" class="popup-action-btn">
                    <?php echo htmlspecialchars($popup_btn); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <button class="popup-bottom-close" onclick="closeHomePopup()">
            <i class="fa-solid fa-xmark"></i> CLOSE
        </button>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const popup = document.getElementById('homePopupOverlay');
        const popupClosed = localStorage.getItem('home_popup_closed');
        const popupTime = localStorage.getItem('home_popup_time');
        const now = new Date().getTime();
        
        // Show popup if not closed in last 24 hours
        if(popupClosed !== 'true' || !popupTime || (now - popupTime) > 24 * 60 * 60 * 1000) {
            popup.style.display = 'flex';
        }
    });

    function closeHomePopup() {
        document.getElementById('homePopupOverlay').style.display = 'none';
        localStorage.setItem('home_popup_closed', 'true');
        localStorage.setItem('home_popup_time', new Date().getTime());
    }
</script>
<?php endif; ?>


<?php if(!empty($notice_text)): ?>
<div class="container mx-auto px-4 mt-4">
    <div class="notice-box">
        <div class="notice-header">
            <span class="notice-title">Notice</span>
            <i class="fa-solid fa-xmark notice-close" onclick="this.closest('.notice-box').style.display='none'"></i>
        </div>
        <div class="notice-content">
            <?php echo nl2br(htmlspecialchars($notice_text)); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container mx-auto px-4">
    <?php 
    $slides_arr = [];
    if(isset($conn)) {
        $slider_query = $conn->query("SELECT * FROM sliders");
        if($slider_query){
            while($row = $slider_query->fetch_assoc()){ $slides_arr[] = $row; }
        }
    }
    $total_slides = count($slides_arr);
    ?>

    <div class="relative w-full sharp-edge shadow-sm bg-gray-100 slider-aspect group">
        <div id="slider" class="h-full w-full">
            <?php if($total_slides > 0): ?>
                <?php foreach($slides_arr as $slide): ?>
                    <a href="<?php echo $slide['link'] ? $slide['link'] : '#'; ?>" class="min-w-full h-full block select-none">
                        <img src="<?php echo $slide['image']; ?>" class="w-full h-full object-fill pointer-events-none" alt="Slide">
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-gray-400 font-normal">No Banners Found</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($total_slides > 1): ?>
    <div class="flex justify-center gap-2 mt-3 mb-6">
        <?php for($i = 0; $i < $total_slides; $i++): ?>
            <button onclick="goToSlide(<?php echo $i; ?>)" 
                    class="slider-dot h-1 w-6 sharp-edge transition-all duration-300 <?php echo ($i === 0) ? 'dot-active' : 'dot-inactive'; ?>" 
                    data-index="<?php echo $i; ?>">
            </button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div class="container mx-auto px-4 pb-6">
    <?php 
    if(isset($conn)) {
        // FILTER: Only show active categories
        $cat_query = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY priority ASC, id ASC");
        
        $has_categories = ($cat_query && $cat_query->num_rows > 0);
        
        function renderGameGrid($conn, $cat_id = null) {
            $sql = "SELECT * FROM games WHERE status = 'active'";
            if($cat_id !== null && $cat_id > 0) {
                $sql .= " AND category_id = $cat_id";
            } elseif($cat_id === 0) {
                $sql .= " AND (category_id = 0 OR category_id IS NULL)";
            }
            
            $games = $conn->query($sql);
            
            if($games && $games->num_rows > 0): ?>
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-6">
                    <?php while($game = $games->fetch_assoc()): ?>
                        <a href="game_detail.php?id=<?php echo $game['id']; ?>" class="block group">
                            <div class="sharp-edge overflow-hidden mb-2 relative bg-white shadow-sm">
                                <img src="<?php echo $game['image']; ?>" class="game-card-img" alt="<?php echo $game['name']; ?>">
                            </div>
                            
                            <div class="text-center">
                                <h3 class="text-black text-xs md:text-sm font-normal leading-tight uppercase">
                                    <?php echo $game['name']; ?>
                                </h3>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <?php endif; 
        }

        if($has_categories) {
            while($cat = $cat_query->fetch_assoc()) {
                // Check if this category actually has active games before showing title
                $check_games = $conn->query("SELECT id FROM games WHERE category_id = {$cat['id']} AND status = 'active' LIMIT 1");
                if($check_games && $check_games->num_rows > 0):
                ?>
                <div class="divider-container px-2">
                    <div class="divider-line"></div>
                    <h2 class="divider-text">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </h2>
                    <div class="divider-line"></div>
                </div>

                <?php renderGameGrid($conn, $cat['id']); ?>
                <?php
                endif;
            }
            
            // Check unassigned
            $uncat_chk = $conn->query("SELECT id FROM games WHERE (category_id = 0 OR category_id IS NULL) AND status = 'active' LIMIT 1");
            if($uncat_chk && $uncat_chk->num_rows > 0) {
                 ?>
                 <div class="divider-container px-2 mt-8">
                    <div class="divider-line"></div>
                    <h2 class="divider-text">Others</h2>
                    <div class="divider-line"></div>
                </div>
                <?php renderGameGrid($conn, 0); 
            }

        } else {
            // No Categories: Show All Active Games
            $all_chk = $conn->query("SELECT id FROM games WHERE status = 'active' LIMIT 1");
            if($all_chk && $all_chk->num_rows > 0):
            ?>
            <div class="divider-container px-2">
                <div class="divider-line"></div>
                <h2 class="divider-text">All Games</h2>
                <div class="divider-line"></div>
            </div>
            <?php renderGameGrid($conn, null); 
            endif;
        }
    } 
    ?>
</div>

<div class="container mx-auto px-4 pb-12">
    <div class="latest-orders-container">
        <h2 class="latest-orders-title">Latest Orders</h2>
        <p class="latest-orders-subtitle">সবচেয়ে সাম্প্রতিক <span>5টি অর্ডার</span> এক নজরে</p>
        
        <?php if(!empty($latest_orders)): ?>
            <?php foreach($latest_orders as $order): ?>
                <div class="order-card">
                    <div class="order-user-info">
                        <?php 
                        $u_img = !empty($order['user_image']) ? $order['user_image'] : 'res/images/default-avatar.png'; 
                        ?>
                        <img src="<?php echo $u_img; ?>" 
                             onerror="this.onerror=null;this.src='res/images/default-avatar.png';" 
                             alt="User" 
                             class="order-user-img">
                        
                        <div class="order-text-group">
                            <h4 class="order-user-name"><?php echo htmlspecialchars($order['user_name'] ?: 'Guest'); ?></h4>
                            <p class="order-details">
                                <?php echo htmlspecialchars($order['product_name']); ?> • ৳<?php echo number_format($order['amount'], 2); ?>
                            </p>
                        </div>
                    </div>
                    <div class="order-status">completed</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-sm py-4">No recent orders found.</p>
        <?php endif; ?>
    </div>
</div>


<script>
    let currentIdx = 0;
    const slider = document.getElementById('slider');
    const dots = document.querySelectorAll('.slider-dot');
    const total = <?php echo $total_slides; ?>;
    let autoPlayInterval;

    function updateSlider() {
        if(total <= 0 || !slider) return;
        slider.style.transform = `translateX(-${currentIdx * 100}%)`;
        dots.forEach((dot, index) => {
            dot.classList.toggle('dot-active', index === currentIdx);
            dot.classList.toggle('dot-inactive', index !== currentIdx);
        });
    }

    function startTimer() {
        if(total > 1) {
            autoPlayInterval = setInterval(() => {
                currentIdx = (currentIdx + 1) % total;
                updateSlider();
            }, 5000);
        }
    }

    function resetTimer() {
        clearInterval(autoPlayInterval);
        startTimer();
    }

    function goToSlide(index) {
        currentIdx = index;
        updateSlider();
        resetTimer();
    }

    function nextSlide() {
        currentIdx = (currentIdx + 1) % total;
        updateSlider();
    }

    function prevSlide() {
        currentIdx = (currentIdx - 1 + total) % total;
        updateSlider();
    }

    let isDragging = false, startPos = 0;

    if(slider) {
        slider.addEventListener('mousedown', dragStart);
        slider.addEventListener('touchstart', dragStart, {passive: true});
        slider.addEventListener('mouseup', dragEnd);
        slider.addEventListener('mouseleave', dragEnd);
        slider.addEventListener('touchend', dragEnd);
        slider.addEventListener('mousemove', dragAction);
        slider.addEventListener('touchmove', dragAction, {passive: true});
    }

    function dragStart(e) {
        isDragging = true;
        startPos = getPositionX(e);
        clearInterval(autoPlayInterval);
    }

    function dragAction(e) {
        if (!isDragging) return;
        const currentPosition = getPositionX(e);
        const diff = currentPosition - startPos;
        if (Math.abs(diff) > 50) {
            if (diff > 0) prevSlide(); else nextSlide();
            isDragging = false; 
            resetTimer(); 
        }
    }

    function dragEnd() {
        if(isDragging) {
            isDragging = false;
            startTimer();
        }
    }

    function getPositionX(e) { return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX; }

    startTimer();
</script>

<?php 
include 'common/footer.php'; 
include 'common/bottom.php'; 
?>