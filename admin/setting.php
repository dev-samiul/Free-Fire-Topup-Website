<?php 
include 'common/header.php'; 

// ====================================================
// 1. HANDLE FORM SUBMISSION
// ====================================================
if(isset($_POST['update'])) {
    
    // A. Handle All Text Inputs (Automated)
    foreach($_POST as $key => $val) {
        if($key == 'update' || $key == 'new_pass') continue; 
        
        $val = $conn->real_escape_string($val);
        
        // Update or Insert Logic
        $check = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($check->num_rows > 0) {
            $conn->query("UPDATE settings SET value='$val' WHERE name='$key'");
        } else {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
        }
    }
    
    // B. Handle File Uploads Helper Function
    function uploadAsset($conn, $inputName, $settingKey, $fileNameBase) {
        if(!empty($_FILES[$inputName]['name'])) {
            $target_dir = "../res/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
            $target_file = $target_dir . $fileNameBase . "." . $ext;
            
            if(move_uploaded_file($_FILES[$inputName]['tmp_name'], $target_file)) {
                $db_path = "res/" . $fileNameBase . "." . $ext;
                
                $chk = $conn->query("SELECT id FROM settings WHERE name='$settingKey'");
                if($chk->num_rows > 0){
                    $conn->query("UPDATE settings SET value='$db_path' WHERE name='$settingKey'");
                } else {
                    $conn->query("INSERT INTO settings (name, value) VALUES ('$settingKey', '$db_path')");
                }
            }
        }
    }

    // Process Images
    uploadAsset($conn, 'app_logo', 'site_logo', 'logo');
    uploadAsset($conn, 'favicon_file', 'favicon_url', 'favicon');
    uploadAsset($conn, 'pwa_file', 'pwa_icon', 'pwa-icon');
    uploadAsset($conn, 'og_file', 'og_image', 'og-image');
    uploadAsset($conn, 'twitter_file', 'twitter_image', 'twitter-card');
    
    // C. Handle Admin Password
    if(!empty($_POST['new_pass'])) {
        $np = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $aid = $_SESSION['admin_id'];
        $conn->query("UPDATE admins SET password='$np' WHERE id=$aid");
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Configuration updated successfully.',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#eab308',
                timer: 1500,
                showConfirmButton: false
            });
        });
    </script>";
}

// Helper to fetch value
function getVal($conn, $key) {
    $q = $conn->query("SELECT value FROM settings WHERE name='$key'");
    return ($q && $q->num_rows > 0) ? htmlspecialchars($q->fetch_assoc()['value']) : '';
}

// Defaults for preview
$current_color = getVal($conn, 'site_color');
if(empty($current_color)) $current_color = "#DC2626";

// Image Previews Helper
function getImg($conn, $key, $default="../res/logo.png") {
    $val = getVal($conn, $key);
    if(empty($val)) return $default;
    return "../" . $val . "?v=" . time();
}

function getFilename($conn, $key) {
    $val = getVal($conn, $key);
    if(empty($val)) return "default.png";
    return basename($val);
}

$logo_preview = getImg($conn, 'site_logo');
$logo_name = getFilename($conn, 'site_logo');

$fav_preview = getImg($conn, 'favicon_url', $logo_preview);
$fav_name = getFilename($conn, 'favicon_url');

$pwa_preview = getImg($conn, 'pwa_icon', $logo_preview);
$pwa_name = getFilename($conn, 'pwa_icon');

$og_preview = getImg($conn, 'og_image', $logo_preview);
$og_name = getFilename($conn, 'og_image');

$tw_preview = getImg($conn, 'twitter_image', $logo_preview);
$tw_name = getFilename($conn, 'twitter_image');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* DARK THEME */
    :root {
        --bg-body: #050505;
        --bg-card: #111111;
        --bg-input: #1a1a1a;
        --border: #222222;
        --text-primary: #ffffff;
        --text-secondary: #9ca3af;
        --accent: #eab308;
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    .container-fluid { padding: 20px; max-width: 1000px; margin: 0 auto; }

    /* HEADER */
    .page-header { margin-bottom: 20px; }
    .page-title { font-size: 24px; font-weight: 700; margin: 0; }

    /* TABS NAVIGATION */
    .tabs-nav {
        display: flex; gap: 10px; margin-bottom: 25px;
        border-bottom: 1px solid var(--border); padding-bottom: 10px;
        overflow-x: auto;
    }
    
    .tab-btn {
        background: transparent; border: none;
        color: var(--text-secondary); font-weight: 600; font-size: 14px;
        padding: 10px 15px; cursor: pointer; white-space: nowrap;
        display: flex; align-items: center; gap: 8px;
        border-radius: 6px; transition: all 0.2s;
    }
    
    .tab-btn:hover { background: #1a1a1a; color: #fff; }
    
    .tab-btn.active {
        background: #1a1a1a; color: var(--accent);
        border: 1px solid var(--border);
    }

    /* TAB CONTENT */
    .tab-pane { display: none; animation: fadeIn 0.3s ease; }
    .tab-pane.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* CARDS */
    .flat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
    }

    /* FORM ELEMENTS */
    .input-group { margin-bottom: 20px; }
    .input-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
    .input-label span { color: var(--accent); } /* Asterisk */
    
    .input-field, .textarea-field {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        color: #fff;
        outline: none;
        transition: border-color 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .input-field:focus, .textarea-field:focus { border-color: #444; }
    
    .input-field.mono { font-family: monospace; font-size: 13px; color: #eab308; }

    /* TAG INPUT STYLES */
    .tag-input-container {
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        min-height: 48px;
    }
    .tag-item {
        background: rgba(234, 179, 8, 0.15);
        border: 1px solid rgba(234, 179, 8, 0.3);
        color: var(--accent);
        border-radius: 4px; padding: 4px 10px; font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 6px;
    }
    .tag-close { cursor: pointer; font-size: 14px; opacity: 0.7; transition: opacity 0.2s; }
    .tag-close:hover { opacity: 1; color: #fff; }
    .tag-input-box { background: transparent; border: none; color: #fff; outline: none; font-size: 14px; flex: 1; min-width: 120px; padding: 4px; }

    /* COLOR PICKER STYLES */
    .color-picker-wrapper {
        display: flex; align-items: center; gap: 10px;
        background: var(--bg-input); border: 1px solid var(--border);
        padding: 6px; border-radius: 8px;
    }
    .custom-color-input {
        -webkit-appearance: none; border: none; width: 50px; height: 40px;
        cursor: pointer; background: none; padding: 0;
    }
    .custom-color-input::-webkit-color-swatch-wrapper { padding: 0; }
    .custom-color-input::-webkit-color-swatch { border: none; border-radius: 6px; border: 1px solid #333; }
    .color-hex-text {
        flex: 1; background: transparent; border: none; color: #fff;
        font-family: monospace; font-size: 15px; text-transform: uppercase;
        outline: none; padding-left: 10px;
    }

    /* SMALL PREVIEW CARD */
    .media-upload-container {
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
    }
    .small-preview-card {
        background: #222; border: 1px solid #333; border-radius: 6px;
        padding: 8px; display: flex; align-items: center; gap: 12px;
        position: relative; overflow: hidden;
    }
    .preview-thumb {
        width: 40px; height: 40px; background: #1a1a1a; border-radius: 4px;
        display: flex; align-items: center; justify-content: center; padding: 2px;
    }
    .preview-thumb img { max-width: 100%; max-height: 100%; object-fit: contain; }
    
    .preview-info { flex: 1; overflow: hidden; }
    .preview-filename { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .preview-size { font-size: 11px; color: var(--text-secondary); }

    .remove-btn {
        background: transparent; border: none; color: #666; cursor: pointer;
        font-size: 16px; padding: 4px; z-index: 10;
    }
    .remove-btn:hover { color: #ef4444; }

    /* SUCCESS EFFECT */
    .small-preview-card.staged-success::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 50%;
        background: linear-gradient(to bottom, rgba(34, 197, 94, 0.5) 0%, transparent 100%);
        z-index: 0; pointer-events: none; animation: slideDownHalf 0.3s ease-out;
    }
    .preview-thumb, .preview-info, .remove-btn { position: relative; z-index: 1; }
    @keyframes slideDownHalf { from { height: 0%; opacity: 0; } to { height: 50%; opacity: 1; } }

    /* STICKY FOOTER */
    .sticky-footer {
        position: fixed; bottom: 0; left: 0; width: 100%;
        background: var(--bg-card); border-top: 1px solid var(--border);
        padding: 15px; z-index: 50; display: flex; justify-content: flex-end;
    }
    @media (min-width: 768px) {
        .sticky-footer { position: static; background: transparent; border: none; padding: 0; margin-top: 30px; margin-bottom: 50px; }
    }

    /* BUTTONS */
    .btn-save {
        background: var(--accent); color: #000; font-weight: 700; padding: 12px 30px;
        border-radius: 8px; border: none; cursor: pointer; font-size: 14px;
        transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-save:hover { filter: brightness(1.1); }
    
    .divider-text {
        font-size: 11px; font-weight: 700; text-transform: uppercase; 
        color: var(--accent); margin: 25px 0 15px 0; letter-spacing: 1px;
    }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Settings</h1>
    </div>

    <div class="tabs-nav">
        <button class="tab-btn active" onclick="openTab(event, 'general')">
            <i class="fa-solid fa-sliders"></i> General
        </button>
        <button class="tab-btn" onclick="openTab(event, 'graphics')">
            <i class="fa-solid fa-image"></i> Graphics
        </button>
        <button class="tab-btn" onclick="openTab(event, 'apis')">
            <i class="fa-solid fa-gamepad"></i> Api Service
        </button>
        <button class="tab-btn" onclick="openTab(event, 'seo')">
            <i class="fa-solid fa-magnifying-glass"></i> SEO & Meta
        </button>
        <button class="tab-btn" onclick="openTab(event, 'social')">
            <i class="fa-solid fa-share-nodes"></i> Social
        </button>
        <button class="tab-btn" onclick="openTab(event, 'notice')">
            <i class="fa-solid fa-bullhorn"></i> Notice
        </button>
        <button class="tab-btn" onclick="openTab(event, 'firebase')">
            <i class="fa-solid fa-fire"></i> Firebase
        </button>
        <button class="tab-btn" onclick="openTab(event, 'security')">
            <i class="fa-solid fa-lock"></i> Security
        </button>
    </div>

    <form method="POST" enctype="multipart/form-data" id="settingsForm">
        
        <div id="general" class="tab-pane active">
            <div class="flat-card">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="input-group">
                        <label class="input-label">Site Name (Global) <span>*</span></label>
                        <input type="text" name="site_name" value="<?php echo getVal($conn, 'site_name'); ?>" class="input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Site Title (Browser Tab) <span>*</span></label>
                        <input type="text" name="site_title" value="<?php echo getVal($conn, 'site_title'); ?>" class="input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Home Page Heading</label>
                        <input type="text" name="home_title" value="<?php echo getVal($conn, 'home_title'); ?>" class="input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Paginate Per Page</label>
                        <input type="number" name="paginate_per_page" value="<?php echo getVal($conn, 'paginate_per_page'); ?>" class="input-field" placeholder="10">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Currency Symbol</label>
                        <input type="text" name="currency" value="<?php echo getVal($conn, 'currency'); ?>" class="input-field text-center font-bold">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">App Download Link</label>
                    <input type="text" name="download_link" value="<?php echo getVal($conn, 'download_link'); ?>" class="input-field" placeholder="https://drive.google.com/...">
                    <p style="font-size:11px; color:#666; margin-top:5px;">This triggers the popup on the user site.</p>
                </div>

                <div class="input-group">
                    <label class="input-label">Site Main Color</label>
                    <div class="color-picker-wrapper">
                        <input type="color" id="pickerInput" value="<?php echo $current_color; ?>" class="custom-color-input">
                        <input type="text" id="hexInput" name="site_color" value="<?php echo $current_color; ?>" class="color-hex-text" placeholder="#RRGGBB" maxlength="7">
                    </div>
                </div>
            </div>
        </div>

        <div id="graphics" class="tab-pane">
            <div class="flat-card">
                
                <div class="input-group">
                    <label class="input-label">Main App Logo</label>
                    <div class="media-upload-container">
                        <div class="small-preview-card">
                            <div class="preview-thumb"><img src="<?php echo $logo_preview; ?>" id="logo-img"></div>
                            <div class="preview-info">
                                <div class="preview-filename" id="logo-name"><?php echo $logo_name; ?></div>
                                <div class="preview-size" id="logo-size">Current File</div>
                            </div>
                            <button type="button" class="remove-btn" onclick="clearUpload('app_logo', 'logo-img', 'logo-name', 'logo-size', '<?php echo $logo_preview; ?>', '<?php echo $logo_name; ?>')">&times;</button>
                        </div>
                        <input type="file" name="app_logo" id="app_logo" class="input-field mt-3" style="font-size:12px;">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Favicon (Browser Tab Icon)</label>
                    <div class="media-upload-container">
                        <div class="small-preview-card">
                            <div class="preview-thumb"><img src="<?php echo $fav_preview; ?>" id="fav-img"></div>
                            <div class="preview-info">
                                <div class="preview-filename" id="fav-name"><?php echo $fav_name; ?></div>
                                <div class="preview-size" id="fav-size">Current File</div>
                            </div>
                            <button type="button" class="remove-btn" onclick="clearUpload('favicon_file', 'fav-img', 'fav-name', 'fav-size', '<?php echo $fav_preview; ?>', '<?php echo $fav_name; ?>')">&times;</button>
                        </div>
                        <input type="file" name="favicon_file" id="favicon_file" class="input-field mt-3" style="font-size:12px;">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">PWA Icon (Add to Home Screen)</label>
                    <div class="media-upload-container">
                        <div class="small-preview-card">
                            <div class="preview-thumb"><img src="<?php echo $pwa_preview; ?>" id="pwa-img"></div>
                            <div class="preview-info">
                                <div class="preview-filename" id="pwa-name"><?php echo $pwa_name; ?></div>
                                <div class="preview-size" id="pwa-size">Current File</div>
                            </div>
                            <button type="button" class="remove-btn" onclick="clearUpload('pwa_file', 'pwa-img', 'pwa-name', 'pwa-size', '<?php echo $pwa_preview; ?>', '<?php echo $pwa_name; ?>')">&times;</button>
                        </div>
                        <input type="file" name="pwa_file" id="pwa_file" class="input-field mt-3" style="font-size:12px;">
                    </div>
                </div>
            </div>
        </div>

        <div id="apis" class="tab-pane">
            <div class="flat-card">
                
                <h3 class="divider-text">UniPin Configuration</h3>
                
                <div class="grid grid-cols-1 gap-5">
                    <div class="input-group">
                        <label class="input-label">UniPin Base API URL</label>
                        <input type="text" name="unipin_base_url" value="<?php echo getVal($conn, 'unipin_base_url'); ?>" class="input-field mono" placeholder="https://api.example.com">
                    </div>
                    <div class="input-group">
                        <label class="input-label">UniPin API Key</label>
                        <input type="text" name="unipin_api_key" value="<?php echo getVal($conn, 'unipin_api_key'); ?>" class="input-field mono" placeholder="Enter API Key">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border); margin: 30px 0;">

                <h3 class="divider-text">UID To Name Check API</h3>

                <div class="grid grid-cols-1 gap-5">
                    <div class="input-group">
                        <label class="input-label">UID Check Base URL</label>
                        <input type="text" name="uid_api_url" value="<?php echo getVal($conn, 'uid_api_url'); ?>" class="input-field mono" placeholder="https://api.example.com/check?uid=">
                    </div>
                    <div class="input-group">
                        <label class="input-label">API Key (Optional)</label>
                        <input type="text" name="uid_api_key" value="<?php echo getVal($conn, 'uid_api_key'); ?>" class="input-field mono" placeholder="If required by the API">
                    </div>
                </div>

            </div>
        </div>

        <div id="seo" class="tab-pane">
            <div class="flat-card">
                <div class="input-group">
                    <label class="input-label">Meta Description</label>
                    <textarea name="meta_desc" class="textarea-field" style="height:80px;"><?php echo getVal($conn, 'meta_desc'); ?></textarea>
                </div>

                <div class="input-group">
                    <label class="input-label">Meta Keywords</label>
                    <div class="tag-input-container" id="tagContainer">
                        <input type="text" id="tagInput" class="tag-input-box" placeholder="Type tag & hit Enter or Paste CSV...">
                    </div>
                    <input type="hidden" name="keywords" id="hiddenKeywords" value="<?php echo getVal($conn, 'keywords'); ?>">
                    <p style="font-size:11px; color:#666; margin-top:5px;">Press Enter, Comma, or Paste comma-separated tags.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="input-group">
                        <label class="input-label">Facebook OG Image</label>
                        <div class="media-upload-container">
                            <div class="small-preview-card">
                                <div class="preview-thumb"><img src="<?php echo $og_preview; ?>" id="og-img"></div>
                                <div class="preview-info">
                                    <div class="preview-filename" id="og-name"><?php echo $og_name; ?></div>
                                    <div class="preview-size" id="og-size">Current File</div>
                                </div>
                                <button type="button" class="remove-btn" onclick="clearUpload('og_file', 'og-img', 'og-name', 'og-size', '<?php echo $og_preview; ?>', '<?php echo $og_name; ?>')">&times;</button>
                            </div>
                            <input type="file" name="og_file" id="og_file" class="input-field mt-3" style="font-size:12px;">
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Twitter Card Image</label>
                        <div class="media-upload-container">
                            <div class="small-preview-card">
                                <div class="preview-thumb"><img src="<?php echo $tw_preview; ?>" id="tw-img"></div>
                                <div class="preview-info">
                                    <div class="preview-filename" id="tw-name"><?php echo $tw_name; ?></div>
                                    <div class="preview-size" id="tw-size">Current File</div>
                                </div>
                                <button type="button" class="remove-btn" onclick="clearUpload('twitter_file', 'tw-img', 'tw-name', 'tw-size', '<?php echo $tw_preview; ?>', '<?php echo $tw_name; ?>')">&times;</button>
                            </div>
                            <input type="file" name="twitter_file" id="twitter_file" class="input-field mt-3" style="font-size:12px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="social" class="tab-pane">
            <div class="flat-card">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="input-group">
                        <label class="input-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="<?php echo getVal($conn, 'whatsapp_number'); ?>" class="input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Telegram Link</label>
                        <input type="text" name="telegram_link" value="<?php echo getVal($conn, 'telegram_link'); ?>" class="input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Facebook Page</label>
                        <input type="text" name="facebook" value="<?php echo getVal($conn, 'facebook'); ?>" class="input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Instagram</label>
                        <input type="text" name="instagram" value="<?php echo getVal($conn, 'instagram'); ?>" class="input-field">
                    </div>
                    
                    <div class="input-group md:col-span-2">
                        <label class="input-label">Floating Button (FAB) Link</label>
                        <input type="text" name="fab_link" value="<?php echo getVal($conn, 'fab_link'); ?>" class="input-field" placeholder="https://...">
                        <p style="font-size:11px; color:#666; margin-top:5px;">Link for the floating support button on homepage.</p>
                    </div>

                    <div class="input-group md:col-span-2">
                        <label class="input-label">YouTube Channel</label>
                        <input type="text" name="youtube" value="<?php echo getVal($conn, 'youtube'); ?>" class="input-field">
                    </div>
                    <div class="input-group md:col-span-2">
                        <label class="input-label">Support Email</label>
                        <input type="text" name="contact_email" value="<?php echo getVal($conn, 'contact_email'); ?>" class="input-field">
                    </div>
                </div>
            </div>
        </div>

        <div id="notice" class="tab-pane">
            <div class="flat-card">
                <div class="input-group">
                    <label class="input-label">Home Page Notice</label>
                    <textarea name="home_notice" class="textarea-field" style="height:150px; resize:vertical;"><?php echo getVal($conn, 'home_notice'); ?></textarea>
                    <p style="font-size:11px; color:#666; margin-top:5px;">This text appears on the scrolling marquee.</p>
                </div>
            </div>
        </div>

        <div id="firebase" class="tab-pane">
            <div class="flat-card">
                <div class="grid grid-cols-1 gap-5">
                    <div class="input-group">
                        <label class="input-label">Database URL</label>
                        <input type="text" name="firebase_database_url" value="<?php echo getVal($conn, 'firebase_database_url'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">API Key</label>
                        <input type="text" name="firebase_api_key" value="<?php echo getVal($conn, 'firebase_api_key'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Auth Domain</label>
                        <input type="text" name="firebase_auth_domain" value="<?php echo getVal($conn, 'firebase_auth_domain'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Project ID</label>
                        <input type="text" name="firebase_project_id" value="<?php echo getVal($conn, 'firebase_project_id'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Storage Bucket</label>
                        <input type="text" name="firebase_storage_bucket" value="<?php echo getVal($conn, 'firebase_storage_bucket'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Sender ID</label>
                        <input type="text" name="firebase_messaging_sender_id" value="<?php echo getVal($conn, 'firebase_messaging_sender_id'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">App ID</label>
                        <input type="text" name="firebase_app_id" value="<?php echo getVal($conn, 'firebase_app_id'); ?>" class="input-field mono">
                    </div>
                </div>
            </div>
        </div>

        <div id="security" class="tab-pane">
            <div class="flat-card" style="border-color: #ef4444;">
                <div class="input-group">
                    <label class="input-label" style="color:#ef4444;">New Admin Password</label>
                    <input type="password" name="new_pass" class="input-field" style="border-color: #ef4444;" placeholder="Enter new password to update...">
                    <p style="font-size:11px; color:#666; margin-top:5px;">Leave empty to keep current password.</p>
                </div>
            </div>
        </div>

        <div class="sticky-footer">
            <div class="max-w-7xl mx-auto w-full flex justify-end">
                <button type="submit" name="update" class="btn-save w-full md:w-auto">
                    Save Configuration
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    // --- TAB LOGIC ---
    function openTab(evt, tabName) {
        evt.preventDefault(); 
        var i, tabcontent, tablinks;
        
        tabcontent = document.getElementsByClassName("tab-pane");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            tabcontent[i].classList.remove("active");
        }
        
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.className += " active";
    }

    // --- COLOR PICKER SYNC ---
    const picker = document.getElementById('pickerInput');
    const textInput = document.getElementById('hexInput');

    if(picker && textInput) {
        picker.addEventListener('input', (e) => {
            textInput.value = e.target.value.toUpperCase();
        });
        textInput.addEventListener('input', (e) => {
            let val = e.target.value;
            if(!val.startsWith('#') && val.length > 0) val = '#' + val;
            if(/^#[0-9A-F]{6}$/i.test(val)) picker.value = val;
        });
    }

    // --- FILE UPLOAD PREVIEW & HALF-CARD SUCCESS EFFECT ---
    ['app_logo', 'favicon_file', 'pwa_file', 'og_file', 'twitter_file'].forEach(id => {
        const input = document.getElementById(id);
        if(!input) return;
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const container = input.closest('.media-upload-container');
                const previewCard = container.querySelector('.small-preview-card');
                const img = previewCard.querySelector('.preview-thumb img');
                const nameEl = previewCard.querySelector('.preview-filename');
                const sizeEl = previewCard.querySelector('.preview-size');
                
                reader.onload = function(e) { img.src = e.target.result; }
                reader.readAsDataURL(file);
                
                nameEl.textContent = file.name;
                sizeEl.textContent = (file.size / 1024).toFixed(0) + ' KB (New)';
                
                // Remove existing to restart animation
                previewCard.classList.remove('staged-success');
                void previewCard.offsetWidth; // Trigger Reflow
                previewCard.classList.add('staged-success');
            }
        });
    });

    // --- CLEAR UPLOAD HELPER ---
    function clearUpload(inputId, imgId, nameId, sizeId, defaultSrc, defaultName) {
        const input = document.getElementById(inputId);
        const container = input.closest('.media-upload-container');
        const previewCard = container.querySelector('.small-preview-card');

        input.value = ''; 
        document.getElementById(imgId).src = defaultSrc;
        document.getElementById(nameId).textContent = defaultName;
        document.getElementById(sizeId).textContent = "Current File";
        
        previewCard.classList.remove('staged-success');
    }

    // --- TAG INPUT LOGIC (KEYWORDS) ---
    const tagContainer = document.getElementById('tagContainer');
    const tagInput = document.getElementById('tagInput');
    const hiddenInput = document.getElementById('hiddenKeywords');
    
    // Initial Tags
    let tags = hiddenInput.value ? hiddenInput.value.split(',').map(t => t.trim()).filter(t => t) : [];

    function renderTags() {
        // Clear all except input
        const existingTags = tagContainer.querySelectorAll('.tag-item');
        existingTags.forEach(t => t.remove());
        
        tags.forEach((tag, index) => {
            const div = document.createElement('div');
            div.className = 'tag-item';
            div.innerHTML = `${tag} <span class="tag-close" data-index="${index}">&times;</span>`;
            tagContainer.insertBefore(div, tagInput);
        });
        
        // Update hidden input
        hiddenInput.value = tags.join(', ');
        
        // Re-attach close listeners
        document.querySelectorAll('.tag-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.getAttribute('data-index');
                tags.splice(idx, 1);
                renderTags();
            });
        });
    }

    // Initialize
    renderTags();

    // 1. KEYDOWN EVENT (Enter or Comma)
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = this.value.trim().replace(/,/g, '');
            if (val && !tags.includes(val)) {
                tags.push(val);
                renderTags();
                this.value = '';
            }
        }
        // Remove last tag on backspace if input is empty
        if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
            tags.pop();
            renderTags();
        }
    });

    // 2. PASTE EVENT (Handle CSV Paste)
    tagInput.addEventListener('paste', function(e) {
        e.preventDefault();
        let paste = (e.clipboardData || window.clipboardData).getData('text');
        const items = paste.split(','); // Split by comma
        
        items.forEach(item => {
            const val = item.trim();
            if (val && !tags.includes(val)) {
                tags.push(val);
            }
        });
        
        renderTags();
        this.value = ''; // Clear input after paste
    });
</script>
