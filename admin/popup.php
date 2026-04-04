<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
// Ensure settings table exists and keys present
$defaults = [
    'popup_image' => '',
    'popup_link' => '',
    'popup_btn_text' => '',
    'popup_text' => ''
];

foreach($defaults as $key => $val) {
    $chk = $conn->query("SELECT id FROM settings WHERE name='$key'");
    if($chk && $chk->num_rows == 0) {
        $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
    }
}

// HELPER
function getAdminSetting($conn, $key) {
    $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
    return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
}

// HANDLE SAVE
if(isset($_POST['save_popup'])) {
    $link = $conn->real_escape_string($_POST['popup_link']);
    $btn = $conn->real_escape_string($_POST['popup_btn_text']);
    $text = $conn->real_escape_string($_POST['popup_text']); 
    
    $conn->query("UPDATE settings SET value='$link' WHERE name='popup_link'");
    $conn->query("UPDATE settings SET value='$btn' WHERE name='popup_btn_text'");
    $conn->query("UPDATE settings SET value='$text' WHERE name='popup_text'");

    if(isset($_FILES['popup_image']) && $_FILES['popup_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['popup_image']['name'], PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newFilename = "popup_" . time() . "." . $ext;
            if(move_uploaded_file($_FILES['popup_image']['tmp_name'], "../uploads/" . $newFilename)) {
                $dbPath = "uploads/" . $newFilename;
                $conn->query("UPDATE settings SET value='$dbPath' WHERE name='popup_image'");
            }
        }
    }

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ 
                icon: 'success', title: 'Saved', text: 'Popup settings updated.',
                background: '#1a1a1a', color: '#fff', confirmButtonColor: '#eab308', 
                timer: 1500, showConfirmButton: false 
            });
        });
    </script>";
}

// HANDLE REMOVE
if(isset($_POST['remove_popup'])) {
    $oldImg = getAdminSetting($conn, 'popup_image');
    if(!empty($oldImg) && file_exists("../" . $oldImg)) {
        unlink("../" . $oldImg);
    }
    // Clear values
    $conn->query("UPDATE settings SET value='' WHERE name='popup_image'");
    $conn->query("UPDATE settings SET value='' WHERE name='popup_text'");
    $conn->query("UPDATE settings SET value='' WHERE name='popup_link'");
    $conn->query("UPDATE settings SET value='' WHERE name='popup_btn_text'");
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ 
                icon: 'success', title: 'Removed', text: 'Popup disabled.',
                background: '#1a1a1a', color: '#fff', confirmButtonColor: '#eab308',
                timer: 1500, showConfirmButton: false 
            });
        });
    </script>";
}

// FETCH VALUES
$currImg = getAdminSetting($conn, 'popup_image');
$currLink = getAdminSetting($conn, 'popup_link');
$currBtn = getAdminSetting($conn, 'popup_btn_text');
$currText = getAdminSetting($conn, 'popup_text');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* THEME VARIABLES */
    :root {
        --bg-body: #050505;
        --bg-card: #111111;
        --bg-input: #1a1a1a;
        --border: #222222;
        --text-primary: #ffffff;
        --text-secondary: #9ca3af;
        --accent: #eab308;
        --red: #ef4444;
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    .container-fluid { padding: 20px; max-width: 700px; margin: 0 auto; }

    /* HEADER */
    .page-header { margin-bottom: 25px; }
    .page-title { font-size: 24px; font-weight: 700; margin: 0; }
    .page-desc { font-size: 13px; color: var(--text-secondary); margin-top: 5px; }

    /* CARD */
    .flat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .flat-header {
        background: #161616;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        font-size: 14px;
        color: #fff;
        display: flex; align-items: center; gap: 10px;
    }

    .flat-body { padding: 25px; }

    /* FORM ELEMENTS */
    .input-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; }
    
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
    }
    .input-field:focus, .textarea-field:focus { border-color: #444; }

    /* PREVIEW */
    .preview-box {
        border: 1px dashed #333; border-radius: 8px; padding: 20px;
        text-align: center; background: #1a1a1a; min-height: 180px;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        margin-bottom: 15px;
    }
    .preview-img {
        max-width: 100%; max-height: 250px; border-radius: 6px; 
        border: 1px solid #333; display: block;
    }

    /* BUTTONS */
    .btn-save {
        background: var(--accent); color: #000; padding: 12px; border-radius: 8px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px; margin-top: 10px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    
    .btn-remove {
        background: rgba(239, 68, 68, 0.1); color: var(--red); 
        padding: 12px; border-radius: 8px; font-weight: 700;
        border: 1px solid rgba(239, 68, 68, 0.2); 
        cursor: pointer; width: 100%; font-size: 14px; margin-top: 15px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-remove:hover { background: var(--red); color: #fff; }

    /* FILE INPUT STYLING */
    input[type=file]::file-selector-button {
        background: #333; color: #fff; border: none; padding: 8px 12px;
        border-radius: 6px; margin-right: 10px; cursor: pointer; font-size: 12px;
    }
    input[type=file]::file-selector-button:hover { background: #444; }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Popup Management</h1>
        <p class="page-desc">Configure the promotional modal shown on app open.</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="flat-card border-t-4 border-t-yellow-500">
            <div class="flat-header">
                <i data-feather="layout" style="width:16px;"></i> Popup Configuration
            </div>
            
            <div class="flat-body">
                
                <div style="margin-bottom: 20px;">
                    <label class="input-label">Current Image</label>
                    <div class="preview-box">
                        <?php if(!empty($currImg)): ?>
                            <img src="../<?php echo $currImg; ?>" class="preview-img">
                            <span style="font-size:11px; background:#22c55e; color:#000; padding:2px 8px; border-radius:4px; margin-top:10px; font-weight:700;">ACTIVE</span>
                        <?php else: ?>
                            <i data-feather="image" style="width:40px; height:40px; color:#333; margin-bottom:10px;"></i>
                            <p style="font-size:12px; color:#666;">No popup active.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="input-label">Upload Image</label>
                    <input type="file" name="popup_image" accept="image/*" class="input-field" style="padding:8px;">
                    <p style="font-size:11px; color:#666; margin-top:5px;">Supported formats: JPG, PNG, WEBP.</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="input-label">Message Body</label>
                    <textarea name="popup_text" rows="3" class="textarea-field" placeholder="Enter optional text..."><?php echo htmlspecialchars($currText); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label class="input-label">Button Link</label>
                        <input type="text" name="popup_link" value="<?php echo htmlspecialchars($currLink); ?>" class="input-field" placeholder="https://...">
                    </div>
                    <div>
                        <label class="input-label">Button Text</label>
                        <input type="text" name="popup_btn_text" value="<?php echo htmlspecialchars($currBtn); ?>" class="input-field" placeholder="e.g. Join Now">
                    </div>
                </div>

                <button type="submit" name="save_popup" class="btn-save">
                    <i data-feather="save" style="width:16px;"></i> Save Changes
                </button>

                <?php if(!empty($currImg)): ?>
                    <button type="submit" name="remove_popup" class="btn-remove" onclick="return confirm('Disable popup?');">
                        <i data-feather="trash-2" style="width:16px;"></i> Disable Popup
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </form>

</div>

<script>
    feather.replace();
</script>
