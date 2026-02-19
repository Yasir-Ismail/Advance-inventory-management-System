<?php
require_once 'includes/auth.php';
checkAuth();
require_once 'includes/db.php';

$error = '';
$success = '';

// Handle Sale (Stock Out)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'sale') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Check Current Stock
    $stmt = $pdo->prepare("SELECT quantity, selling_price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['quantity'] >= $quantity) {
        $selling_price = $product['selling_price'];
        $total_amount = $quantity * $selling_price;

        try {
            $pdo->beginTransaction();

            // 1. Save Sales Record
            $stmt = $pdo->prepare("INSERT INTO sales (product_id, quantity, selling_price, total_amount) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $quantity, $selling_price, $total_amount]);

            // 2. Decrease Product Quantity
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $product_id]);

            $pdo->commit();
            $success = "Sale recorded successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Execution failed: " . $e->getMessage();
        }
    } else {
        $error = "Insufficient stock! Only " . ($product['quantity'] ?? 0) . " items available.";
    }
}

// Fetch Data for Forms/Lists
$products = $pdo->query("SELECT id, name, category, quantity, selling_price FROM products ORDER BY name ASC")->fetchAll();
$sales = $pdo->query("SELECT s.*, p.name as product_name 
                      FROM sales s 
                      JOIN products p ON s.product_id = p.id 
                      ORDER BY s.sale_date DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out (Sales) - IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .main-content { margin-left: 250px; padding: 20px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-10 ms-sm-auto main-content">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Stock Out (Sales)</h1>
                <div>
                    <a href="utils/export_csv.php?type=sales" class="btn btn-outline-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </a>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#saleModal">
                        <i class="bi bi-cart-dash"></i> New Sale
                    </button>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Selling Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td class="small"><?php echo date('Y-m-d H:i', strtotime($s['sale_date'])); ?></td>
                                <td class="fw-bold"><?php echo $s['product_name']; ?></td>
                                <td><?php echo $s['quantity']; ?></td>
                                <td>$<?php echo number_format($s['selling_price'], 2); ?></td>
                                <td class="fw-bold">$<?php echo number_format($s['total_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Sale Modal -->
<div class="modal fade" id="saleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Sale (Stock Out)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="sale">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select" required onchange="updatePrice(this)">
                            <option value="">Select Product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['selling_price']; ?>" data-qty="<?php echo $p['quantity']; ?>">
                                    <?php echo $p['name']; ?> (Available: <?php echo $p['quantity']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="sale_qty" class="form-control" required min="1" oninput="calculateTotal()">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Unit Price ($)</label>
                            <input type="text" id="unit_price" class="form-control" readonly>
                        </div>
                        <div class="col">
                            <label class="form-label">Total Amount ($)</label>
                            <input type="text" id="total_amount" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Complete Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updatePrice(select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        document.getElementById('unit_price').value = price ? '$' + parseFloat(price).toFixed(2) : '';
        calculateTotal();
    }

    function calculateTotal() {
        const qty = document.getElementById('sale_qty').value;
        const select = document.querySelector('select[name="product_id"]');
        const price = select.options[select.selectedIndex].getAttribute('data-price');
        
        if (qty && price) {
            document.getElementById('total_amount').value = '$' + (qty * price).toFixed(2);
        } else {
            document.getElementById('total_amount').value = '';
        }
    }
</script>
</body>
</html>
