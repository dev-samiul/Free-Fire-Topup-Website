<?php 
include 'common/header.php'; 

// 1. VALIDATE ID
if(!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='order.php';</script>";
    exit;
}
$oid = (int)$_GET['id'];

// 2. HANDLE FORM SUBMISSION (UPDATE)
if(isset($_POST['update_order'])) {
    $amount = (float)$_POST['amount'];
    $status = $conn->real_escape_string($_POST['status']);
    
    // Update Query
    $update_sql = "UPDATE orders SET amount = '$amount', status = '$status' WHERE id = $oid";
    if($conn->query($update_sql)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Order details saved successfully.',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#eab308'
                });
            });
        </script>";
    }
}

// 3. HANDLE DELETE
if(isset($_POST['delete_order'])) {
    $conn->query("DELETE FROM orders WHERE id = $oid");
    echo "<script>window.location.href='order.php';</script>";
    exit;
}

// 4. FETCH DATA
$sql = "SELECT o.*, u.name as uname, u.email as uemail, g.name as gname, p.name as pname 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        LEFT JOIN games g ON o.game_id = g.id 
        LEFT JOIN products p ON o.product_id = p.id 
        WHERE o.id = $oid";
$result = $conn->query($sql);

if($result->num_rows == 0) {
    echo "<script>window.location.href='order.php';</script>";
    exit;
}

$order = $result->fetch_assoc();
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
        --accent: #eab308; /* Yellow */
        --red: #ef4444;
        --green: #22c55e;
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        margin: 0;
        overflow-x: hidden; /* Prevent body scroll */
    }

    .container-fluid { padding: 20px; max-width: 800px; margin: 0 auto; }

    /* HEADER */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .page-title { font-size: 24px; font-weight: 700; margin: 0; }
    
    .btn-delete-top {
        background: var(--red);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-delete-top:hover { opacity: 0.9; }

    /* FORM CARD */
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .form-group { margin-bottom: 20px; }
    
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 8px;
    }
    .form-label span { color: var(--red); } /* Asterisk */

    .form-control, .form-select, textarea {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        color: #fff;
        font-size: 14px;
        outline: none;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.2s;
        box-sizing: border-box; /* Fix input overflow */
    }
    
    .form-control:focus, textarea:focus { border-color: #444; }
    
    textarea { min-height: 100px; resize: vertical; }

    /* ACCOUNT INFO BOX */
    .account-info-box {
        background: var(--bg-input);
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }
    .account-row {
        display: flex;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
    }
    .account-row:last-child { border-bottom: none; }
    
    .col-name { width: 40%; color: var(--text-secondary); font-size: 13px; font-weight: 500; }
    .col-value { width: 60%; color: var(--text-secondary); font-size: 13px; font-family: monospace; word-break: break-all; }

    /* ACTION BUTTONS */
    .btn-group { display: flex; gap: 10px; margin-top: 10px; }
    
    .btn-save {
        background: var(--accent);
        color: #000;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
    }
    
    .btn-cancel {
        background: transparent;
        color: #fff;
        border: 1px solid var(--border);
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-cancel:hover { background: var(--bg-input); }

    /* TRANSACTION SECTION */
    .section-title { font-size: 16px; font-weight: 600; margin-bottom: 15px; margin-top: 10px; }
    
    /* Responsive Table Wrapper */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
    }

    .transaction-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px; /* Forces scroll on mobile */
    }
    
    .transaction-table th {
        text-align: left;
        padding: 12px 16px;
        background: var(--bg-input);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    
    .transaction-table td {
        padding: 14px 16px;
        color: #fff;
        font-size: 13px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    
    .transaction-table tr:last-child td { border-bottom: none; }
    
    .type-badge {
        color: var(--red);
        background: rgba(239, 68, 68, 0.1);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

</style>

<div class="container-fluid">

    <div class="page-header">
        <h1 class="page-title">Edit Order</h1>
        <form method="POST" id="deleteForm" onsubmit="return confirmDelete(event)">
            <input type="hidden" name="delete_order" value="1">
            <button type="submit" class="btn-delete-top">Delete</button>
        </form>
    </div>

    <form method="POST" class="form-card">
        <input type="hidden" name="update_order" value="1">

        <div class="form-group">
            <label class="form-label">Delivery Message</label>
            <textarea name="delivery_message" placeholder="Type here..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Amount</label>
            <input type="text" name="amount" class="form-control" value="<?php echo number_format($order['amount'], 2, '.', ''); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Status<span>*</span></label>
            <select name="status" class="form-select">
                <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>Pending</option>
                <option value="completed" <?php echo $order['status']=='completed'?'selected':''; ?>>Completed</option>
                <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                <option value="processing" <?php echo $order['status']=='processing'?'selected':''; ?>>Processing</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Account Info</label>
            <div class="account-info-box">
                <div class="account-row">
                    <div class="col-name">Name</div>
                    <div class="col-value">Value</div>
                </div>
                <div class="account-row">
                    <div class="col-name">player_id</div>
                    <div class="col-value"><?php echo htmlspecialchars($order['player_id']); ?></div>
                </div>
                <?php if(!empty($order['zone_id'])): ?>
                <div class="account-row">
                    <div class="col-name">zone_id</div>
                    <div class="col-value"><?php echo htmlspecialchars($order['zone_id']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-save">Save changes</button>
            <a href="order.php" class="btn-cancel">Cancel</a>
        </div>
    </form>

    <div>
        <h3 class="section-title">Transaction</h3>
        
        <div class="table-responsive">
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Transaction ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i data-feather="credit-card" style="width:14px; color:#888;"></i>
                                <?php echo htmlspecialchars($order['payment_method']); ?>
                            </div>
                        </td>
                        <td><span class="type-badge">Payment</span></td>
                        <td style="color:#aaa;"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        <td style="font-weight: 600;">- <?php echo number_format($order['amount'], 2); ?></td>
                        <td style="font-family: monospace; color:#666;"><?php echo htmlspecialchars($order['transaction_id']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    feather.replace();

    function confirmDelete(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            background: '#1a1a1a',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#333',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm').submit();
            }
        });
    }
</script>
