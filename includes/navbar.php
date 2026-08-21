<nav class="navbar">
    <div class="container">
        <div class="nav-wrapper">
            <a href="<?php echo APP_URL; ?>/index.php" class="logo">
                <span class="logo-mark"><i class="fas fa-car"></i></span>
                <span><?php echo APP_NAME; ?></span>
            </a>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open menu" aria-expanded="false" aria-controls="navMenu">
                <span class="hamburger" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            
            <div class="nav-overlay" id="navOverlay"></div>
            
            <div class="nav-menu" id="navMenu">
                <div class="nav-drawer-top">
                    <span class="nav-drawer-brand">
                        <span class="logo-mark"><i class="fas fa-car"></i></span>
                        <span class="nav-drawer-title">Menu</span>
                    </span>
                    <button type="button" class="nav-drawer-close" id="navDrawerClose" aria-label="Close menu">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <ul class="nav-links">
                    <li><a href="<?php echo APP_URL; ?>/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="<?php echo APP_URL; ?>/cars.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : ''; ?>">Cars</a></li>
                    <li><a href="<?php echo APP_URL; ?>/brands.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'brands.php' ? 'active' : ''; ?>">Brands</a></li>
                    <li><a href="<?php echo APP_URL; ?>/about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                    <?php if (!isAdmin()): ?>
                        <li><a href="<?php echo APP_URL; ?>/contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-actions">
                    <?php if (isLoggedIn() && isAdmin()): ?>
                        <a href="<?php echo APP_URL; ?>/admin/index.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-gauge-high"></i> Admin
                        </a>
                        <span class="nav-user-chip">
                            <i class="fas fa-user-shield"></i>
                            <?php echo e($currentUser['name']); ?>
                        </span>
                        <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-sm btn-outline btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php elseif (isLoggedIn()): ?>
                        <div class="user-dropdown">
                            <button class="user-dropdown-toggle" type="button" aria-expanded="false">
                                <span class="user-avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></span>
                                <span class="user-dropdown-name"><?php echo e($currentUser['name']); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-dropdown-menu">
                                <a href="<?php echo APP_URL; ?>/dashboard.php"><i class="fas fa-gauge-high"></i> Dashboard</a>
                                <a href="<?php echo APP_URL; ?>/my-reservations.php"><i class="fas fa-calendar-check"></i> My Reservations</a>
                                <a href="<?php echo APP_URL; ?>/my-reviews.php"><i class="fas fa-star"></i> My Reviews</a>
                                <a href="<?php echo APP_URL; ?>/profile.php"><i class="fas fa-user"></i> Profile</a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo APP_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                        
                        <div class="nav-account-panel">
                            <div class="nav-account-head">
                                <span class="user-avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></span>
                                <div>
                                    <strong><?php echo e($currentUser['name']); ?></strong>
                                    <span>Your account</span>
                                </div>
                            </div>
                            <a href="<?php echo APP_URL; ?>/dashboard.php"><i class="fas fa-gauge-high"></i> Dashboard</a>
                            <a href="<?php echo APP_URL; ?>/my-reservations.php"><i class="fas fa-calendar-check"></i> My Reservations</a>
                            <a href="<?php echo APP_URL; ?>/my-reviews.php"><i class="fas fa-star"></i> My Reviews</a>
                            <a href="<?php echo APP_URL; ?>/profile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="<?php echo APP_URL; ?>/logout.php" class="nav-account-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo APP_URL; ?>/login.php" class="btn btn-sm btn-outline">Login</a>
                        <a href="<?php echo APP_URL; ?>/signup.php" class="btn btn-sm btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
