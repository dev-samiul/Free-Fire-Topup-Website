<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
$conn->query("CREATE TABLE IF NOT EXISTS redeem_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    status ENUM('active','used') DEFAULT 'active',
    order_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// ====================================================
// ACTIONS
// ====================================================

// 1. ADD CODE
if(isset($_POST['add_code'])) {
    $pid = (int)$_POST['product_id'];
    $raw_codes = $_POST['code'];
    $codes = explode("\n", $raw_codes);
    $count = 0;
    
    foreach($codes as $c) {
        $c = trim($c);
        if(!empty($c)) {
            $c_esc = $conn->real_escape_string($c);
            $conn->query("INSERT INTO redeem_codes (product_id, code) VALUES ($pid, '$c_esc')");
            $count++;
        }
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success', title: 'Success',
                text: '$count codes added successfully!',
                background: '#1a1a1a', color: '#fff', confirmButtonColor: '#eab308',
                timer: 1500, showConfirmButton: false
            });
        });
    </script>";
}

// 2. ASSIGN MANUAL
if(isset($_POST['assign_order'])) {
    $cid = (int)$_POST['code_id'];
    $oid = (int)$_POST['order_id'];
    
    // Check Order
    $chk = $conn->query("SELECT id FROM orders WHERE id=$oid");
    if($chk && $chk->num_rows > 0) {
        $conn->query("UPDATE redeem_codes SET order_id=$oid, status='used' WHERE id=$cid");
        $conn->query("UPDATE orders SET status='completed' WHERE id=$oid");
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success', title: 'Assigned', text: 'Order #$oid completed.',
                    background: '#1a1a1a', color: '#fff', confirmButtonColor: '#eab308'
                });
            });
        </script>";
    } else {
        echo "<script>alert('Order ID not found');</script>";
    }
}

// 3. DELETE
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM redeem_codes WHERE id=$id");
    echo "<script>window.location='redeemcode.php';</script>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* THEME VARIABLES (Consistent with other pages) */
    :root {
        --bg-body: #050505;
        --bg-card: #111111;
        --bg-input: #1a1a1a;
        --border: #222222;
        --text-primary: #ffffff;
        --text-secondary: #9ca3af;
        --accent: #eab308;
        --red: #ef4444;
        --green: #22c55e;
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
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-title { font-size: 24px; font-weight: 700; margin: 0; }

    /* LAYOUT GRID */
    .grid-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 25px;
    }
    /* Mobile Fix */
    @media (max-width: 991px) {
        .grid-layout { grid-template-columns: 1fr; }
    }

    /* CARDS */
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
        display: flex; justify-content: space-between; align-items: center;
    }

    .flat-body { padding: 20px; }

    /* FORMS */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; }
    
    .form-input, .form-select, .form-textarea { 
        width: 100%; background: var(--bg-input); border: 1px solid var(--border);
        padding: 12px; border-radius: 8px; color: #fff; outline: none; font-size: 14px;
        font-family: 'Inter', sans-serif; box-sizing: border-box;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #444; }
    
    /* Scrollbar for textarea */
    .form-textarea::-webkit-scrollbar { width: 6px; }
    .form-textarea::-webkit-scrollbar-thumb { background: #333; border-radius: 3px; }

    /* BUTTONS */
    .btn-save {
        background: var(--accent); color: #000; padding: 12px; border-radius: 8px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px;
    }
    .btn-dark {
        background: #222; color: #fff; border: 1px solid var(--border); padding: 12px;
        border-radius: 8px; font-weight: 700; width: 100%; cursor: pointer;
    }
    .btn-dark:hover { background: #333; }

    /* TABLE */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    
    th {
        text-align: left; padding: 12px 20px;
        background: #161616; color: var(--text-secondary);
        font-size: 12px; font-weight: 600; text-transform: uppercase;
        border-bottom: 1px solid var(--border);
    }
    
    td {
        padding: 14px 20px; border-bottom: 1px solid var(--border);
        font-size: 13px; color: #fff; vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* UTILS */
    .code-box {
        font-family: monospace; background: rgba(234, 179, 8, 0.1); 
        padding: 4px 8px; border-radius: 4px; border: 1px dashed rgba(234, 179, 8, 0.3); 
        color: var(--accent); font-weight: 600; display: inline-flex; gap: 8px; align-items: center;
    }
    .copy-icon { cursor: pointer; opacity: 0.7; }
    .copy-icon:hover { opacity: 1; }
    
    .badge-count { background: #222; padding: 2px 8px; border-radius: 4px; font-size: 11px; color: #fff; }
    .del-btn { color: var(--red); cursor: pointer; display: flex; align-items: center; justify-content: flex-end; }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Redeem Codes</h1>
    </div>

    <div class="grid-layout">
        
        <div style="min-width: 0;"> 
            
            <div class="flat-card border-t-4 border-t-yellow-500">
                <div class="flat-header">
                    <span>Add New Codes</span>
                    <i data-feather="plus-circle" style="width:16px;"></i>
                </div>
                <div class="flat-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="" disabled selected>-- Select --</option>
                                <?php 
                                $prods = $conn->query("SELECT p.id, p.name, g.name as gname FROM products p JOIN games g ON p.game_id=g.id WHERE g.type IN ('voucher','unipin') ORDER BY g.name");
                                while($p = $prods->fetch_assoc()) echo "<option value='{$p['id']}'>{$p['gname']} - {$p['name']}</option>"; 
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Codes (One per line)</label>
                            <textarea name="code" rows="5" class="form-textarea" placeholder="UP-1111&#10;UP-2222" required></textarea>
                        </div>
                        
                        <button type="submit" name="add_code" class="btn-save">Save Codes</button>
                    </form>
                </div>
            </div>

            <div class="flat-card">
                <div class="flat-header">
                    <span>Manual Assign</span>
                    <i data-feather="link" style="width:16px;"></i>
                </div>
                <div class="flat-body">
                    <p style="font-size:12px; color:#666; margin-bottom:15px; line-height:1.4;">Force assign a code to an order. This will mark order as <b>Completed</b>.</p>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Order ID</label>
                            <input type="number" name="order_id" class="form-input" placeholder="e.g. 501" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Available Code</label>
                            <select name="code_id" class="form-select" required>
                                <option value="" disabled selected>-- Select --</option>
                                <?php 
                                $codes = $conn->query("SELECT r.id, r.code, p.name FROM redeem_codes r JOIN products p ON r.product_id=p.id WHERE r.status='active' LIMIT 50");
                                if($codes && $codes->num_rows > 0) {
                                    while($c = $codes->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['name']} - ".substr($c['code'],0,10)."...</option>";
                                } else {
                                    echo "<option disabled>No codes</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="assign_order" class="btn-dark">Assign & Complete</button>
                    </form>
                </div>
            </div>

        </div>

        <div style="min-width: 0;">
            <div class="flat-card">
                <div class="flat-header">
                    <span>Active Inventory</span>
                    <span class="badge-count">
                        <?php echo $conn->query("SELECT COUNT(*) FROM redeem_codes WHERE status='active'")->fetch_row()[0]; ?> Codes
                    </span>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Code</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $list = $conn->query("SELECT r.*, p.name as pname, g.name as gname, g.image as gimg FROM redeem_codes r JOIN products p ON r.product_id=p.id JOIN games g ON p.game_id=g.id WHERE r.status='active' ORDER BY r.id DESC LIMIT 50");
                            
                            if($list && $list->num_rows > 0):
                                while($row = $list->fetch_assoc()): 
                                    $img = !empty($row['gimg']) ? "../".$row['gimg'] : "https://via.placeholder.com/30";
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?php echo $img; ?>" style="width:28px; height:28px; border-radius:4px; object-fit:cover; border:1px solid #333;">
                                        <div>
                                            <div style="font-size:12px; font-weight:700;"><?php echo htmlspecialchars($row['gname']); ?></div>
                                            <div style="font-size:11px; color:#666;"><?php echo htmlspecialchars($row['pname']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="code-box">
                                        <?php echo htmlspecialchars($row['code']); ?>
                                        <i data-feather="copy" class="copy-icon" style="width:12px;" onclick="copyText('<?php echo htmlspecialchars($row['code']); ?>')"></i>
                                    </div>
                                </td>
                                <td>
                                    <a href="?del=<?php echo $row['id']; ?>" onclick="return confirm('Delete?')" class="del-btn">
                                        <i data-feather="trash-2" style="width:16px;"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="3" style="text-align:center; padding:30px; color:#444;">No active codes found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    feather.replace();

    function copyText(text) {
        navigator.clipboard.writeText(text);
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 1000
        });
        Toast.fire({ icon: 'success', title: 'Copied!', background: '#1a1a1a', color: '#fff' });
    }
</script>
