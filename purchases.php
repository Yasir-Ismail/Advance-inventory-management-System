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
    <title>Stock In (Purchases) - IMS Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="bg-light">

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-auto">
            <?php include 'layout/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <main class="col main-wrapper animate-fade-in">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold mb-1">Stock In (Purchases)</h2>
                    <p class="text-muted small">Record and track inventory stock additions</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="utils/export_csv.php?type=purchases" class="btn btn-outline-success d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm bg-white border">
                        <i class="bi bi-file-earmark-excel"></i> <span>Export CSV</span>
                    </a>
                    <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#purchaseModal">
                        <i class="bi bi-plus-circle"></i> <span>New Purchase</span>
                    </button>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show premium-card border-0 mb-4 shadow-sm" style="border-left: 5px solid var(--accent-emerald) !important;">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show premium-card border-0 mb-4 shadow-sm" style="border-left: 5px solid var(--accent-ruby) !important;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small uppercase">
                            <th class="ps-3">DATE / TIME</th>
                            <th>PRODUCT</th>
                            <th>SUPPLIER</th>
                            <th>QUANTITY</th>
                            <th>UNIT PRICE</th>
                            <th class="pe-3">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $pur): ?>
                            <tr>
                                <td class="ps-3 py-4">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark"><?php echo date('M d, Y', strtotime($pur['purchase_date'])); ?></span>
                                        <span class="text-muted small"><?php echo date('H:i A', strtotime($pur['purchase_date'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-box text-primary small"></i>
                                        </div>
                                        <span class="fw-medium"><?php echo $pur['product_name']; ?></span>
                                    </div>
                                </td>
                                <td class="text-muted small"><?php echo $pur['supplier_name']; ?></td>
                                <td><span class="badge bg-light text-dark fw-normal px-3 py-2"><?php echo $pur['quantity']; ?> pcs</span></td>
                                <td class="text-muted">$<?php echo number_format($pur['purchase_price'], 2); ?></td>
                                <td class="pe-3 fw-bold text-primary">$<?php echo number_format($pur['total_amount'], 2); ?></td>
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
