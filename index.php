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
    <title>Dashboard - IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .main-content { margin-left: 250px; padding: 20px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
        .card-stat { border: none; border-radius: 12px; transition: 0.3s; color: white; }
        .card-stat:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2">Dashboard Summary</h1>
                <div class="text-muted">Welcome, <?php echo $_SESSION['user_name']; ?></div>
            </div>

            <!-- Stats Overview -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card card-stat bg-primary p-3">
                        <h6>Total Products</h3>
                        <h2 class="fw-bold"><?php echo $total_products; ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat bg-success p-3">
                        <h6>Inventory Quantity</h3>
                        <h2 class="fw-bold"><?php echo $total_quantity; ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat bg-info p-3">
                        <h6>Stock Value</h3>
                        <h2 class="fw-bold">$<?php echo number_format($total_value, 2); ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat bg-danger p-3">
                        <h6>Low Stock Alerts</h3>
                        <h2 class="fw-bold"><?php echo $low_stock; ?></h2>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">Recent Transactions</div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#purchases">Purchases</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sales">Sales</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-3">
                                <div class="tab-pane fade show active" id="purchases">
                                    <table class="table table-hover">
                                        <thead><tr><th>Product</th><th>Qty</th><th>Total</th><th>Date</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($recent_purchases as $p): ?>
                                                <tr>
                                                    <td><?php echo $p['product_name']; ?></td>
                                                    <td><?php echo $p['quantity']; ?></td>
                                                    <td>$<?php echo number_format($p['total_amount'], 2); ?></td>
                                                    <td class="small"><?php echo date('M d, H:i', strtotime($p['purchase_date'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="sales">
                                    <table class="table table-hover">
                                        <thead><tr><th>Product</th><th>Qty</th><th>Total</th><th>Date</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($recent_sales as $s): ?>
                                                <tr>
                                                    <td><?php echo $s['product_name']; ?></td>
                                                    <td><?php echo $s['quantity']; ?></td>
                                                    <td>$<?php echo number_format($s['total_amount'], 2); ?></td>
                                                    <td class="small"><?php echo date('M d, H:i', strtotime($s['sale_date'])); ?></td>
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
                    <div class="card shadow-sm p-3">
                        <h6>Stock Level Overview</h6>
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock'],
            datasets: [{
                data: [<?php echo $total_products - $low_stock; ?>, <?php echo $low_stock; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
</body>
</html>
