<?php 
include 'common/config.php'; 

// ====================================================
// 1. AJAX HANDLERS
// ====================================================

// A. Handle Balance Refresh
if(isset($_GET['action']) && $_GET['action'] == 'get_balance') {
    header('Content-Type: application/json');
    if(isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        $res = $conn->query("SELECT balance FROM users WHERE id=$uid");
        if ($res && $row = $res->fetch_assoc()) {
            echo json_encode(['balance' => (float)$row['balance']]);
        } else {
            echo json_encode(['balance' => 0]);
        }
    } else {
        echo json_encode(['balance' => 0]);
    }
    exit;
}

// B. Handle UID Check (HL Gaming + Fallback)
if(isset($_GET['action']) && $_GET['action'] == 'check_uid_api') {
    $uid = isset($_GET['uid']) ? trim($_GET['uid']) : '';
    
    if (empty($uid)) {
        echo json_encode(['error' => 'UID Required']);
        exit;
    }

    // --- CONFIGURATION ---
    $useruid = "YOUR_USER_UID"; 
    $apiKey = "YOUR_API_KEY";   
    $region = "bd";             

    // Primary API URL (HL Gaming)
    $primaryUrl = "https://proapis.hlgamingofficial.com/main/games/freefire/account/api?sectionName=AllData&PlayerUid=$uid&region=$region&useruid=$useruid&api=$apiKey";
    
    // Fallback API URL (bhauxinfo2)
    $fallbackUrl = "https://bhauxinfo2.vercel.app/bhau?uid=$uid&region=BD";

    // Helper Function
    function fetchUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpCode == 200) ? $response : false;
    }

    // Try Primary API
    $response = fetchUrl($primaryUrl);
    $finalResponse = null;

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['result']['AccountInfo']['AccountName'])) {
            $finalResponse = json_encode(['name' => $data['result']['AccountInfo']['AccountName']]);
        }
    }

    // Try Fallback API if Primary Failed
    if (!$finalResponse) {
        $response = fetchUrl($fallbackUrl);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['basicInfo']['nickname'])) {
                $finalResponse = json_encode(['name' => $data['basicInfo']['nickname']]);
            }
        }
    }

    // Output Result
    header('Content-Type: application/json');
    if ($finalResponse) {
        echo $finalResponse;
    } else {
        echo json_encode(['error' => 'Player Not Found']);
    }
    exit;
}

// ====================================================
// 2. PAGE LOAD LOGIC
// ====================================================
include 'common/header.php'; 

if(!isset($_GET['id'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

$id = (int)$_GET['id'];
$game_query = $conn->query("SELECT * FROM games WHERE id=$id");

if(!$game_query || $game_query->num_rows === 0) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

$game = $game_query->fetch_assoc();

// FIX: Normalize Game Type
$gameType = strtolower(trim($game['type']));

// Get Configuration for Subscription/UID
$hintText = !empty($game['hint_text']) ? $game['hint_text'] : 'Enter Player ID';
$isUidCheckEnabled = isset($game['check_uid']) ? (int)$game['check_uid'] : 1; 

// CHECK: Ensure 'status' column exists in products table
if(isset($conn)) {
    $chk_st = $conn->query("SHOW COLUMNS FROM products LIKE 'status'");
    if($chk_st->num_rows == 0) {
        $conn->query("ALTER TABLE products ADD COLUMN status TINYINT DEFAULT 1");
    }
}

$products = $conn->query("SELECT * FROM products WHERE game_id=$id ORDER BY price ASC");

// Initial Balance
$user_balance = 0.00;
if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $ub_res = $conn->query("SELECT balance FROM users WHERE id=$uid");
    if($ub_res && $row = $ub_res->fetch_assoc()){
        $user_balance = (float)$row['balance'];
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Lato:wght@400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* =========================================
       COLOR CONFIGURATION
       ========================================= */
    :root {
        --primary-color: #DC2626; /* Fallback Red */
    }

    /* Global & Fonts */
    .support-fab, .floating-contact, .whatsapp-float, #support-btn { display: none !important; }
    body { background-color: #f0f5f9; padding-bottom: 80px; } 
    .font-bree { font-family: 'Bree Serif', serif; }
    .font-bangla { font-family: 'Noto Serif Bengali', serif; }
    .font-lato { font-family: 'Lato', sans-serif; }

    /* Number Badge */
    .step-badge {
        background-color: var(--primary-color);
        color: white; width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Bree Serif', serif; font-size: 16px; padding-top: 2px;
    }

    /* Product Card */
    .pkg-card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.2s;
        min-height: 60px; 
        position: relative; 
    }
    
    .pkg-radio:checked + .pkg-card {
        border-color: var(--primary-color);
        background-color: #fff1f2; 
        box-shadow: 0 0 0 1px var(--primary-color);
    }
    .pkg-title { color: #1f2937; font-weight: 700; font-size: 13px; line-height: 1.2; }
    .pkg-price { color: var(--primary-color); font-weight: 600; font-size: 12px; margin-top: 2px; }

    /* Buttons */
    .btn-check-bg {
        background-image: url('res/backgrounds/buttonbg.png');
        background-size: cover; background-position: center;
        color: white; 
        text-shadow: none !important;
        border: none;
    }
    .btn-valid-name {
        color: white;
        border: none;
        box-shadow: none;
    }

    /* Payment Method Styling */
    .pay-card { transition: none !important; border: 1px solid #e5e7eb; }
    
    .pay-radio:checked + .pay-card { 
        border: 1px solid #e5e7eb;
        position: relative; 
    }
    .pay-radio:checked + .pay-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 0; height: 0;
        border-style: solid; border-width: 24px 24px 0 0;
        border-color: var(--primary-color) transparent transparent transparent; z-index: 1;
    }
    .pay-radio:checked + .pay-card::after {
        content: '✓'; position: absolute; top: -2px; left: 3px;
        color: white; font-size: 12px; font-weight: bold; z-index: 2;
    }

    /* 3D Icon */
    .game-icon-3d {
        box-shadow: 6px 6px 0px rgba(0,0,0,0.2); 
        border: 2px solid white;
        transform: translateY(-2px);
    }
    
    /* Errors & Popups */
    .error-msg-box {
        background-color: #fee2e2;
        border: 1px solid var(--primary-color);
        color: #b91c1c;
        font-size: 12px;
        padding: 8px;
        border-radius: 6px;
        margin-top: 8px;
        display: none; 
        align-items: center;
        gap: 6px;
        font-family: 'Noto Serif Bengali', serif;
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    
    .input-error { border: 1px solid var(--primary-color) !important; background-color: #fef2f2; }
    .sharp-popup { border-radius: 0px !important; box-shadow: 0 0 0 1000px rgba(0,0,0,0.8); aspect-ratio: 3/2; display: flex; flex-direction: column; justify-content: center; align-items: center; width: 90%; max-width: 340px; }
    .sharp-btn { border-radius: 0px !important; text-transform: uppercase; letter-spacing: 1px; font-weight: 900; }
    
    /* Notice Fonts */
    .notice-title { font-family: 'Lato', sans-serif !important; font-weight: 900; letter-spacing: 0.5px; }
    .notice-content { font-family: 'Noto Serif Bengali', serif; line-height: 1.6; }

    /* Dynamic Classes */
    .bg-primary { background-color: var(--primary-color) !important; }
    .text-primary { color: var(--primary-color) !important; }
    .border-primary { border-color: var(--primary-color) !important; }
    .hover-text-primary:hover { color: var(--primary-color) !important; }
    .focus-ring-primary:focus { outline: none; border-color: var(--primary-color); }
    
    /* Stock Out Styles */
    .pkg-disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .stock-out-badge {
        position: absolute;
        top: 8px; right: -25px;
        background-color: #ef4444;
        color: white;
        font-size: 9px;
        font-weight: 800;
        padding: 2px 25px;
        text-transform: uppercase;
        transform: rotate(45deg);
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        z-index: 10;
        pointer-events: none;
    }
</style>

<div class="w-full bg-primary px-4 py-6 flex items-center gap-5 shadow-lg relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('res/pattern.png')]"></div>
    <div class="relative z-10 w-20 h-20 rounded-xl overflow-hidden game-icon-3d bg-gray-900">
        <img src="<?php echo $game['image']; ?>" class="w-full h-full object-cover">
    </div>
    <div class="relative z-10">
        <h1 class="text-white font-bree text-2xl drop-shadow-md"><?php echo $game['name']; ?></h1>
        <div class="h-1 w-12 bg-white/30 rounded mt-2"></div>
    </div>
</div>

<form action="instantpay.php" method="POST" id="topupForm" class="container mx-auto px-3 mt-5 max-w-lg" onsubmit="return validateForm(event)">
    <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
    <input type="hidden" name="action" value="create_order">
    <input type="hidden" name="order_txid" id="orderTxid">
    
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-4">
        <h3 class="font-bree text-gray-800 text-lg mb-4 flex items-center gap-3 border-b pb-3">
            <span class="step-badge">1</span> Select Package
        </h3>
        
        <div class="grid grid-cols-2 gap-3">
            <?php while($prod = $products->fetch_assoc()): 
                // ফিক্স: সঠিকভাবে স্ট্যাটাস চেক করা
                $isActive = true; // ডিফল্ট true
                
                // যদি status কলাম থাকে এবং এর মান 0 বা '0' বা 'inactive' হয় তাহলে false
                if(isset($prod['status'])) {
                    if($prod['status'] === 'inactive' || $prod['status'] === 0 || $prod['status'] === '0') {
                        $isActive = false;
                    }
                }
                
                // শুধুমাত্র inactive প্রোডাক্ট ডিজেবল হবে
                $disabledAttr = $isActive ? '' : 'disabled';
                $cursorClass = $isActive ? 'cursor-pointer' : 'cursor-not-allowed pkg-disabled';
                
                // active প্রোডাক্টের জন্য onclick ইভেন্ট যোগ করা
                $onclickAttr = $isActive ? "selectProduct(this)" : "";
            ?>
            <label class="<?php echo $cursorClass; ?> relative group select-none">
                <input type="radio" name="product_id" value="<?php echo $prod['id']; ?>" 
                       data-price="<?php echo $prod['price']; ?>" 
                       data-pname="<?php echo $prod['name']; ?>" 
                       class="pkg-radio sr-only" 
                       onchange="updateTotal()" 
                       <?php echo $disabledAttr; ?>>
                
                <div class="pkg-card p-2 h-full flex flex-col justify-center items-center" onclick="<?php echo $onclickAttr; ?>">
                    <span class="pkg-title font-bree text-center block">
                        <?php echo $prod['name']; ?>
                    </span>
                    <span class="pkg-price font-bree">
                        BDT <?php echo number_format((float)$prod['price'], 0); ?>
                    </span>
                    
                    <?php if(!$isActive): ?>
                        <div class="stock-out-badge">STOCK OUT</div>
                    <?php endif; ?>
                </div>
            </label>
            <?php endwhile; ?>
        </div>
        
        <div id="pkgError" class="error-msg-box">
            <i class="fa-solid fa-circle-exclamation"></i> অনুগ্রহ করে একটি প্যাকেজ সিলেক্ট করুন
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-4" id="accountInfoSection" style="<?php echo ($gameType == 'voucher') ? 'display:none;' : ''; ?>">
        <h3 class="font-bree text-gray-800 text-lg mb-4 flex items-center gap-3 border-b pb-3">
            <span class="step-badge">2</span> Account Info
        </h3>
        
        <div class="space-y-4">
            <div>
                <label class="text-sm text-gray-600 block mb-2 font-bangla font-semibold" id="inputLabel">
                    <?php echo ($gameType == 'subscription') ? htmlspecialchars($hintText) : 'এখানে প্লেয়ার আইডি কোড দিন'; ?>
                </label>
                
                <input type="text" id="playerIdInput" name="player_id" 
                       placeholder="<?php echo ($gameType == 'subscription') ? 'Type here...' : 'এখানে প্লেয়ার আইডি কোড দিন'; ?>" 
                       class="w-full border border-gray-300 rounded-lg p-3 text-sm font-bangla focus-ring-primary" 
                       autocomplete="off">
                
                <div id="idError" class="error-msg-box">
                    <i class="fa-solid fa-circle-exclamation"></i> দয়া করে প্রয়োজনীয় তথ্য দিন
                </div>
            </div>
            
            <?php if($gameType == 'uid' && $isUidCheckEnabled == 1): ?>
            <button type="button" id="btnCheckName" onclick="checkUidName()" class="w-full btn-check-bg py-3 rounded-lg font-bangla text-base font-semibold hover:opacity-95 transition-all shadow-sm flex items-center justify-center gap-2">
                আপনার গেম আইডির নাম চেক করুন
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if($gameType == 'voucher'): ?>
        <input type="hidden" name="player_id" value="Voucher_Request_No_ID">
    <?php endif; ?>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-4">
        <h3 class="font-bree text-gray-800 text-lg mb-4 flex items-center gap-3 border-b pb-3">
            <span class="step-badge">3</span> Select one option
        </h3>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <label class="cursor-pointer relative">
                <input type="radio" name="payment_method" value="wallet" checked class="pay-radio sr-only" onchange="toggleWalletCheck(true)">
                <div class="pay-card border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm h-full flex flex-col">
                    <div class="p-4 flex-1 flex items-center justify-center bg-white">
                        <img src="res/images/walletpay.png" class="h-10 object-contain" alt="Wallet">
                    </div>
                    <div class="bg-[#d1d5db] text-center py-1.5 text-xs font-bold text-gray-700 uppercase">
                        Wallet Pay
                    </div>
                </div>
            </label>

            <label class="cursor-pointer relative">
                <input type="radio" name="payment_method" value="online" class="pay-radio sr-only" onchange="toggleWalletCheck(false)">
                <div class="pay-card border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm h-full flex flex-col">
                    <div class="p-2 flex-1 flex flex-col items-center justify-center bg-white">
                        <img src="res/images/instantpay.png" class="h-8 object-contain mb-1" alt="Instant">
                    </div>
                    <div class="bg-[#d1d5db] text-center py-1.5 text-xs font-bold text-gray-700 uppercase">
                        Instant Pay
                    </div>
                </div>
            </label>
        </div>

        <div class="text-sm text-gray-600 space-y-2 pl-1 mb-4">
            <p class="flex items-center gap-2 font-bangla text-gray-500">
                <i class="fa-solid fa-circle-info"></i> আপনার অ্যাকাউন্ট ব্যালেন্স 
                <span class="text-primary font-bold">৳ <span id="userBalanceDisplay"><?php echo number_format($user_balance, 2); ?></span></span> 
                <button type="button" onclick="refreshBalance()" class="text-gray-400 hover-text-primary transition-transform active:rotate-180">
                    <i class="fa-solid fa-rotate-right" id="refreshIcon"></i>
                </button>
            </p>
            <p class="flex items-center gap-2 font-bangla text-gray-500">
                <i class="fa-solid fa-circle-info"></i> প্রোডাক্ট কিনতে আপনার প্রয়োজন <span class="font-bold text-primary">৳ <span id="neededPrice">0</span></span>
            </p>
            
            <div id="balanceError" class="error-msg-box">
                <i class="fa-solid fa-circle-exclamation"></i> আপনার ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই!
            </div>
        </div>

        <button type="submit" id="mainSubmitBtn" class="w-full bg-primary hover:opacity-90 text-white font-bree py-3.5 rounded-lg shadow-md transition-all active:scale-95 text-xl tracking-wide">
            Buy Now
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-gray-800 text-lg notice-title">Rules & Conditions</h3>
        </div>
        <div class="p-4 text-sm text-gray-700 leading-relaxed space-y-2 notice-content" style="white-space: pre-wrap; word-wrap: break-word;">
            <?php 
            if(!empty($game['description'])) {
                echo htmlspecialchars($game['description']); 
            } else {
                echo "No rules found.";
            }
            ?>
        </div>
    </div>
</form>

<div id="confirmPopup" class="fixed inset-0 hidden z-[999] flex items-center justify-center">
    <div class="bg-white p-6 shadow-2xl relative sharp-popup">
        <div class="w-full flex flex-col items-center justify-center text-center">
            <div class="text-5xl mb-4 text-primary"><i class="fa-solid fa-circle-question"></i></div>
            <h2 class="text-xl font-black mb-2 uppercase tracking-wide text-gray-900 font-lato">Confirm Purchase?</h2>
            <div id="confirmDetails" class="text-gray-600 text-xs mb-6 font-bold leading-relaxed px-4"></div>
            <div class="flex gap-2 w-full">
                <button onclick="closeConfirm()" class="flex-1 py-3 text-gray-800 sharp-btn bg-gray-200">CANCEL</button>
                <button onclick="submitFinalForm()" class="flex-1 py-3 text-white sharp-btn bg-primary">CONFIRM</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPrice = 0;
    let selectedPackageName = "";
    let userBalance = <?php echo $user_balance; ?>;
    let isWalletSelected = true;

    // --- প্রোডাক্ট সিলেক্ট করার ফাংশন ---
    function selectProduct(element) {
        // প্যারেন্ট লেবেল খুঁজে বের করা
        const label = element.closest('label');
        if(label) {
            // লেবেলের ভিতরে radio ইনপুট খুঁজে বের করা
            const radio = label.querySelector('input[type="radio"]');
            if(radio && !radio.disabled) {
                radio.checked = true;
                // ম্যানুয়ালি onchange ইভেন্ট ট্রিগার করা
                radio.dispatchEvent(new Event('change', { bubbles: true }));
                updateTotal();
            }
        }
    }

    function generateTXID() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let result = '';
        for (let i = 0; i < 10; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    function checkUidName() {
        const uidInput = document.getElementById('playerIdInput');
        const btn = document.getElementById('btnCheckName');
        
        if(!uidInput) return;

        const uid = uidInput.value.trim();

        if(uid === "") {
            document.getElementById('idError').style.display = 'flex';
            uidInput.classList.add('input-error');
            return;
        }

        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Checking...';
        btn.disabled = true;

        fetch('game_detail.php?action=check_uid_api&uid=' + uid)
            .then(response => response.json())
            .then(data => {
                let foundName = "";
                
                if(data.name) {
                    foundName = data.name;
                } else if(data.basicInfo && data.basicInfo.nickname) {
                    foundName = data.basicInfo.nickname;
                } else if(data.nickname) {
                    foundName = data.nickname;
                } else if(data.PlayerName) {
                    foundName = data.PlayerName;
                }

                if (foundName) {
                    btn.innerHTML = foundName;
                } else {
                    btn.innerHTML = 'Player Not Found';
                    setTimeout(() => {
                        btn.innerHTML = 'আপনার গেম আইডির নাম চেক করুন';
                        btn.disabled = false;
                    }, 3000);
                }
            })
            .catch(error => {
                btn.innerHTML = 'Error Checking';
                setTimeout(() => {
                    btn.innerHTML = 'আপনার গেম আইডির নাম চেক করুন';
                    btn.disabled = false;
                }, 3000);
            });
    }

    function updateTotal() {
        const radios = document.getElementsByName('product_id');
        for(let r of radios) {
            if(r.checked) {
                currentPrice = parseFloat(r.getAttribute('data-price'));
                selectedPackageName = r.getAttribute('data-pname');
                break;
            }
        }
        const needed = document.getElementById('neededPrice');
        if(needed) needed.innerText = currentPrice;
        
        const err = document.getElementById('pkgError');
        if(err) err.style.display = 'none';
    }

    function toggleWalletCheck(isWallet) {
        isWalletSelected = isWallet;
        const balErr = document.getElementById('balanceError');
        if(balErr) balErr.style.display = 'none';
    }

    function refreshBalance() {
        const icon = document.getElementById('refreshIcon');
        const display = document.getElementById('userBalanceDisplay');
        if(icon) icon.classList.add('fa-spin');
        
        fetch('game_detail.php?action=get_balance')
            .then(res => res.json())
            .then(data => {
                userBalance = parseFloat(data.balance);
                if(display) display.innerText = userBalance.toFixed(2);
                if(icon) setTimeout(() => icon.classList.remove('fa-spin'), 500);
            })
            .catch(() => { if(icon) icon.classList.remove('fa-spin'); });
    }

    function validateForm(e) {
        e.preventDefault();

        let valid = true;
        document.querySelectorAll('.error-msg-box').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
        
        if(currentPrice === 0) {
            const pkgErr = document.getElementById('pkgError');
            if(pkgErr) pkgErr.style.display = 'flex';
            valid = false;
        }

        const playerId = document.getElementById('playerIdInput');
        if(playerId && document.body.contains(playerId) && playerId.offsetParent !== null) {
            if(playerId.value.trim() === "") {
                const idErr = document.getElementById('idError');
                if(idErr) idErr.style.display = 'flex';
                playerId.classList.add('input-error');
                valid = false;
            }
        }

        if(isWalletSelected && userBalance < currentPrice) {
            const balErr = document.getElementById('balanceError');
            if(balErr) balErr.style.display = 'flex';
            valid = false;
        }

        if(valid) {
            if(isWalletSelected) {
                const pidVal = (playerId && playerId.offsetParent !== null) ? playerId.value : 'N/A';
                document.getElementById('confirmDetails').innerHTML = `
                    <p>Package: ${selectedPackageName}</p>
                    <p>Total: ৳${currentPrice}</p>
                    <p>Details: ${pidVal}</p>
                `;
                document.getElementById('confirmPopup').classList.remove('hidden');
            } else {
                const orderTx = document.getElementById('orderTxid');
                if(orderTx) orderTx.value = generateTXID();
                
                const form = document.getElementById('topupForm');
                form.action = 'instantpay.php';
                
                const btn = document.getElementById('mainSubmitBtn');
                if(btn) btn.disabled = true;
                
                form.submit();
            }
        }
        return false;
    }

    function closeConfirm() {
        document.getElementById('confirmPopup').classList.add('hidden');
    }

    function submitFinalForm() {
        const orderTx = document.getElementById('orderTxid');
        if(orderTx) orderTx.value = generateTXID();
        
        const form = document.getElementById('topupForm');
        form.action = 'order.php';
        
        const btn = document.getElementById('mainSubmitBtn');
        if(btn) btn.disabled = true;
        
        form.submit();
    }
    
    updateTotal();
</script>

<?php 
include 'common/footer.php'; 
include 'common/bottom.php'; 
?>