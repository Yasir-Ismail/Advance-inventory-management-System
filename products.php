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
    <title>Products - IMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .main-content { margin-left: 250px; padding: 20px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
        .low-stock { background-color: #ffe5e5 !important; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-10 ms-sm-auto main-content">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Product Management</h1>
                <div>
                    <a href="utils/export_csv.php?type=products" class="btn btn-outline-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <form class="mb-4" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or category..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                    <?php if ($search): ?>
                        <a href="products.php" class="btn btn-outline-danger">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Cost</th>
                            <th>Selling</th>
                            <th>Qty</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr class="<?php echo $p['quantity'] <= $p['low_stock_threshold'] ? 'low-stock' : ''; ?>">
                                <td><?php echo $p['id']; ?></td>
                                <td>
                                    <?php echo $p['name']; ?>
                                    <?php if ($p['quantity'] <= $p['low_stock_threshold']): ?>
                                        <span class="badge bg-danger ms-2">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo $p['category']; ?></span></td>
                                <td>$<?php echo number_format($p['cost_price'], 2); ?></td>
                                <td>$<?php echo number_format($p['selling_price'], 2); ?></td>
                                <td class="fw-bold"><?php echo $p['quantity']; ?></td>
                                <td><?php echo $p['supplier_name'] ?: 'N/A'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white" onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
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
