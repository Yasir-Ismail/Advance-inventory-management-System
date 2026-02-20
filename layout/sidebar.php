<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar shadow-lg" id="sidebarMenu">
    <div class="position-sticky pt-4 h-100">
        <div class="px-4 mb-5">
            <h3 class="text-white fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-box-seam-fill text-accent-sky"></i>
                <span>IMS <span class="fw-light">Pro</span></span>
            </h3>
        </div>

        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'suppliers.php' ? 'active' : ''; ?>" href="suppliers.php">
                    <i class="bi bi-truck"></i>
                    <span>Suppliers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'purchases.php' ? 'active' : ''; ?>" href="purchases.php">
                    <i class="bi bi-cart-plus"></i>
                    <span>Purchases</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                    <i class="bi bi-cash-stack"></i>
                    <span>Sales</span>
                </a>
            </li>
        </ul>

        <div class="mt-auto px-4 pt-5 pb-4">
            <hr class="text-white opacity-25 mb-4">
            <a class="nav-link text-danger-hover p-0 d-flex align-items-center gap-2" href="logout.php" style="color: rgba(255,255,255,0.6) !important;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </div>
</div>

<style>
    .text-accent-sky { color: #0ea5e9; }
    .text-danger-hover:hover { color: #ef4444 !important; }
</style>
