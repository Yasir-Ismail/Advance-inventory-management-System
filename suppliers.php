<?php
require_once 'includes/auth.php';
checkAuth();
require_once 'includes/db.php';

// Handle Add/Edit/Delete Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];

        if ($_POST['action'] == 'save') {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE suppliers SET name=?, phone=?, email=?, address=? WHERE id=?");
                $stmt->execute([$name, $phone, $email, $address, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, email, address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $phone, $email, $address]);
            }
        } elseif ($_POST['action'] == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id=?");
            $stmt->execute([$id]);
        }
        redirect('suppliers.php');
    }
}

// Fetch Suppliers with Search
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM suppliers";
if ($search) {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query($sql);
}
$suppliers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - IMS Pro</title>
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
                    <h2 class="fw-bold mb-1">Supplier Management</h2>
                    <p class="text-muted small">Manage your product suppliers and contact details</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> <span>Add Supplier</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="premium-card p-4 mb-4">
                <form method="GET">
                    <div class="input-group input-group-lg border rounded-3 overflow-hidden shadow-sm">
                        <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 ps-2" placeholder="Search suppliers by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>" style="font-size: 1rem;">
                        <button class="btn btn-primary px-5 fw-medium border-0" type="submit">Search</button>
                        <?php if ($search): ?>
                            <a href="suppliers.php" class="btn btn-outline-danger border-0 d-flex align-items-center px-4">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small uppercase">
                            <th class="ps-3">ID</th>
                            <th>SUPPLIER NAME</th>
                            <th>CONTACT INFO</th>
                            <th>ADDRESS</th>
                            <th class="text-end pe-3">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td class="ps-3 py-4 text-muted small">#<?php echo $s['id']; ?></td>
                                <td class="fw-bold text-dark"><?php echo $s['name']; ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="small fw-medium"><i class="bi bi-telephone text-muted me-2"></i><?php echo $s['phone']; ?></span>
                                        <span class="small text-muted"><i class="bi bi-envelope text-muted me-2"></i><?php echo $s['email']; ?></span>
                                    </div>
                                </td>
                                <td class="text-muted small" style="max-width: 250px;"><?php echo $s['address']; ?></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-action-edit me-2" onclick='editSupplier(<?php echo json_encode($s); ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this supplier?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
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
    .btn-action-edit, .btn-action-delete { border: none; background: #f8fafc; border-radius: 8px; width: 36px; height: 36px; transition: var(--transition); }
    .btn-action-edit:hover { background: #e0f2fe; color: #0284c7; }
    .btn-action-delete:hover { background: #fee2e2; color: #ef4444; }
</style>

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="sup_id">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="name" id="sup_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="sup_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" id="sup_email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="sup_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function resetForm() {
        document.getElementById('modalTitle').innerText = 'Add New Supplier';
        document.getElementById('sup_id').value = '';
        document.getElementById('sup_name').value = '';
        document.getElementById('sup_phone').value = '';
        document.getElementById('sup_email').value = '';
        document.getElementById('sup_address').value = '';
    }

    function editSupplier(s) {
        document.getElementById('modalTitle').innerText = 'Edit Supplier';
        document.getElementById('sup_id').value = s.id;
        document.getElementById('sup_name').value = s.name;
        document.getElementById('sup_phone').value = s.phone;
        document.getElementById('sup_email').value = s.email;
        document.getElementById('sup_address').value = s.address;
        
        var modal = new bootstrap.Modal(document.getElementById('supplierModal'));
        modal.show();
    }
</script>
</body>
</html>
