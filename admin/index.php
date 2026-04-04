<?php 
include 'common/header.php'; 

// ====================================================
// HELPER FUNCTION: PERCENTAGE CALCULATION
// ====================================================
function calculateGrowth($current, $previous) {
    if ($previous == 0) {
        return ($current > 0) ? 100 : 0;
    }
    return round((($current - $previous) / $previous) * 100, 1); // 1 decimal place
}

// ====================================================
// 1. REVENUE STATISTICS
// ====================================================
// Total Revenue (All time)
$rev_query = $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed'");
$total_revenue = ($rev_query && $row = $rev_query->fetch_row()) ? $row[0] : 0;

// Current Month Revenue
$cur_rev_q = $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$current_month_revenue = ($cur_rev_q && $row = $cur_rev_q->fetch_row()) ? $row[0] : 0;

// Last Month Revenue
$last_rev_q = $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
$last_month_revenue = ($last_rev_q && $row = $last_rev_q->fetch_row()) ? $row[0] : 0;

$revenue_growth = calculateGrowth($current_month_revenue, $last_month_revenue);


// ====================================================
// 2. USER STATISTICS
// ====================================================
// Total Users
$users_query = $conn->query("SELECT COUNT(*) FROM users");
$total_users = $users_query ? $users_query->fetch_row()[0] : 0;

// Current Month Users
$cur_user_q = $conn->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$current_month_users = ($cur_user_q && $row = $cur_user_q->fetch_row()) ? $row[0] : 0;

// Last Month Users
$last_user_q = $conn->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
$last_month_users = ($last_user_q && $row = $last_user_q->fetch_row()) ? $row[0] : 0;

$user_growth = calculateGrowth($current_month_users, $last_month_users);


// ====================================================
// 3. ORDER STATISTICS
// ====================================================
// Total Orders
$orders_query = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $orders_query ? $orders_query->fetch_row()[0] : 0;

// Current Month Orders
$cur_ord_q = $conn->query("SELECT COUNT(*) FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$current_month_orders = ($cur_ord_q && $row = $cur_ord_q->fetch_row()) ? $row[0] : 0;

// Last Month Orders
$last_ord_q = $conn->query("SELECT COUNT(*) FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
$last_month_orders = ($last_ord_q && $row = $last_ord_q->fetch_row()) ? $row[0] : 0;

$order_growth = calculateGrowth($current_month_orders, $last_month_orders);


// ====================================================
// FETCH RECENT ORDERS
// ====================================================
$recent_orders = $conn->query("
    SELECT o.*, u.name as user_name, g.name as game_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN games g ON o.game_id = g.id 
    ORDER BY o.created_at DESC LIMIT 10
");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --bg-body: #050505;
        --bg-card: #111111; /* Very dark card background */
        --border: #222222;
        --text-primary: #ffffff;
        --text-secondary: #9ca3af; /* Grayish text */
        --accent-green: #4ade80; /* Bright Green */
    }

    /* Reset & Base */
    * { box-sizing: border-box; }
    
    body {
        background-color: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        overflow-x: hidden; 
    }

    .dashboard-container {
        padding: 15px; 
        max-width: 100%;
        margin: 0 auto;
    }

    h1 { margin: 0 0 20px 0; font-size: 20px; font-weight: 600; padding-left: 5px; color: #fff; }

    /* GRID SYSTEM */
    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .grid-charts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100%, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }
    @media (min-width: 768px) {
        .grid-charts { grid-template-columns: 1fr 1fr; }
    }

    /* === NEW CARD DESIGN (MATCHING IMAGE) === */
    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px; /* Smooth rounded corners */
        padding: 24px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        min-height: 160px;
        /* box-shadow removed */
    }

    .card-label { 
        font-size: 14px; 
        color: var(--text-secondary); 
        margin-bottom: 8px; 
        font-weight: 500; 
    }
    
    .card-value { 
        font-size: 32px; 
        font-weight: 700; 
        color: #fff; 
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    
    .trend-container {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--accent-green);
        font-weight: 500;
        z-index: 10;
        position: relative;
    }

    .trend-arrow { font-size: 14px; }

    /* WAVE GRAPH (SPARKLINE) */
    .sparkline-wrapper {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 50px; /* Height of the wave */
        z-index: 1;
        opacity: 1;
    }

    /* CHART BOXES */
    .chart-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        width: 100%;
    }
    .chart-header { font-size: 15px; font-weight: 600; margin-bottom: 15px; color: #d1d5db; }

    /* TABLE SECTION */
    .table-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        width: 100%;
        overflow: hidden;
    }
    
    .table-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .search-input {
        background: #1a1a1a;
        border: 1px solid var(--border);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        width: 100%;
        max-width: 250px;
        font-size: 13px;
        outline: none;
    }

    /* RESPONSIVE TABLE */
    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; min-width: 650px; }
    
    th { 
        text-align: left; 
        color: var(--text-secondary); 
        font-size: 12px; 
        font-weight: 600; 
        padding: 12px 0; 
        border-bottom: 1px solid var(--border); 
        white-space: nowrap;
    }
    
    td { 
        padding: 16px 0; 
        border-bottom: 1px solid var(--border); 
        font-size: 13px; 
        color: #e5e5e5; 
        vertical-align: middle;
    }
    
    tr:last-child td { border-bottom: none; }

    /* STATUS BADGES */
    .status-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
        white-space: nowrap;
    }
    .st-pending { background: rgba(234, 179, 8, 0.1); color: #facc15; }
    .st-completed { background: rgba(34, 197, 94, 0.1); color: #4ade80; }
    .st-cancelled { background: rgba(239, 68, 68, 0.1); color: #f87171; }

</style>

<div class="dashboard-container">
    
    <div class="grid-stats">
        
        <div class="card">
            <div class="card-label">Revenue</div>
            <div class="card-value">BDT <?php echo number_format((float)$total_revenue, 2); ?></div>
            <div class="trend-container">
                Increase by <?php echo $revenue_growth; ?>% in this month 
                <i class="fa-solid fa-arrow-trend-up trend-arrow"></i>
            </div>
            <div class="sparkline-wrapper">
                <canvas id="sparkRevenue"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-label">New customers</div>
            <div class="card-value"><?php echo number_format((int)$total_users); ?></div>
            <div class="trend-container">
                Increase by <?php echo $user_growth; ?>% in this month 
                <i class="fa-solid fa-arrow-trend-up trend-arrow"></i>
            </div>
            <div class="sparkline-wrapper">
                <canvas id="sparkUsers"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-label">New orders</div>
            <div class="card-value"><?php echo number_format((int)$total_orders); ?></div>
            <div class="trend-container">
                Increase by <?php echo $order_growth; ?>% in this month 
                <i class="fa-solid fa-arrow-trend-up trend-arrow"></i>
            </div>
            <div class="sparkline-wrapper">
                <canvas id="sparkOrders"></canvas>
            </div>
        </div>

    </div>

    <div class="grid-charts">
        <div class="chart-box">
            <div class="chart-header">Orders per month</div>
            <div style="height: 200px;">
                <canvas id="mainOrdersChart"></canvas>
            </div>
        </div>
        <div class="chart-box">
            <div class="chart-header">Users per month</div>
            <div style="height: 200px;">
                <canvas id="mainUsersChart"></canvas>
            </div>
        </div>
    </div>

    <div class="table-box">
        <div class="table-top">
            <div class="chart-header" style="margin:0; font-size:16px;">Latest Orders</div>
            <input type="text" class="search-input" placeholder="Search orders...">
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="15%">Order Date</th>
                        <th width="15%">Order ID</th>
                        <th width="20%">User</th>
                        <th width="20%">Product</th>
                        <th width="15%">Amount</th>
                        <th width="15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($recent_orders && $recent_orders->num_rows > 0): 
                        while($row = $recent_orders->fetch_assoc()): 
                            $st = isset($row['status']) ? strtolower($row['status']) : 'pending';
                            $userName = isset($row['user_name']) ? $row['user_name'] : 'Guest';
                            $gameName = isset($row['game_name']) ? $row['game_name'] : 'Unknown';
                            $amount = isset($row['amount']) ? $row['amount'] : 0;
                            $orderId = isset($row['id']) ? $row['id'] : '#';
                            $date = isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '-';

                            $badge = 'st-pending';
                            if($st == 'completed') $badge = 'st-completed';
                            if($st == 'cancelled') $badge = 'st-cancelled';
                    ?>
                    <tr>
                        <td style="color:#9ca3af;"><?php echo $date; ?></td>
                        <td style="font-family: monospace; color:#6b7280; font-size:12px;"><?php echo $orderId; ?></td>
                        <td><?php echo htmlspecialchars($userName ?? ''); ?></td>
                        <td style="color:#9ca3af;"><?php echo htmlspecialchars($gameName ?? ''); ?></td>
                        <td style="font-weight: 600;">BDT <?php echo number_format((float)$amount); ?></td>
                        <td><span class="status-badge <?php echo $badge; ?>"><?php echo ucfirst($st); ?></span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 20px; color:#6b7280;">No recent orders found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // Config for the Wave Effect (Sparkline)
    const sparkConfig = {
        type: 'line',
        data: {
            labels: [1, 2, 3, 4, 5, 6, 7],
            datasets: [{
                data: [10, 25, 18, 30, 25, 40, 50], // Dummy Data for wave visual
                borderColor: '#4ade80', // Green Color
                borderWidth: 2,
                tension: 0.5, // High tension for smooth wave
                pointRadius: 0, // No dots
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            layout: { padding: 0 }
        }
    };

    // Render Wave Charts
    new Chart(document.getElementById('sparkRevenue'), JSON.parse(JSON.stringify(sparkConfig)));
    new Chart(document.getElementById('sparkUsers'), JSON.parse(JSON.stringify(sparkConfig)));
    new Chart(document.getElementById('sparkOrders'), JSON.parse(JSON.stringify(sparkConfig)));

    // Main Chart Config
    const mainChartConfig = (ctx, label) => {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Feb 1', 'Feb 5', 'Feb 10', 'Feb 15', 'Feb 20'],
                datasets: [{
                    label: label,
                    data: [10, 25, 20, 35, 30], // Replace with real PHP JSON data if needed
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74, 222, 128, 0.05)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: '#111',
                    pointBorderColor: '#4ade80',
                    pointRadius: 4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#222' }, ticks: { color: '#6b7280', font: {size: 11} } },
                    y: { grid: { color: '#222' }, ticks: { color: '#6b7280', font: {size: 11} } }
                }
            }
        });
    };

    // Render Main Charts
    mainChartConfig(document.getElementById('mainOrdersChart'), 'Orders');
    mainChartConfig(document.getElementById('mainUsersChart'), 'Users');
</script>
