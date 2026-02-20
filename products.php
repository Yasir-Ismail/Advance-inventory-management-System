<?php
require_once 'includes/auth.php';
checkAuth();
require_once 'includes/db.php';

// Handle Add/Edit/Delete Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'save') {
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'];
            $category = $_POST['category'];
            $cost_price = $_POST['cost_price'];
            $selling_price = $_POST['selling_price'];
            $quantity = $_POST['quantity'];
            $threshold = $_POST['low_stock_threshold'];
            $supplier_id = $_POST['supplier_id'];

            if ($id) {
                $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, cost_price=?, selling_price=?, quantity=?, low_stock_threshold=?, supplier_id=? WHERE id=?");
                $stmt->execute([$name, $category, $cost_price, $selling_price, $quantity, $threshold, $supplier_id, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, category, cost_price, selling_price, quantity, low_stock_threshold, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $category, $cost_price, $selling_price, $quantity, $threshold, $supplier_id]);
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
        }
        redirect('products.php');
    }
}

// Fetch Suppliers for dropdown
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();

// Fetch Products with Search/Filter
$search = $_GET['search'] ?? '';
$sql = "SELECT p.*, s.name as supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id";
if ($search) {
    $sql .= " WHERE p.name LIKE ? OR p.category LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query($sql);
}
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - IMS Pro</title>
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
                    <h2 class="fw-bold mb-1">Product Management</h2>
                    <p class="text-muted small">Manage your inventory products and stock levels</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="utils/export_csv.php?type=products" class="btn btn-outline-success d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm bg-white border">
                        <i class="bi bi-file-earmark-excel"></i> <span>Export CSV</span>
                    </a>
                    <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> <span>Add Product</span>
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="premium-card p-4 mb-4">
                <form method="GET">
                    <div class="input-group input-group-lg border rounded-3 overflow-hidden shadow-sm">
                        <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 ps-2" placeholder="Search by product name, category..." value="<?php echo htmlspecialchars($search); ?>" style="font-size: 1rem;">
                        <button class="btn btn-primary px-5 fw-medium border-0" type="submit">Search</button>
                        <?php if ($search): ?>
                            <a href="products.php" class="btn btn-outline-danger border-0 d-flex align-items-center px-4">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small uppercase">
                            <th class="ps-3">PRODUCT</th>
                            <th>CATEGORY</th>
                            <th>COST</th>
                            <th>SELLING</th>
                            <th>QUANTITY</th>
                            <th>SUPPLIER</th>
                            <th class="text-end pe-3">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr class="<?php echo $p['quantity'] <= $p['low_stock_threshold'] ? 'low-stock-row' : ''; ?>">
                                <td class="ps-3 py-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="product-icon-box bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo $p['name']; ?></div>
                                            <div class="text-muted small">ID: #<?php echo $p['id']; ?></div>
                                        </div>
                                        <?php if ($p['quantity'] <= $p['low_stock_threshold']): ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger px-3">Low Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark fw-normal px-3 py-2"><?php echo $p['category']; ?></span></td>
                                <td class="text-muted fw-medium">$<?php echo number_format($p['cost_price'], 2); ?></td>
                                <td class="fw-bold text-dark">$<?php echo number_format($p['selling_price'], 2); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold <?php echo $p['quantity'] <= $p['low_stock_threshold'] ? 'text-danger' : 'text-primary'; ?>"><?php echo $p['quantity']; ?></span>
                                        <span class="text-muted small">in stock</span>
                                    </div>
                                </td>
                                <td class="text-muted small"><?php echo $p['supplier_name'] ?: 'N/A'; ?></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-action-edit me-2" onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <button class="btn btn-action-delete text-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<style>
    .low-stock-row { border-left: 4px solid var(--accent-ruby) !important; }
    .btn-action-edit, .btn-action-delete { border: none; background: #f8fafc; border-radius: 8px; width: 36px; height: 36px; transition: var(--transition); }
    .btn-action-edit:hover { background: #e0f2fe; color: #0284c7; }
    .btn-action-delete:hover { background: #fee2e2; color: #ef4444; }
</style>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="prod_id">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" id="prod_name" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" id="prod_category" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" id="prod_supplier" class="form-select">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Cost Price ($)</label>
                            <input type="number" step="0.01" name="cost_price" id="prod_cost" class="form-control" required min="0">
                        </div>
                        <div class="col">
                            <label class="form-label">Selling Price ($)</label>
                            <input type="number" step="0.01" name="selling_price" id="prod_selling" class="form-control" required min="0">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Initial Quantity</label>
                            <input type="number" name="quantity" id="prod_qty" class="form-control" required min="0">
                        </div>
                        <div class="col">
                            <label class="form-label">Low Stock Alert at</label>
                            <input type="number" name="low_stock_threshold" id="prod_threshold" class="form-control" value="5" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function resetForm() {
        document.getElementById('modalTitle').innerText = 'Add New Product';
        document.getElementById('prod_id').value = '';
        document.getElementById('prod_name').value = '';
        document.getElementById('prod_category').value = '';
        document.getElementById('prod_supplier').value = '';
        document.getElementById('prod_cost').value = '';
        document.getElementById('prod_selling').value = '';
        document.getElementById('prod_qty').value = '';
        document.getElementById('prod_threshold').value = '5';
    }

    function editProduct(p) {
        document.getElementById('modalTitle').innerText = 'Edit Product';
        document.getElementById('prod_id').value = p.id;
        document.getElementById('prod_name').value = p.name;
        document.getElementById('prod_category').value = p.category;
        document.getElementById('prod_supplier').value = p.supplier_id;
        document.getElementById('prod_cost').value = p.cost_price;
        document.getElementById('prod_selling').value = p.selling_price;
        document.getElementById('prod_qty').value = p.quantity;
        document.getElementById('prod_threshold').value = p.low_stock_threshold;
        
        var modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    }
</script>
</body>
</html>
