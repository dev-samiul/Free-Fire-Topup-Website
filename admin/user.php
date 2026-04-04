<?php 
include 'common/header.php'; 

// ====================================================
// 1. SELF-HEALING DATABASE (Fixes Missing Columns)
// ====================================================
// Check if 'role' column exists, if not, add it
$check_role = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if($check_role->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
}

// Check if 'status' column exists, if not, add it
$check_status = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if($check_status->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active'");
}

// Check if 'support_pin' column exists (Just in case)
$check_pin = $conn->query("SHOW COLUMNS FROM users LIKE 'support_pin'");
if($check_pin->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN support_pin VARCHAR(6) DEFAULT '0000'");
}

// ====================================================
// 2. HANDLE ACTIONS
// ====================================================

// ADD USER
if(isset($_POST['add_user'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $pass = $_POST['password']; 
    $role = $_POST['role'];
    $balance = (float)$_POST['balance'];
    
    // Insert with new columns
    $conn->query("INSERT INTO users (name, email, phone, password, role, balance, status) VALUES ('$name', '$email', '$phone', '$pass', '$role', '$balance', 'active')");
    echo "<script>window.location.href='user.php';</script>";
}

// UPDATE USER
if(isset($_POST['update_user'])) {
    $uid = (int)$_POST['edit_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $bal = (float)$_POST['balance'];
    $role = $_POST['role'];
    
    $sql = "UPDATE users SET name='$name', phone='$phone', email='$email', balance='$bal', role='$role'";
    if(!empty($_POST['password'])) {
        $pass = $_POST['password'];
        $sql .= ", password='$pass'";
    }
    $sql .= " WHERE id=$uid";
    $conn->query($sql);
    echo "<script>window.location.href='user.php';</script>";
}

// TOGGLE STATUS
if(isset($_GET['toggle_status'])) {
    $uid = (int)$_GET['toggle_status'];
    $curr = $_GET['curr']; 
    $new = ($curr == 'active') ? 'inactive' : 'active';
    $conn->query("UPDATE users SET status='$new' WHERE id=$uid");
    echo "<script>window.location.href='user.php';</script>";
}

// DELETE USER
if(isset($_GET['delete_id'])) {
    $uid = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id=$uid");
    echo "<script>window.location.href='user.php';</script>";
}

// ====================================================
// 3. FILTERS & PAGINATION
// ====================================================
$where = "WHERE 1=1";

// Role Tab
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
if($role_filter != 'all') {
    $where .= " AND role = '$role_filter'";
}

// Search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
if(!empty($search)) {
    $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Select Query
$sql = "SELECT * FROM users $where ORDER BY id DESC LIMIT $start, $limit";
$users = $conn->query($sql);

$total_rows_q = $conn->query("SELECT COUNT(*) FROM users $where");
$total_rows = $total_rows_q ? $total_rows_q->fetch_row()[0] : 0;
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
        --accent: #eab308;
        --green: #22c55e;
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
    
    .btn-new-user {
        background: var(--accent);
        color: #000;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 13px;
        border: none;
        cursor: pointer;
        display: inline-block;
    }

    /* TABS */
    .tabs-container {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    .tabs-wrapper {
        background: #1a1a1a;
        padding: 4px;
        border-radius: 8px;
        display: flex;
        gap: 5px;
        border: 1px solid var(--border);
    }
    .tab-item {
        padding: 6px 20px;
        border-radius: 6px;
        font-size: 13px;
        color: var(--text-secondary);
        text-decoration: none;
        transition: 0.2s;
    }
    .tab-item.active {
        background: #2a2a2a; 
        color: var(--accent);
        font-weight: 600;
    }

    /* TABLE CARD */
    .table-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    /* FILTER BAR */
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
        padding: 8px 12px;
    }
    .search-box input {
        background: transparent; border: none; outline: none;
        color: #fff; width: 100%; font-size: 13px; margin-left: 10px;
    }

    .icon-btn {
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        color: var(--text-secondary);
        cursor: pointer;
        position: relative;
    }
    .badge-count {
        position: absolute; top: 0; right: 0;
        background: var(--accent); color: #000;
        font-size: 9px; font-weight: 700; width: 14px; height: 14px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }

    /* TABLE */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    
    th {
        text-align: left;
        padding: 12px 16px;
        color: var(--text-secondary); 
        font-size: 12px;
        font-weight: 600;
        border-bottom: 1px solid var(--border);
        background: #161616;
    }
    
    td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
        color: #fff;
        vertical-align: middle;
    }
    
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* ELEMENTS */
    .balance-box {
        background: #1a1a1a;
        border: 1px solid #333;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: monospace;
        display: inline-block;
    }

    /* TOGGLE SWITCH */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 34px;
        height: 18px;
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
        height: 14px; width: 14px;
        left: 2px; bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: #eab308; }
    input:checked + .slider:before { transform: translateX(16px); }

    .btn-edit-text {
        color: var(--accent);
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .role-badge {
        font-size: 10px; font-weight: 700; padding: 3px 6px; border-radius: 4px; text-transform: uppercase;
    }
    .role-admin { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    .role-user { background: rgba(255, 255, 255, 0.1); color: #aaa; border: 1px solid #333; }

    /* MODAL */
    .modal {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.8); z-index: 100;
        align-items: center; justify-content: center;
    }
    .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border);
        width: 90%; max-width: 500px;
        border-radius: 12px; padding: 25px;
        position: relative;
    }
    .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; }
    
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px; }
    .form-input { 
        width: 100%; background: var(--bg-input); border: 1px solid var(--border);
        padding: 10px; border-radius: 6px; color: #fff; outline: none;
    }
    .form-input:focus { border-color: #444; }
    
    .btn-submit {
        width: 100%; background: var(--accent); color: #000;
        padding: 12px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;
    }

    /* FOOTER */
    .table-footer { padding: 15px; display: flex; justify-content: center; border-top: 1px solid var(--border); }
    .limit-select { background: #1a1a1a; color: #fff; border: 1px solid #333; padding: 5px 10px; border-radius: 6px; }

</style>

<div class="container-fluid">
    
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <button onclick="openModal('addModal')" class="btn-new-user">New user</button>
    </div>

    <div class="tabs-container">
        <div class="tabs-wrapper">
            <a href="?role=all" class="tab-item <?php echo $role_filter=='all'?'active':''; ?>">All</a>
            <a href="?role=user" class="tab-item <?php echo $role_filter=='user'?'active':''; ?>">User</a>
            <a href="?role=admin" class="tab-item <?php echo $role_filter=='admin'?'active':''; ?>">Admin</a>
        </div>
    </div>

    <div class="table-card">
        
        <div class="filter-bar">
            <form method="GET" class="search-box">
                <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
                <i data-feather="search" style="color: #666; width: 16px;"></i>
                <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
            </form>
            <div class="icon-btn">
                <i data-feather="columns" style="width: 18px;"></i>
            </div>
            <div class="icon-btn">
                <div class="badge-count">0</div>
                <i data-feather="filter" style="width: 18px;"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" style="accent-color:#eab308;"></th>
                        <th>Name <i data-feather="chevron-down" style="width:12px; vertical-align:middle;"></i></th>
                        <th>Phone <i data-feather="chevron-down" style="width:12px; vertical-align:middle;"></i></th>
                        <th>Balance <i data-feather="chevron-down" style="width:12px; vertical-align:middle;"></i></th>
                        <th>Status <i data-feather="chevron-down" style="width:12px; vertical-align:middle;"></i></th>
                        <th>Reseller <i data-feather="chevron-down" style="width:12px; vertical-align:middle;"></i></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users && $users->num_rows > 0): 
                        while($row = $users->fetch_assoc()): 
                            // SAFE DEFAULT VALUES using Null Coalescing ??
                            $u_role = $row['role'] ?? 'user';
                            $u_status = $row['status'] ?? 'active';

                            $roleBadge = ($u_role == 'admin') ? 'role-admin' : 'role-user';
                            $isActive = ($u_status == 'active');
                            $isReseller = false; // Default false
                    ?>
                    <tr>
                        <td><input type="checkbox" style="accent-color:#eab308;"></td>
                        <td>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($row['name']); ?></div>
                            <span class="role-badge <?php echo $roleBadge; ?>"><?php echo strtoupper($u_role); ?></span>
                        </td>
                        <td style="color:#aaa;"><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td>
                            <div class="balance-box"><?php echo number_format($row['balance'], 2); ?></div>
                        </td>
                        <td>
                            <a href="?toggle_status=<?php echo $row['id']; ?>&curr=<?php echo $u_status; ?>" class="toggle-switch">
                                <input type="checkbox" <?php echo $isActive?'checked':''; ?>>
                                <span class="slider"></span>
                            </a>
                        </td>
                        <td>
                            <a href="?toggle_reseller=<?php echo $row['id']; ?>&curr=<?php echo $isReseller?1:0; ?>" class="toggle-switch">
                                <input type="checkbox" <?php echo $isReseller?'checked':''; ?>>
                                <span class="slider"></span>
                            </a>
                        </td>
                        <td>
                            <?php 
                                // Ensure role/status exist in array before encoding
                                $row['role'] = $u_role; 
                                $row['status'] = $u_status;
                            ?>
                            <a href="javascript:void(0)" onclick='openEditModal(<?php echo json_encode($row); ?>)' class="btn-edit-text">
                                <i data-feather="edit-2" style="width:14px;"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:#666;">No users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <select class="limit-select">
                <option>10</option>
                <option>25</option>
            </select>
        </div>
    </div>

</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">New User</h2>
        <form method="POST">
            <input type="hidden" name="add_user" value="1">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="text" name="password" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Balance</label>
                <input type="number" step="0.01" name="balance" class="form-input" value="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="closeModal('addModal')" style="flex:1; background:transparent; border:1px solid #333; color:#fff; padding:12px; border-radius:8px;">Cancel</button>
                <button type="submit" style="flex:1;" class="btn-submit">Add User</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="modal-title">Edit User</h2>
            <a id="del_link" href="#" style="color:var(--red); font-size:12px; text-decoration:none;">Delete</a>
        </div>
        <form method="POST">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" name="edit_id" id="edit_id">
            
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" id="edit_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" id="edit_phone" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">New Password (Optional)</label>
                <input type="text" name="password" class="form-input" placeholder="Leave empty to keep current">
            </div>
            <div class="form-group">
                <label class="form-label">Balance</label>
                <input type="number" step="0.01" name="balance" id="edit_balance" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" id="edit_role" class="form-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="closeModal('editModal')" style="flex:1; background:transparent; border:1px solid #333; color:#fff; padding:12px; border-radius:8px;">Cancel</button>
                <button type="submit" style="flex:1;" class="btn-submit">Save</button>
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

    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_phone').value = user.phone;
        document.getElementById('edit_balance').value = user.balance;
        document.getElementById('edit_role').value = user.role || 'user'; // Fallback
        document.getElementById('del_link').href = '?delete_id=' + user.id;
        
        openModal('editModal');
    }
</script>
