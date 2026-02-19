<?php
require_once 'includes/auth.php';
checkAuth();
require_once 'includes/db.php';

$error = '';
$success = '';

// Handle Purchase (Stock In)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'purchase') {
    $product_id = $_POST['product_id'];
    $supplier_id = $_POST['supplier_id'];
    $quantity = $_POST['quantity'];
    $purchase_price = $_POST['purchase_price'];
    $total_amount = $quantity * $purchase_price;

    try {
        $pdo->beginTransaction();

        // 1. Save Purchase Record
        $stmt = $pdo->prepare("INSERT INTO purchases (product_id, supplier_id, quantity, purchase_price, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $supplier_id, $quantity, $purchase_price, $total_amount]);

        // 2. Update Product Quantity
        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);

        $pdo->commit();
        $success = "Stock In recorded successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Execution failed: " . $e->getMessage();
    }
}

// Fetch Data for Forms/Lists
$products = $pdo->query("SELECT id, name, category, quantity FROM products ORDER BY name ASC")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();
$purchases = $pdo->query("SELECT pur.*, p.name as product_name, s.name as supplier_name 
                          FROM purchases pur 
                          JOIN products p ON pur.product_id = p.id 
                          JOIN suppliers s ON pur.supplier_id = s.id 
                          ORDER BY pur.purchase_date DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In (Purchases) - IMS</title>
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
                <h1 class="h2">Stock In (Purchases)</h1>
                <div>
                    <a href="utils/export_csv.php?type=purchases" class="btn btn-outline-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchaseModal">
                        <i class="bi bi-plus-circle"></i> New Purchase
                    </button>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $pur): ?>
                            <tr>
                                <td class="small"><?php echo date('Y-m-d H:i', strtotime($pur['purchase_date'])); ?></td>
                                <td class="fw-bold"><?php echo $pur['product_name']; ?></td>
                                <td><?php echo $pur['supplier_name']; ?></td>
                                <td><?php echo $pur['quantity']; ?></td>
                                <td>$<?php echo number_format($pur['purchase_price'], 2); ?></td>
                                <td class="fw-bold">$<?php echo number_format($pur['total_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Purchase Modal -->
<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Purchase (Stock In)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="purchase">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?> (Current: <?php echo $p['quantity']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required min="1">
                        </div>
                        <div class="col">
                            <label class="form-label">Cost Price ($)</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" required min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Complete Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
