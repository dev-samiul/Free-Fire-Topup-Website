<?php 
include 'common/header.php'; 

// ====================================================
// HANDLE SETTINGS UPDATE
// ====================================================
if(isset($_POST['update_payment'])) {
    foreach($_POST as $key => $val) {
        if($key == 'update_payment') continue;
        
        $val = $conn->real_escape_string($val);
        
        // Update or Insert into 'settings' table
        $check = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($check->num_rows > 0) {
            $conn->query("UPDATE settings SET value='$val' WHERE name='$key'");
        } else {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
        }
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Payment details updated successfully.',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#eab308',
                timer: 1500,
                showConfirmButton: false
            });
        });
    </script>";
}

// Helper Function
function getVal($conn, $key) {
    $q = $conn->query("SELECT value FROM settings WHERE name='$key'");
    return ($q && $q->num_rows > 0) ? htmlspecialchars($q->fetch_assoc()['value']) : '';
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    .container-fluid { padding: 20px; max-width: 600px; margin: 0 auto; }

    /* HEADER */
    .page-header { margin-bottom: 25px; text-align: center; }
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

    /* FORMS */
    .input-group { margin-bottom: 20px; }
    .input-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; }
    
    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 12px;
        font-size: 16px;
        z-index: 1;
    }

    .input-field {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px 12px 12px 40px; /* Space for icon */
        font-size: 14px;
        color: #fff;
        outline: none;
        transition: border-color 0.2s;
        font-family: monospace;
    }
    .input-field:focus { border-color: #444; }

    /* BRAND COLORS */
    .bkash-color { color: #e2136e; }
    .nagad-color { color: #f7931a; }
    .rocket-color { color: #8c3494; }
    .video-color { color: #ef4444; }

    /* BUTTONS */
    .btn-save {
        background: var(--accent); color: #000; padding: 12px; border-radius: 8px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px; margin-top: 10px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-save:hover { filter: brightness(1.1); }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Payment Methods</h1>
        <p class="page-desc">Configure wallet numbers for deposits.</p>
    </div>

    <form method="POST">
        <div class="flat-card border-t-4 border-t-yellow-500">
            <div class="flat-header">
                <i data-feather="credit-card" style="width:16px;"></i> Wallet Configuration
            </div>
            
            <div class="flat-body">
                
                <div class="input-group">
                    <label class="input-label">bKash Number</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-b input-icon bkash-color"></i>
                        <input type="text" name="admin_bkash_number" value="<?php echo getVal($conn, 'admin_bkash_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Nagad Number</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-n input-icon nagad-color"></i>
                        <input type="text" name="admin_nagad_number" value="<?php echo getVal($conn, 'admin_nagad_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Rocket Number</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-r input-icon rocket-color"></i>
                        <input type="text" name="admin_rocket_number" value="<?php echo getVal($conn, 'admin_rocket_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                    </div>
                </div>

                <div style="height: 1px; background: #222; margin: 25px 0;"></div>

                <div class="input-group">
                    <label class="input-label">"How to Add Money" Video URL</label>
                    <div class="input-wrapper">
                        <i class="fa-brands fa-youtube input-icon video-color"></i>
                        <input type="text" name="add_money_video" value="<?php echo getVal($conn, 'add_money_video'); ?>" class="input-field" placeholder="https://youtube.com/watch?v=..." style="font-family: 'Inter', sans-serif;">
                    </div>
                    <p style="font-size:11px; color:#666; margin-top:6px;">This video link appears in the user wallet section.</p>
                </div>

                <button type="submit" name="update_payment" class="btn-save">
                    <i data-feather="save" style="width:16px;"></i> Save Changes
                </button>

            </div>
        </div>
    </form>

</div>

<script>
    feather.replace();
</script>
