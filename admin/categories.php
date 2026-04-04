<?php 
include 'common/header.php'; 

// ====================================================
// 1. SELF-HEALING DATABASE
// ====================================================
// Ensure table exists (Basic check)
$conn->query("CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), image VARCHAR(255))");

// Check/Add 'slot' column
$check_slot = $conn->query("SHOW COLUMNS FROM categories LIKE 'slot'");
if($check_slot->num_rows == 0) {
    $conn->query("ALTER TABLE categories ADD COLUMN slot INT DEFAULT 0");
}

// Check/Add 'status' column
$check_status = $conn->query("SHOW COLUMNS FROM categories LIKE 'status'");
if($check_status->num_rows == 0) {
    $conn->query("ALTER TABLE categories ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
}

// ====================================================
// 2. HANDLE ACTIONS
// ====================================================

// ADD CATEGORY
if(isset($_POST['add_category'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $slot = (int)$_POST['slot'];
    $status = $conn->real_escape_string($_POST['status']);
    
    // Image handling (Placeholder logic - you can expand this)
    $image = 'default.png'; 
    
    $conn->query("INSERT INTO categories (name, slot, status, image) VALUES ('$title', '$slot', '$status', '$image')");
    echo "<script>window.location.href='categories.php';</script>";
}

// UPDATE CATEGORY
if(isset($_POST['update_category'])) {
    $cid = (int)$_POST['edit_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $slot = (int)$_POST['slot'];
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("UPDATE categories SET name='$title', slot='$slot', status='$status' WHERE id=$cid");
    echo "<script>window.location.href='categories.php';</script>";
}

// DELETE CATEGORY
if(isset($_GET['delete_id'])) {
    $cid = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM categories WHERE id=$cid");
    echo "<script>window.location.href='categories.php';</script>";
}

// TOGGLE STATUS
if(isset($_GET['toggle_status'])) {
    $cid = (int)$_GET['toggle_status'];
    $curr = $_GET['curr'];
    $new = ($curr == 'active') ? 'inactive' : 'active';
    $conn->query("UPDATE categories SET status='$new' WHERE id=$cid");
    echo "<script>window.location.href='categories.php';</script>";
}

// ====================================================
// 3. SEARCH & PAGINATION
// ====================================================
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = "WHERE 1=1";
if(!empty($search)) {
    $where .= " AND name LIKE '%$search%'";
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$sql = "SELECT * FROM categories $where ORDER BY slot ASC, id DESC LIMIT $start, $limit";
$categories = $conn->query($sql);

$total_rows = $conn->query("SELECT COUNT(*) FROM categories $where")->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);
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
        --accent: #f59e0b; /* Amber/Orange */
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
    .page-header { margin-bottom: 20px; }
    .page-title { font-size: 24px; font-weight: 700; margin-bottom: 15px; }
    
    .btn-new {
        background: var(--accent);
        color: #000;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        border: none;
        cursor: pointer;
        display: inline-block;
    }

    /* CARD & FILTER */
    .table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    .filter-bar {
        padding: 15px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .search-box {
        flex: 1;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        display: flex; align-items: center;
        padding: 10px 12px;
    }
    .search-box input {
        background: transparent; border: none; outline: none;
        color: #fff; width: 100%; font-size: 14px; margin-left: 10px;
    }

    .icon-btn {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        color: var(--text-secondary);
        cursor: pointer;
    }

    /* TABLE */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 700px; }
    
    th {
        text-align: left;
        padding: 15px 20px;
        color: var(--text-secondary); 
        font-size: 13px;
        font-weight: 600;
        border-bottom: 1px solid var(--border);
        background: #161616;
    }
    
    td {
        padding: 15px 20px;
        border-bottom: 1px solid var(--border);
        font-size: 14px;
        color: #fff;
        vertical-align: middle;
    }
    
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* TOGGLE SWITCH */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 36px;
        height: 20px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #4b5563; 
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute; content: "";
        height: 16px; width: 16px;
        left: 2px; bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--accent); }
    input:checked + .slider:before { transform: translateX(16px); }

    /* ACTION BUTTONS */
    .action-group { display: flex; gap: 15px; }
    .btn-action {
        display: flex; align-items: center; gap: 5px;
        font-size: 13px; font-weight: 600;
        text-decoration: none; cursor: pointer;
    }
    .btn-edit { color: var(--accent); }
    .btn-delete { color: #f87171; }

    /* MODAL */
    .modal {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.8); z-index: 100;
        align-items: center; justify-content: center;
    }
    .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border);
        width: 90%; max-width: 450px;
        border-radius: 12px; padding: 25px;
        position: relative;
    }
    .modal-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; }
    
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 13px; color: var(--text-primary); margin-bottom: 8px; font-weight: 600; }
    .form-label span { color: var(--red); }
    
    .form-input, .form-select { 
        width: 100%; background: var(--bg-input); border: 1px solid var(--border);
        padding: 12px; border-radius: 8px; color: #fff; outline: none; font-size: 14px;
    }
    .form-input:focus { border-color: #444; }
    
    .btn-submit {
        background: var(--accent); color: #000;
        padding: 10px 20px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;
    }
    .btn-cancel {
        background: var(--bg-input); color: #fff;
        padding: 10px 20px; border: 1px solid var(--border); border-radius: 8px; font-weight: 600; cursor: pointer;
    }

    /* Delete Btn in Modal */
    .btn-modal-delete {
        background: var(--red); color: white;
        padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700;
        border: none; cursor: pointer; margin-bottom: 15px;
    }

    /* Footer */
    .table-footer { padding: 15px; display: flex; justify-content: center; border-top: 1px solid var(--border); }
    .limit-select { background: #1a1a1a; color: #fff; border: 1px solid #333; padding: 5px 12px; border-radius: 6px; }

</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Categories</h1>
        <button onclick="openModal('addModal')" class="btn-new">New category</button>
    </div>

    <div class="table-card">
        
        <div class="filter-bar">
            <form method="GET" class="search-box">
                <i data-feather="search" style="color: #666; width: 18px;"></i>
                <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
            </form>
            <div class="icon-btn">
                <i data-feather="columns" style="width: 20px;"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" style="accent-color:#f59e0b;"></th>
                        <th>Title <i data-feather="chevron-down" style="width:14px; vertical-align:middle;"></i></th>
                        <th>Slot <i data-feather="chevron-down" style="width:14px; vertical-align:middle;"></i></th>
                        <th>Status <i data-feather="chevron-down" style="width:14px; vertical-align:middle;"></i></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($categories && $categories->num_rows > 0): 
                        while($row = $categories->fetch_assoc()): 
                            $status = $row['status'] ?? 'active';
                            $isActive = ($status == 'active');
                    ?>
                    <tr>
                        <td><input type="checkbox" style="accent-color:#f59e0b;"></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $row['slot'] ?? 0; ?></td>
                        <td>
                            <a href="?toggle_status=<?php echo $row['id']; ?>&curr=<?php echo $status; ?>" class="toggle-switch">
                                <input type="checkbox" <?php echo $isActive?'checked':''; ?>>
                                <span class="slider"></span>
                            </a>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="javascript:void(0)" onclick='openEditModal(<?php echo json_encode($row); ?>)' class="btn-action btn-edit">
                                    <i data-feather="edit-2" style="width:14px;"></i> Edit
                                </a>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-action btn-delete">
                                    <i data-feather="trash" style="width:14px;"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">No categories found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div style="display:flex; align-items:center; gap:10px; color:#666; font-size:13px;">
                Per page 
                <select class="limit-select">
                    <option>10</option>
                    <option>25</option>
                </select>
            </div>
        </div>
    </div>

</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">New Category</h2>
        <form method="POST">
            <input type="hidden" name="add_category" value="1">
            
            <div class="form-group">
                <label class="form-label">Title<span>*</span></label>
                <input type="text" name="title" class="form-input" required placeholder="e.g. FREE FIRE">
            </div>
            
            <div class="form-group">
                <label class="form-label">Slot<span>*</span></label>
                <input type="number" name="slot" class="form-input" value="0" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status<span>*</span></label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn-submit">Save changes</button>
                <button type="button" onclick="closeModal('addModal')" class="btn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">Edit Category</h2>
        
        <form id="deleteForm" method="GET">
            <input type="hidden" name="delete_id" id="del_input_id">
            <button type="button" onclick="submitDelete()" class="btn-modal-delete">Delete</button>
        </form>

        <form method="POST">
            <input type="hidden" name="update_category" value="1">
            <input type="hidden" name="edit_id" id="edit_id">
            
            <div class="form-group">
                <label class="form-label">Title<span>*</span></label>
                <input type="text" name="title" id="edit_title" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Slot<span>*</span></label>
                <input type="number" name="slot" id="edit_slot" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status<span>*</span></label>
                <select name="status" id="edit_status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn-submit">Save changes</button>
                <button type="button" onclick="closeModal('editModal')" class="btn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    feather.replace();

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openEditModal(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_title').value = data.name;
        document.getElementById('edit_slot').value = data.slot || 0;
        document.getElementById('edit_status').value = data.status || 'active';
        document.getElementById('del_input_id').value = data.id;
        
        openModal('editModal');
    }

    function submitDelete() {
        if(confirm('Are you sure you want to delete this category?')) {
            document.getElementById('deleteForm').submit();
        }
    }

    function confirmDelete(id) {
        if(confirm('Are you sure?')) {
            window.location.href = '?delete_id=' + id;
        }
    }
</script>
