<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="admin-mobile-topbar">
    <button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-label="Open admin menu" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>
    <span class="admin-mobile-title">
        <i class="fas fa-gauge-high"></i> Admin Panel
    </span>
    <a href="<?php echo APP_URL; ?>/logout.php" class="admin-mobile-logout" aria-label="Logout">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</div>

<div class="admin-overlay" id="adminOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-logo">
        <i class="fas fa-gauge-high"></i>
        <span>Admin Panel</span>
        <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close admin menu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="admin-nav">
        <a href="index.php" class="admin-nav-item <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a href="cars.php" class="admin-nav-item <?php echo $currentPage == 'cars.php' || $currentPage == 'car-form.php' ? 'active' : ''; ?>">
            <i class="fas fa-car"></i>
            <span>Cars</span>
        </a>
        <a href="brands.php" class="admin-nav-item <?php echo $currentPage == 'brands.php' ? 'active' : ''; ?>">
            <i class="fas fa-tag"></i>
            <span>Brands</span>
        </a>
        <a href="reservations.php" class="admin-nav-item <?php echo $currentPage == 'reservations.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Reservations</span>
        </a>
        <a href="purchases.php" class="admin-nav-item <?php echo $currentPage == 'purchases.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Purchases</span>
        </a>
        <a href="users.php" class="admin-nav-item <?php echo $currentPage == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="reviews.php" class="admin-nav-item <?php echo $currentPage == 'reviews.php' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i>
            <span>Reviews</span>
        </a>
        <div class="admin-nav-divider"></div>
        <a href="<?php echo APP_URL; ?>/index.php" class="admin-nav-item">
            <i class="fas fa-home"></i>
            <span>Back to Site</span>
        </a>
        <a href="<?php echo APP_URL; ?>/logout.php" class="admin-nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>
