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
    <title>Suppliers - IMS</title>
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
                <h1 class="h2">Supplier Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Add Supplier
                </button>
            </div>

            <!-- Search Bar -->
            <form class="mb-4" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search suppliers..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                    <?php if ($search): ?>
                        <a href="suppliers.php" class="btn btn-outline-danger">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td class="fw-bold"><?php echo $s['name']; ?></td>
                                <td><?php echo $s['phone']; ?></td>
                                <td><?php echo $s['email']; ?></td>
                                <td><?php echo $s['address']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white" onclick='editSupplier(<?php echo json_encode($s); ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this supplier?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
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
