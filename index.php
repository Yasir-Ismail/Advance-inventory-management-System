<?php
require_once 'includes/auth.php';
checkAuth();
require_once 'includes/db.php';

// Summary Data
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_quantity = $pdo->query("SELECT SUM(quantity) FROM products")->fetchColumn() ?: 0;
$total_value = $pdo->query("SELECT SUM(quantity * cost_price) FROM products")->fetchColumn() ?: 0.00;
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= low_stock_threshold")->fetchColumn();

// Recent Purchases
$recent_purchases = $pdo->query("SELECT p.*, pr.name as product_name FROM purchases p JOIN products pr ON p.product_id = pr.id ORDER BY p.purchase_date DESC LIMIT 5")->fetchAll();

// Recent Sales
$recent_sales = $pdo->query("SELECT s.*, pr.name as product_name FROM sales s JOIN products pr ON s.product_id = pr.id ORDER BY s.sale_date DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IMS Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-auto">
            <?php include 'layout/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <main class="col main-wrapper">
            <div class="animate-fade-in">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="fw-bold mb-1">Dashboard Overview</h2>
                        <p class="text-muted small">Welcome back, <span class="fw-medium text-primary"><?php echo $_SESSION['user_name']; ?></span></p>
                    </div>
                    <div class="bg-white p-2 rounded-3 shadow-sm border">
                        <span class="text-muted small px-2"><i class="bi bi-calendar3 me-2"></i><?php echo date('D, M d Y'); ?></span>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="premium-card stat-card bg-grad-sky p-4">
                            <i class="bi bi-box-seam card-icon-bg"></i>
                            <h6 class="opacity-75 mb-2">Total Products</h6>
                            <h2 class="fw-bold mb-0"><?php echo $total_products; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="premium-card stat-card bg-grad-emerald p-4">
                            <i class="bi bi-stack card-icon-bg"></i>
                            <h6 class="opacity-75 mb-2">Inventory Quantity</h6>
                            <h2 class="fw-bold mb-0"><?php echo $total_quantity; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="premium-card stat-card bg-grad-sunset p-4">
                            <i class="bi bi-currency-dollar card-icon-bg"></i>
                            <h6 class="opacity-75 mb-2">Stock Value</h6>
                            <h2 class="fw-bold mb-0">$<?php echo number_format($total_value, 2); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="premium-card stat-card bg-grad-ruby p-4">
                            <i class="bi bi-exclamation-triangle card-icon-bg"></i>
                            <h6 class="opacity-75 mb-2">Low Stock Alerts</h6>
                            <h2 class="fw-bold mb-0"><?php echo $low_stock; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="premium-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Recent Transactions</h5>
                                <ul class="nav nav-pills nav-pills-custom" id="transactionTabs">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#purchases">Purchases</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sales">Sales</button>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="tab-content mt-3">
                                <div class="tab-pane fade show active" id="purchases">
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr class="text-muted small">
                                                    <th>PRODUCT</th>
                                                    <th>QUANTITY</th>
                                                    <th>TOTAL AMOUNT</th>
                                                    <th>DATE</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_purchases as $p): ?>
                                                    <tr>
                                                        <td class="fw-medium"><?php echo $p['product_name']; ?></td>
                                                        <td><span class="badge bg-light text-dark fw-normal"><?php echo $p['quantity']; ?> pcs</span></td>
                                                        <td class="text-primary fw-bold">$<?php echo number_format($p['total_amount'], 2); ?></td>
                                                        <td class="text-muted small"><?php echo date('M d, H:i', strtotime($p['purchase_date'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="sales">
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr class="text-muted small">
                                                    <th>PRODUCT</th>
                                                    <th>QUANTITY</th>
                                                    <th>TOTAL AMOUNT</th>
                                                    <th>DATE</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_sales as $s): ?>
                                                    <tr>
                                                        <td class="fw-medium"><?php echo $s['product_name']; ?></td>
                                                        <td><span class="badge bg-light text-dark fw-normal"><?php echo $s['quantity']; ?> pcs</span></td>
                                                        <td class="text-accent-emerald fw-bold">$<?php echo number_format($s['total_amount'], 2); ?></td>
                                                        <td class="text-muted small"><?php echo date('M d, H:i', strtotime($s['sale_date'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="premium-card p-4 h-100">
                            <h5 class="fw-bold mb-4">Stock Overview</h5>
                            <div class="position-relative" style="height: 250px;">
                                <canvas id="stockChart"></canvas>
                            </div>
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted"><i class="bi bi-circle-fill text-accent-emerald me-2"></i>Healthly Stock</span>
                                    <span class="fw-bold small"><?php echo $total_products - $low_stock; ?> Items</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted"><i class="bi bi-circle-fill text-accent-ruby me-2"></i>Low Stock</span>
                                    <span class="fw-bold small"><?php echo $low_stock; ?> Items</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .nav-pills-custom .nav-link { background: var(--bg-light); color: var(--secondary-color); font-size: 0.85rem; padding: 8px 16px; border-radius: 8px; margin-left: 8px; transition: var(--transition); }
    .nav-pills-custom .nav-link.active { background: var(--accent-sky); color: #fff; }
    .text-accent-emerald { color: #10b981; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Healthy', 'Low Stock'],
            datasets: [{
                data: [<?php echo $total_products - $low_stock; ?>, <?php echo $low_stock; ?>],
                backgroundColor: ['#10b981', '#ef4444'],
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>
