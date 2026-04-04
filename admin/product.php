<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
$conn->query("CREATE TABLE IF NOT EXISTS product_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    status ENUM('unused', 'used') DEFAULT 'unused',
    order_id INT DEFAULT 0,
    used_at TIMESTAMP NULL,
    INDEX(product_id),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure 'status' column exists in products
$checkStatus = $conn->query("SHOW COLUMNS FROM products LIKE 'status'");
if($checkStatus && $checkStatus->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
}

// ====================================================
// LOGIC
// ====================================================
$view = isset($_GET['action']) ? $_GET['action'] : 'list'; 
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Variables
$game_id = '';
$name = '';
$price = '';
$status = 'active';
$stock_count = 0;

// Edit Data Fetch
if($view == 'edit' && $editId > 0) {
    $res = $conn->query("SELECT * FROM products WHERE id=$editId");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $game_id = $row['game_id'];
        $name = $row['name'];
        $price = $row['price'];
        $status = $row['status'] ?? 'active';
        
        // Get Stock Count
        $stk = $conn->query("SELECT COUNT(*) as cnt FROM product_codes WHERE product_id=$editId AND status='unused'");
        $stock_count = $stk->fetch_assoc()['cnt'];
    }
}

// TOGGLE STATUS
if(isset($_GET['toggle_status'])) {
    $pid = (int)$_GET['toggle_status'];
    $currentStatus = $_GET['current'];
    $newStatus = ($currentStatus == 'active') ? 'inactive' : 'active';
    $conn->query("UPDATE products SET status='$newStatus' WHERE id=$pid");
    echo "<script>window.location='product.php';</script>";
}

// Save Handle
if(isset($_POST['save_product'])) {
    $gid = (int)$_POST['game_id'];
    $n = $conn->real_escape_string($_POST['name']);
    $p = (float)$_POST['price'];
    
    $target_pid = 0;
    
    if($view == 'edit') {
        $target_pid = (int)$_POST['update_id'];
        $conn->query("UPDATE products SET game_id=$gid, name='$n', price='$p' WHERE id=$target_pid");
        $msg = "Product updated successfully.";
    } else {
        $conn->query("INSERT INTO products (game_id, name, price, status) VALUES ($gid, '$n', '$p', 'active')");
        $target_pid = $conn->insert_id;
        $msg = "Product added successfully.";
        $view = 'list';
    }

    // --- SAVE STOCK CODES ---
    if(!empty($_POST['stock_codes']) && $target_pid > 0) {
        $codes = preg_split("/\r\n|\n|\r/", $_POST['stock_codes']);
        $added = 0;
        $stmt = $conn->prepare("INSERT INTO product_codes (product_id, code, status) VALUES (?, ?, 'unused')");
        foreach($codes as $code) {
            $code = trim($code);
            if(!empty($code)) {
                $stmt->bind_param("is", $target_pid, $code);
                if($stmt->execute()) $added++;
            }
        }
        if($added > 0) $msg .= " Added $added new codes.";
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success', title: 'Success', text: '$msg', 
                background: '#1a1a1a', color: '#fff', confirmButtonColor: '#eab308',
                timer: 1500, showConfirmButton: false
            }).then(() => { window.location='product.php?action=$view&id=$editId'; });
        });
    </script>";
}

// Delete Handle
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    $conn->query("DELETE FROM product_codes WHERE product_id=$id"); // Clean up codes
    echo "<script>window.location='product.php';</script>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* THEME VARIABLES */
    :root {
        --bg-body: #050505; --bg-card: #111111; --bg-input: #1a1a1a;
        --border: #222222; --text-pri: #ffffff; --text-sec: #9ca3af;
        --accent: #eab308; --red: #ef4444; --green: #22c55e;
    }
    body { background-color: var(--bg-body); color: var(--text-pri); font-family: 'Inter', sans-serif; margin: 0; }
    .container-fluid { padding: 20px; max-width: 100%; }

    /* HEADER */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-title { font-size: 24px; font-weight: 700; margin: 0; }
    .btn-new { background: var(--accent); color: #000; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; text-decoration: none; }
    .btn-delete-page { background: var(--red); color: #fff; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; border: none; cursor: pointer; text-decoration: none; }

    /* CARD */
    .table-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 30px; }
    .filter-bar { padding: 15px; border-bottom: 1px solid var(--border); display: flex; gap: 10px; align-items: center; }
    .search-box { flex: 1; background: var(--bg-input); border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; padding: 10px 12px; }
    .search-box input { background: transparent; border: none; outline: none; color: #fff; width: 100%; font-size: 14px; margin-left: 10px; }

    /* TABLE */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    th { text-align: left; padding: 15px 20px; color: var(--text-sec); font-size: 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border); background: #161616; white-space: nowrap; }
    td { padding: 15px 20px; border-bottom: 1px solid var(--border); font-size: 14px; color: #fff; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* FORM */
    .form-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 20px; max-width: 600px; margin: 0 auto; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 12px; color: #fff; margin-bottom: 8px; font-weight: 600; }
    .form-input, .form-select, .form-textarea { width: 100%; background: var(--bg-input); border: 1px solid var(--border); padding: 12px; border-radius: 8px; color: #fff; outline: none; font-size: 14px; font-family: 'Inter', sans-serif; box-sizing: border-box; }
    .form-textarea { min-height: 120px; resize: vertical; font-family: monospace; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #444; }
    
    .btn-save { background: var(--accent); color: #000; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; }
    .btn-cancel { background: transparent; color: #fff; padding: 12px 24px; margin-top: 10px; border: 1px solid var(--border); border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: block; text-align: center; }

    /* BADGES */
    .stock-badge { background: #1f2937; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; color: #9ca3af; border: 1px solid #374151; }
    .stock-green { color: #22c55e; border-color: #064e3b; background: #064e3b20; }
    .stock-red { color: #ef4444; border-color: #7f1d1d; background: #7f1d1d20; }

    /* SWITCH */
    .toggle-switch { position: relative; display: inline-block; width: 36px; height: 20px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #4b5563; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--accent); }
    input:checked + .slider:before { transform: translateX(16px); }

    .action-btn { color: var(--accent); font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-right: 15px; }
    .delete-btn { color: var(--red); }
</style>

<div class="container-fluid">

    <?php if($view == 'list'): ?>
    <div class="page-header">
        <h1 class="page-title">Products / Variations</h1>
        <a href="?action=add" class="btn-new">New Product</a>
    </div>

    <div class="table-card">
        <div class="filter-bar">
            <div class="search-box">
                <i data-feather="search" style="color: #666; width: 18px;"></i>
                <input type="text" placeholder="Search products...">
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" style="accent-color:#f59e0b;"></th>
                        <th>Game</th>
                        <th>Variation Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Fetch products with Stock Count
                    $sql = "SELECT p.*, g.name as gname, 
                            (SELECT COUNT(*) FROM product_codes WHERE product_id = p.id AND status = 'unused') as stock
                            FROM products p 
                            LEFT JOIN games g ON p.game_id = g.id 
                            ORDER BY p.id DESC";
                    $result = $conn->query($sql);
                    
                    if($result && $result->num_rows > 0):
                        while($row = $result->fetch_assoc()): 
                            $status = isset($row['status']) ? $row['status'] : 'active';
                            $isActive = ($status == 'active');
                            $stock = (int)$row['stock'];
                            $stockClass = ($stock > 0) ? 'stock-green' : 'stock-red';
                    ?>
                    <tr>
                        <td><input type="checkbox" style="accent-color:#f59e0b;"></td>
                        <td style="color:#fff; font-weight:600;"><?php echo htmlspecialchars($row['gname']); ?></td>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td style="font-weight:700; white-space:nowrap;">৳ <?php echo number_format($row['price'], 2); ?></td>
                        
                        <td>
                            <span class="stock-badge <?php echo $stockClass; ?>">
                                <?php echo $stock; ?> Codes
                            </span>
                        </td>
                        
                        <td>
                            <a href="?toggle_status=<?php echo $row['id']; ?>&current=<?php echo $status; ?>" class="toggle-switch">
                                <input type="checkbox" <?php echo $isActive ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </a>
                        </td>
                        
                        <td class="text-right nowrap">
                            <a href="?action=edit&id=<?php echo $row['id']; ?>" class="action-btn">
                                <i data-feather="edit-2" style="width:14px;"></i> Edit
                            </a>
                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="action-btn delete-btn">
                                <i data-feather="trash" style="width:14px;"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:#666;">No products found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: 
        // ================= VIEW: ADD / EDIT =================
        $isEdit = ($view == 'edit');
    ?>
    
    <div class="page-header" style="max-width: 600px; margin: 0 auto 20px auto;">
        <h1 class="page-title"><?php echo $isEdit ? 'Edit Product' : 'New Product'; ?></h1>
        <?php if($isEdit): ?>
            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $editId; ?>)" class="btn-delete-page">Delete</a>
        <?php endif; ?>
    </div>

    <form method="POST" class="form-card">
        <input type="hidden" name="save_product" value="1">
        <?php if($isEdit): ?>
            <input type="hidden" name="update_id" value="<?php echo $editId; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Select Game</label>
            <select name="game_id" class="form-select" required>
                <option value="" disabled <?php echo !$isEdit?'selected':''; ?>>-- Choose Game --</option>
                <?php 
                $games = $conn->query("SELECT id, name FROM games ORDER BY name ASC");
                while($g = $games->fetch_assoc()): 
                    $selected = ($isEdit && $game_id == $g['id']) ? 'selected' : '';
                    echo "<option value='{$g['id']}' $selected>{$g['name']}</option>";
                endwhile;
                ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Package Name</label>
            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. 25 Diamonds" required>
        </div>

        <div class="form-group">
            <label class="form-label">Price (BDT)</label>
            <input type="number" step="0.01" name="price" class="form-input" value="<?php echo htmlspecialchars($price); ?>" placeholder="0.00" required>
        </div>

        <div class="form-group" style="background:#222; padding:15px; border-radius:8px; border:1px solid #333;">
            <label class="form-label" style="color:#eab308; display:flex; justify-content:space-between;">
                <span>Add UniPin Stock (Optional)</span>
                <?php if($isEdit): ?>
                    <span style="font-size:11px; color:#fff;">Current Unused Stock: <?php echo $stock_count; ?></span>
                <?php endif; ?>
            </label>
            <textarea name="stock_codes" class="form-textarea" placeholder="Paste codes here (one per line)...&#10;UP-1234-5678&#10;UP-8765-4321"></textarea>
            <p style="font-size:11px; color:#666; margin-top:5px;">Codes entered here will be added to the stock for this specific package.</p>
        </div>

        <div style="margin-top:30px;">
            <button type="submit" class="btn-save">
                <?php echo $isEdit ? 'Save Changes' : 'Add Product'; ?>
            </button>
            <a href="product.php" class="btn-cancel">Cancel</a>
        </div>
    </form>

    <?php endif; ?>

</div>

<script>
    feather.replace();
    
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Product?',
            text: "This will remove the product and its stock.",
            icon: 'warning',
            background: '#1a1a1a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#333',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?delete=' + id;
            }
        });
    }
</script>
