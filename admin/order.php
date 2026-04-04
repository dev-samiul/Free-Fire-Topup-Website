<?php 
include 'common/header.php'; 

// ====================================================
// 1. HANDLE SINGLE DELETE
// ====================================================
if(isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM orders WHERE id=$id");
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success', title: 'Deleted!', text: 'Order #$id removed.',
                background: '#1a1a1a', color: '#fff', confirmButtonColor: '#eab308'
            }).then(() => { window.location.href = 'order.php'; });
        });
    </script>";
}

// ====================================================
// 2. STATS LOGIC
// ====================================================
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0] ?? 0;
$open_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0] ?? 0;
$avg_row = $conn->query("SELECT AVG(amount) FROM orders")->fetch_row();
$avg_price = ($avg_row && $avg_row[0]) ? number_format($avg_row[0], 2) : "0.00";

// ====================================================
// 3. FILTER & PAGINATION
// ====================================================
$where = "WHERE 1=1";
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
if($status_filter != 'all') {
    $sf = $conn->real_escape_string($status_filter);
    $where .= " AND o.status = '$sf'";
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
if(!empty($search)) {
    $where .= " AND (o.id LIKE '%$search%' OR u.name LIKE '%$search%' OR p.name LIKE '%$search%')";
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$sql = "SELECT o.*, u.name as uname, p.name as pname 
        FROM orders o 
        LEFT JOIN users u ON o.user_id=u.id 
        LEFT JOIN products p ON o.product_id=p.id 
        $where ORDER BY o.id DESC LIMIT $start, $limit";
$orders = $conn->query($sql);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --bg-body: #050505;
        --bg-card: #111111;
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
        font-family: 'Lato', sans-serif;
        overflow-x: hidden;
    }

    .container-fluid { padding: 20px; }
    
    .page-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; }

    /* STATS CARDS */
    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
    }

    .stat-label { font-size: 13px; color: var(--text-secondary); margin-bottom: 5px; font-weight: 500; }
    .stat-value { font-size: 28px; font-weight: 700; color: #fff; z-index: 2; position: relative; }

    .wave-container {
        position: absolute;
        bottom: 0; left: 0; width: 100%; height: 50px;
        z-index: 1; opacity: 0.6; pointer-events: none;
    }

    /* TABS */
    .tabs-wrapper {
        background: var(--bg-card);
        border-radius: 12px; padding: 8px; border: 1px solid var(--border);
        margin-bottom: 20px; white-space: nowrap; overflow-x: auto;
    }
    .tab-link {
        display: inline-block; padding: 8px 16px; font-size: 13px;
        color: var(--text-secondary); text-decoration: none; border-radius: 6px;
        margin-right: 5px; transition: all 0.2s; font-weight: 500;
    }
    .tab-link:hover { color: #fff; background: #1a1a1a; }
    .tab-link.active { background: #1f1f1f; color: var(--accent); font-weight: 700; border: 1px solid #333; }

    /* TABLE */
    .table-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 12px; overflow: hidden;
    }
    .table-header {
        padding: 15px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }
    .search-input-group {
        flex: 1; background: #1a1a1a; border: 1px solid var(--border);
        border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; gap: 10px;
    }
    .search-input-group input { background: transparent; border: none; outline: none; color: #fff; width: 100%; font-size: 13px; }

    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    
    th {
        text-align: left; padding: 15px 20px; color: var(--text-secondary);
        font-size: 12px; font-weight: 600; border-bottom: 1px solid var(--border); background: #161616;
    }
    td {
        padding: 15px 20px; border-bottom: 1px solid var(--border);
        font-size: 13px; color: #e5e5e5; vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #1a1a1a; }

    /* STATUS BADGE */
    .status-badge {
        padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; border: 1px solid;
    }
    .st-completed { color: #22c55e; border-color: rgba(34, 197, 94, 0.3); background: rgba(34, 197, 94, 0.05); }
    .st-pending { color: #eab308; border-color: rgba(234, 179, 8, 0.3); background: rgba(234, 179, 8, 0.05); }
    .st-cancelled { color: #ef4444; border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.05); }

    .action-btn { 
        display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; 
        text-decoration: none; cursor: pointer; margin-right: 10px;
    }
    .btn-view { color: #aaa; }
    .btn-delete { color: var(--red); }
</style>

<div class="container-fluid">
    
    <h1 class="page-title">Orders</h1>

    <div class="grid-stats">
        
        <div class="stat-card">
            <div>
                <div class="stat-label">Orders</div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            </div>
            <div class="wave-container">
                <canvas id="waveOrders"></canvas>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <div class="stat-label">Open orders</div>
                <div class="stat-value"><?php echo number_format($open_orders); ?></div>
            </div>
            <div class="wave-container">
                <canvas id="waveOpen"></canvas>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <div class="stat-label">Average price</div>
                <div class="stat-value"><?php echo $avg_price; ?></div>
            </div>
            <div class="wave-container">
                <canvas id="waveAvg"></canvas>
            </div>
        </div>

    </div>

    <div class="tabs-wrapper">
        <a href="?status=all" class="tab-link <?php echo $status_filter=='all'?'active':''; ?>">All</a>
        <a href="?status=completed" class="tab-link <?php echo $status_filter=='completed'?'active':''; ?>">Completed</a>
        <a href="?status=pending" class="tab-link <?php echo $status_filter=='pending'?'active':''; ?>">Pending</a>
        <a href="?status=cancelled" class="tab-link <?php echo $status_filter=='cancelled'?'active':''; ?>">Cancelled</a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <form method="GET" class="search-input-group">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                <i data-feather="search" style="width:16px; color:#666;"></i>
                <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox"></th>
                        <th>ID</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($orders && $orders->num_rows > 0): 
                        while($row = $orders->fetch_assoc()): 
                            $st = strtolower($row['status'] ?? 'pending');
                            $badge = ($st == 'completed') ? 'st-completed' : (($st == 'cancelled') ? 'st-cancelled' : 'st-pending');
                    ?>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td style="color:#666; font-family:monospace;"><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['uname'] ?? 'Guest'); ?></td>
                        <td>
                            <div style="font-weight:500;"><?php echo htmlspecialchars($row['pname'] ?? 'Unknown'); ?></div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $badge; ?>"><?php echo strtoupper($st); ?></span>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">
                                <i data-feather="eye" style="width:14px;"></i> View
                            </a>
                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="action-btn btn-delete">
                                <i data-feather="trash" style="width:14px;"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px; color:#666;">No orders found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    feather.replace();

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete?', text: "Cannot revert!", icon: 'warning',
            background: '#1a1a1a', color: '#fff', showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#333', confirmButtonText: 'Yes'
        }).then((res) => {
            if (res.isConfirmed) window.location.href = '?delete_id=' + id;
        });
    }

    // CHART CONFIG: WHITE / LIGHT GRAY LINES
    const waveConfig = {
        type: 'line',
        data: {
            labels: [1, 2, 3, 4, 5, 6, 7],
            datasets: [{
                data: [12, 19, 15, 25, 22, 30, 28],
                borderColor: 'rgba(255, 255, 255, 0.5)', // White/Light Gray
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0,
                fill: false
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            layout: { padding: 0 }
        }
    };

    new Chart(document.getElementById('waveOrders'), JSON.parse(JSON.stringify(waveConfig)));
    new Chart(document.getElementById('waveOpen'), JSON.parse(JSON.stringify(waveConfig)));
    new Chart(document.getElementById('waveAvg'), JSON.parse(JSON.stringify(waveConfig)));
</script>
