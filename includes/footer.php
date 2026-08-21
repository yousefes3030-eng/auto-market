<?php if (empty($isAdminPage)): ?>
    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-car"></i> <?php echo APP_NAME; ?></h3>
                    <p>Your trusted platform for renting and buying quality cars. Find your perfect vehicle today.</p>
                    <div class="footer-notice">
                        <i class="fas fa-info-circle"></i>
                        <strong>Demo Project:</strong> All payments are simulated. No real money is processed.
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/cars.php">Browse Cars</a></li>
                        <li><a href="<?php echo APP_URL; ?>/brands.php">Brands</a></li>
                        <li><a href="<?php echo APP_URL; ?>/about.php">About Us</a></li>
                        <?php if (!isAdmin()): ?>
                            <li><a href="<?php echo APP_URL; ?>/contact.php">Contact</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul>
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo APP_URL; ?>/admin/index.php">Admin Panel</a></li>
                            <li><a href="<?php echo APP_URL; ?>/logout.php">Logout</a></li>
                        <?php elseif (isLoggedIn()): ?>
                            <li><a href="<?php echo APP_URL; ?>/dashboard.php">Dashboard</a></li>
                            <li><a href="<?php echo APP_URL; ?>/my-reservations.php">My Reservations</a></li>
                            <li><a href="<?php echo APP_URL; ?>/profile.php">Profile</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo APP_URL; ?>/login.php">Login</a></li>
                            <li><a href="<?php echo APP_URL; ?>/signup.php">Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <?php if (!isAdmin()): ?>
                    <div class="footer-section">
                        <h4>Contact</h4>
                        <ul class="footer-contact">
                            <li><i class="fas fa-envelope"></i> info@automarket.com</li>
                            <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                            <li><i class="fas fa-map-marker-alt"></i> 123 Auto Street, Car City</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Demo Semester Project. All payments are simulated and no real money is processed.</p>
            </div>
        </div>
    </footer>
<?php endif; ?>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php echo $additionalJS; ?>
    <?php endif; ?>
</body>
</html>
