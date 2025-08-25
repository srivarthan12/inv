<?php
$currentPage = $_GET['page'] ?? 'stock_management';
?>
<nav class="col-md-2 d-none d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'stock_management') ? 'active' : ''; ?>" href="index.php?page=stock_management">
                    <i class="bi bi-box-seam"></i> Stock Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'product_management') ? 'active' : ''; ?>" href="index.php?page=product_management">
                    <i class="bi bi-list-ul"></i> Product Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'expo_stock') ? 'active' : ''; ?>" href="index.php?page=expo_stock">
                    <i class="bi bi-shop-window"></i> Expo Stock
                </a>
            </li>
        </ul>
    </div>
</nav>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">