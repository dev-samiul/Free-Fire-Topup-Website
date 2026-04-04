<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
try {
    $conn->query("CREATE TABLE IF NOT EXISTS games (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), type VARCHAR(50), category_id INT, description TEXT, image VARCHAR(255), status ENUM('active', 'inactive') DEFAULT 'active')");
    
    // Check Columns
    $checkStatus = $conn->query("SHOW COLUMNS FROM games LIKE 'status'");
    if($checkStatus && $checkStatus->num_rows == 0) $conn->query("ALTER TABLE games ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");

    $checkCat = $conn->query("SHOW COLUMNS FROM games LIKE 'category_id'");
    if($checkCat && $checkCat->num_rows == 0) $conn->query("ALTER TABLE games ADD COLUMN category_id INT DEFAULT 0");

    $checkHint = $conn->query("SHOW COLUMNS FROM games LIKE 'hint_text'");
    if($checkHint && $checkHint->num_rows == 0) $conn->query("ALTER TABLE games ADD COLUMN hint_text VARCHAR(255) DEFAULT NULL");

    $checkUidCheck = $conn->query("SHOW COLUMNS FROM games LIKE 'check_uid'");
    if($checkUidCheck && $checkUidCheck->num_rows == 0) $conn->query("ALTER TABLE games ADD COLUMN check_uid TINYINT DEFAULT 1");

} catch(Exception $e) {}

// ====================================================
// INITIALIZE VARIABLES
// ====================================================
$editMode = false;
$editId = 0;
$name = '';
$type = 'uid';
$desc = '';
$currImg = '';
$catId = 0;
$hintText = '';
$checkUid = 1; 
$modalOpen = false; 

// EDIT MODE
if(isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM games WHERE id=$editId");
    if($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $editMode = true;
        $modalOpen = true; 
        $name = $row['name'];
        $type = $row['type'];
        $desc = $row['description'];
        $currImg = $row['image'];
        $catId = $row['category_id'] ?? 0;
        $hintText = $row['hint_text'] ?? '';
        $checkUid = $row['check_uid'] ?? 1;
    }
}

// TOGGLE STATUS
if(isset($_GET['toggle_status'])) {
    $gid = (int)$_GET['toggle_status'];
    $currentStatus = $_GET['status'];
    $newStatus = ($currentStatus == 'active') ? 'inactive' : 'active';
    
    $conn->query("UPDATE games SET status='$newStatus' WHERE id=$gid");
    echo "<script>window.location='game.php';</script>";
}

// SAVE ACTIONS
if(isset($_POST['save_game'])) {
    // FIX: DO NOT use real_escape_string here because we use prepare() later.
    // Using both causes "\r\n" to appear as text.
    $n = $_POST['name'];
    $t = $_POST['type'];
    $c = (int)$_POST['category_id'];
    $d = $_POST['description']; // Raw input (Prepared statement handles security)
    $h = $_POST['hint_text'];
    $cu = isset($_POST['check_uid']) ? 1 : 0;
    
    $finalImage = $editMode ? $currImg : ''; 
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $newFilename = "game_" . time() . "." . $ext;
            if(move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $newFilename)) {
                $finalImage = "uploads/" . $newFilename;
            }
        }
    }

    if(empty($finalImage)) {
        echo "<script>alert('Image is required!'); window.history.back();</script>";
    } else {
        if($editMode) {
            $uid = (int)$_POST['update_id'];
            $stmt = $conn->prepare("UPDATE games SET name=?, type=?, category_id=?, description=?, image=?, hint_text=?, check_uid=? WHERE id=?");
            $stmt->bind_param("ssisssii", $n, $t, $c, $d, $finalImage, $h, $cu, $uid);
        } else {
            $stmt = $conn->prepare("INSERT INTO games (name, type, category_id, description, image, hint_text, check_uid, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("ssisssi", $n, $t, $c, $d, $finalImage, $h, $cu);
        }
        
        if($stmt->execute()) {
            echo "<script>window.location='game.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

// DELETE ACTION
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM games WHERE id=$id");
    echo "<script>window.location='game.php';</script>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
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

    .container-fluid { padding: 20px; max-width: 100%; }

    /* HEADER */
    .page-header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .page-title { font-size: 24px; font-weight: 700; margin: 0; }
    .btn-new {
        background: var(--accent); color: #000; padding: 10px 20px;
        border-radius: 8px; font-weight: 700; font-size: 13px; border: none; cursor: pointer;
    }

    /* CARD & TABLE */
    .table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .filter-bar {
        padding: 15px; border-bottom: 1px solid var(--border);
        display: flex; gap: 10px; align-items: center;
    }
    
    .search-box {
        flex: 1; background: var(--bg-input); border: 1px solid var(--border);
        border-radius: 8px; display: flex; align-items: center; padding: 10px 12px;
    }
    .search-box input {
        background: transparent; border: none; outline: none;
        color: #fff; width: 100%; font-size: 14px; margin-left: 10px;
    }

    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    
    th {
        text-align: left; padding: 15px 20px;
        color: var(--text-secondary); font-size: 13px; font-weight: 600;
        border-bottom: 1px solid var(--border); background: #161616;
    }
    
    td {
        padding: 15px 20px; border-bottom: 1px solid var(--border);
        font-size: 14px; color: #fff; vertical-align: middle;
    }
    
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* MODAL STYLES */
    .modal {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.8); z-index: 100;
        align-items: center; justify-content: center;
    }
    
    <?php if($modalOpen): ?>
    .modal { display: flex !important; }
    <?php endif; ?>

    .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border);
        width: 90%; max-width: 600px;
        border-radius: 12px; padding: 25px;
        position: relative;
        max-height: 90vh; overflow-y: auto;
    }
    .modal-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }

    /* FORM */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; font-weight: 600; }
    .form-input, .form-select, .form-textarea { 
        width: 100%; background: var(--bg-input); border: 1px solid var(--border);
        padding: 12px; border-radius: 8px; color: #fff; outline: none; font-size: 14px;
        font-family: 'Inter', sans-serif;
    }
    .form-input:focus { border-color: #444; }
    .form-textarea { min-height: 100px; resize: vertical; }

    .btn-save {
        background: var(--accent); color: #000; padding: 12px 24px; width: 100%;
        border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px;
    }
    .btn-cancel-modal {
        position: absolute; top: 20px; right: 20px; background: transparent; border: none; color: #666; font-size: 20px; cursor: pointer;
    }
    .btn-cancel-modal:hover { color: #fff; }

    /* BADGES & SWITCHES */
    .type-badge {
        font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 4px; 
        text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid;
    }
    .badge-uid { color: #eab308; border-color: rgba(234, 179, 8, 0.2); background: rgba(234, 179, 8, 0.05); }
    .badge-voucher { color: #a855f7; border-color: rgba(168, 85, 247, 0.2); background: rgba(168, 85, 247, 0.05); }
    .badge-subscription { color: #22c55e; border-color: rgba(34, 197, 94, 0.2); background: rgba(34, 197, 94, 0.05); }

    .toggle-switch {
        position: relative; display: inline-block; width: 36px; height: 20px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #4b5563; transition: .4s; border-radius: 34px;
    }
    .slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px;
        background-color: white; transition: .4s; border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--accent); }
    input:checked + .slider:before { transform: translateX(16px); }

    /* SMALL TOGGLE FOR MODAL */
    .small-toggle { width: 40px; height: 22px; }
    .small-toggle .slider:before { height: 18px; width: 18px; bottom: 2px; left: 2px; }
    .small-toggle input:checked + .slider:before { transform: translateX(18px); }

    .btn-edit-text {
        color: var(--accent); font-weight: 600; font-size: 13px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-right: 15px;
    }
    .btn-delete-text {
        color: var(--red); font-weight: 600; font-size: 13px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;
    }

    /* CONDITIONAL SECTIONS */
    .conditional-field { display: none; margin-top: 10px; }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Products</h1>
        <button onclick="openModal()" class="btn-new">New product</button>
    </div>

    <div class="table-card">
        
        <div class="filter-bar">
            <div class="search-box">
                <i data-feather="search" style="color: #666; width: 18px;"></i>
                <input type="text" placeholder="Search products...">
            </div>
            <i data-feather="filter" style="color: #666; width: 20px; margin-left: 10px;"></i>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" style="accent-color:#eab308;"></th>
                        <th>Product Title <i data-feather="chevron-down" style="width:14px; vertical-align:middle;"></i></th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT g.*, c.name as cat_name FROM games g LEFT JOIN categories c ON g.category_id = c.id ORDER BY g.id DESC";
                    $games = $conn->query($sql);
                    
                    if($games && $games->num_rows > 0): 
                        while($row = $games->fetch_assoc()): 
                            $badge = 'badge-uid';
                            if($row['type'] == 'voucher') $badge = 'badge-voucher';
                            if($row['type'] == 'subscription') $badge = 'badge-subscription';
                            
                            $status = isset($row['status']) ? $row['status'] : 'active';
                            $isActive = ($status == 'active');
                    ?>
                    <tr>
                        <td><input type="checkbox" style="accent-color:#eab308;"></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="../<?php echo $row['image']; ?>" style="width:40px; height:40px; border-radius:6px; object-fit:cover; background:#222;">
                                <span style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($row['name']); ?></span>
                            </div>
                        </td>
                        <td style="color:#aaa;"><?php echo htmlspecialchars($row['cat_name'] ?? 'Uncategorized'); ?></td>
                        <td>
                            <span class="type-badge <?php echo $badge; ?>"><?php echo strtoupper($row['type']); ?></span>
                        </td>
                        <td>
                            <a href="?toggle_status=<?php echo $row['id']; ?>&status=<?php echo $status; ?>" class="toggle-switch">
                                <input type="checkbox" <?php echo $isActive ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </a>
                        </td>
                        <td>
                            <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit-text">
                                <i data-feather="edit-2" style="width:14px;"></i> Edit
                            </a>
                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-delete-text">
                                <i data-feather="trash" style="width:14px;"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px; color:#666;">No products found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div id="gameModal" class="modal">
    <div class="modal-content">
        <button type="button" onclick="closeModal()" class="btn-cancel-modal">&times;</button>
        <h3 class="modal-title">
            <?php echo $editMode ? 'Edit Product' : 'Add New Product'; ?>
        </h3>
        
        <form method="POST" enctype="multipart/form-data">
            <?php if($editMode): ?>
                <input type="hidden" name="update_id" value="<?php echo $editId; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="0">Select Category</option>
                        <?php 
                        $cats = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                        while($c = $cats->fetch_assoc()): 
                            $sel = ($c['id'] == $catId) ? 'selected' : '';
                            echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
                        endwhile;
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" id="typeSelect" class="form-select" onchange="toggleTypeFields()">
                        <option value="uid" <?php echo $type=='uid'?'selected':''; ?>>UID Topup</option>
                        <option value="subscription" <?php echo $type=='subscription'?'selected':''; ?>>Subscription</option>
                        <option value="voucher" <?php echo $type=='voucher'?'selected':''; ?>>Voucher / In-Game</option>
                    </select>
                </div>
            </div>

            <div id="hintField" class="form-group conditional-field">
                <label class="form-label">User Hint Text</label>
                <input type="text" name="hint_text" class="form-input" value="<?php echo htmlspecialchars($hintText); ?>" placeholder="e.g. Enter Email & Password, or Account Info">
                <p style="font-size:11px; color:#666; margin-top:5px;">This text will be shown above the input box for the user.</p>
            </div>

            <div id="uidCheckField" class="form-group conditional-field" style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-input); padding:10px; border-radius:8px; border:1px solid var(--border);">
                <div>
                    <label class="form-label" style="margin-bottom:0;">UID Name Check</label>
                    <p style="font-size:11px; color:#666;">Validate player ID using API?</p>
                </div>
                <label class="toggle-switch small-toggle">
                    <input type="checkbox" name="check_uid" <?php echo ($checkUid==1) ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($desc); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Thumbnail Image</label>
                <input type="file" name="image" class="form-input" accept="image/*" <?php echo $editMode?'':'required'; ?>>
                <?php if($editMode && !empty($currImg)): ?>
                    <img src="../<?php echo $currImg; ?>" style="width:60px; height:60px; border-radius:6px; margin-top:10px; border:1px solid #333;">
                <?php endif; ?>
            </div>

            <button type="submit" name="save_game" class="btn-save">
                <?php echo $editMode ? 'Save Changes' : 'Add Product'; ?>
            </button>
        </form>
    </div>
</div>

<script>
    feather.replace();

    function openModal() {
        document.getElementById('gameModal').style.display = 'flex';
        toggleTypeFields(); // Init State
    }

    function closeModal() {
        <?php if($editMode): ?>
        window.location.href = 'game.php';
        <?php else: ?>
        document.getElementById('gameModal').style.display = 'none';
        <?php endif; ?>
    }

    // TOGGLE LOGIC
    function toggleTypeFields() {
        const type = document.getElementById('typeSelect').value;
        const hintDiv = document.getElementById('hintField');
        const uidDiv = document.getElementById('uidCheckField');

        // Reset
        hintDiv.style.display = 'none';
        uidDiv.style.display = 'none';

        if(type === 'subscription') {
            hintDiv.style.display = 'block';
        } else if(type === 'uid') {
            uidDiv.style.display = 'flex';
        }
    }

    // Run on load if in edit mode
    <?php if($editMode): ?>
    document.addEventListener('DOMContentLoaded', toggleTypeFields);
    <?php endif; ?>

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Product?',
            text: "This will remove the product and related data.",
            icon: 'warning',
            background: '#1a1a1a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#333',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?del=" + id;
            }
        });
    }
</script>
