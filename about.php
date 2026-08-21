<?php
$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>About <?php echo APP_NAME; ?></h1>
        <p>Your trusted partner for quality vehicles</p>
    </div>
</div>

<div class="about-page">
    <div class="container">
        <div class="about-content">
            <section class="about-section">
                <h2>Who We Are</h2>
                <p><?php echo APP_NAME; ?> is a leading platform for renting and purchasing quality vehicles. We connect customers with a diverse selection of new and pre-owned cars from trusted manufacturers.</p>
                <p>Our mission is to make finding and acquiring your perfect vehicle as simple and transparent as possible. Whether you need a short-term rental or are looking to purchase your next car, we provide the tools and support to make informed decisions.</p>
            </section>
            
            <section class="about-section">
                <h2>What We Offer</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Wide Selection</h3>
                        <p>Browse through our extensive collection of vehicles from leading manufacturers across multiple categories.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Quality Assurance</h3>
                        <p>All vehicles undergo thorough inspection and verification to ensure they meet our quality standards.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Flexible Rental</h3>
                        <p>Rent cars for short or long-term periods with competitive daily rates and easy booking process.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>Easy Purchase</h3>
                        <p>Browse and purchase new or pre-owned vehicles with transparent pricing and secure transactions.</p>
                    </div>
                </div>
            </section>
            
            <section class="about-section">
                <h2>Our Commitment</h2>
                <p>We are committed to providing exceptional service and maintaining the highest standards of integrity. Our platform is designed with your convenience in mind, offering:</p>
                <ul>
                    <li>Transparent pricing with no hidden fees</li>
                    <li>Detailed vehicle information and specifications</li>
                    <li>Secure booking and payment processing</li>
                    <li>Responsive customer support</li>
                    <li>User-friendly interface for easy navigation</li>
                </ul>
            </section>
            
            <section class="about-section">
                <h2>Demo Project Notice</h2>
                <div class="demo-notice-box">
                    <i class="fas fa-info-circle"></i>
                    <p><strong>Important:</strong> This is a demonstration semester project created for educational purposes. All payment processing is simulated, and no real financial transactions occur. This platform showcases modern web development practices and full-stack application architecture.</p>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
