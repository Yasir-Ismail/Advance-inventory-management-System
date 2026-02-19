<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse shadow" id="sidebarMenu">
    <div class="position-sticky pt-3 h-100">
        <div class="text-center mb-4">
            <h4 class="text-white fw-bold">IMS Admin</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo $currentPage == 'index.php' ? 'active bg-primary' : ''; ?>" href="index.php">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo $currentPage == 'products.php' ? 'active bg-primary' : ''; ?>" href="products.php">
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo $currentPage == 'suppliers.php' ? 'active bg-primary' : ''; ?>" href="suppliers.php">
                    Suppliers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo $currentPage == 'purchases.php' ? 'active bg-primary' : ''; ?>" href="purchases.php">
                    Purchases (Stock In)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo $currentPage == 'sales.php' ? 'active bg-primary' : ''; ?>" href="sales.php">
                    Sales (Stock Out)
                </a>
            </li>
        </ul>
        <hr class="text-secondary mx-3">
        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .sidebar { min-height: 100vh; position: fixed; left: 0; z-index: 1000; }
    .nav-link { padding: 12px 20px; font-size: 1.05rem; transition: 0.3s; }
    .nav-link:hover { background-color: rgba(255,255,255,0.1); }
    .active { border-radius: 5px; margin: 0 10px; }
</style>
