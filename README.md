# AutoMarket - Car Rental & Purchase Platform

A complete full-stack web application for renting and purchasing cars, built with PHP, MySQL, HTML, CSS, and JavaScript.

## 🚗 Project Overview

AutoMarket is a production-ready car rental and sales platform that allows users to browse, rent, and purchase vehicles. It features a complete user management system, reservation system with date validation, simulated payment processing, reviews and ratings, and a comprehensive admin dashboard.

**Important:** This is a demonstration semester project. All payment processing is simulated using a fake PayPal system. No real money is processed.

## ✨ Features

### Public Features
- 🏠 Modern homepage with hero section and featured cars
- 🚙 Advanced car browsing with search and filters
- 🔍 Detailed car pages with image galleries
- ⭐ Reviews and ratings system
- 📱 Fully responsive design
- 🔐 User authentication (signup/login)

### User Features
- 📅 Car rental reservation system with date validation
- 💳 Simulated PayPal payment processing
- 🛒 Car purchase functionality
- 📊 Personal dashboard with statistics
- 📝 Manage reservations (view and cancel)
- ⭐ Write and manage reviews
- 👤 Profile management

### Admin Features
- 📈 Comprehensive dashboard with statistics
- 🚗 Car management (CRUD operations)
- 🏷️ Brand management
- 📅 Reservation management
- 👥 User management
- ⭐ Review management
- 📊 Revenue tracking

### Technical Features
- ✅ PDO prepared statements (SQL injection protection)
- 🔒 Password hashing with bcrypt
- 🛡️ CSRF protection
- 🚫 XSS prevention with output escaping
- ✅ Server-side validation
- ✅ Role-based access control
- ✅ Transaction support for data integrity
- ✅ Overlapping reservation prevention

## 🛠️ Technology Stack

### Backend
- **PHP 8+** - Server-side logic
- **MySQL** - Database
- **PDO** - Database access layer

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling (with CSS Grid and Flexbox)
- **Vanilla JavaScript** - Interactivity
- **Font Awesome** - Icons

### Architecture
- MVC-inspired structure
- Separation of concerns
- Reusable components
- Single source of truth (database)

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server (or XAMPP/WAMP/Laragon)
- Web browser (Chrome, Firefox, Safari, Edge)

## 🚀 Installation

### Step 1: Clone/Download Project
```bash
# Place the project in your web server directory
# For XAMPP: C:\xampp\htdocs\
# For WAMP: C:\wamp64\www\
# For Laragon: C:\laragon\www\
```

### Step 2: Create Database
1. Open phpMyAdmin or MySQL command line
2. Create a new database:
```sql
CREATE DATABASE car_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Import Database Schema
1. Navigate to the `database` folder
2. Import `schema.sql` first:
   - In phpMyAdmin: Select car_platform database → Import → Choose schema.sql
   - Or via command line: `mysql -u root -p car_platform < database/schema.sql`

### Step 4: Import Seed Data
1. Import `seed.sql` to populate with demo data:
   - In phpMyAdmin: Select car_platform database → Import → Choose seed.sql
   - Or via command line: `mysql -u root -p car_platform < database/seed.sql`

### Step 5: Configure Database Connection
1. Open `config/config.php`
2. Update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_platform');
define('DB_USER', 'root');        // Change if needed
define('DB_PASS', '');            // Change if needed
```

3. Update APP_URL if not using localhost:
```php
define('APP_URL', 'http://localhost/auto-market');
```

### Step 6: Set Permissions
Ensure the `uploads` directory is writable:
```bash
chmod -R 755 uploads/  # Linux/Mac
# On Windows, ensure the folder has write permissions
```

### Step 7: Start Web Server
- **XAMPP**: Start Apache and MySQL modules
- **WAMP**: Start all services
- **Laragon**: Click "Start All"
- **Built-in PHP server**: `php -S localhost:8000`

### Step 8: Access Application
Open your browser and navigate to:
```
http://localhost/auto-market/
```
Or if using built-in PHP server:
```
http://localhost:8000/
```

## 👤 Demo Accounts

### Admin Account
- **Email:** admin@carplatform.com
- **Password:** Password123!

### User Accounts
- **Email:** john@example.com
- **Password:** Password123!

- **Email:** sarah@example.com
- **Password:** Password123!

## 📁 Project Structure

```
auto-market/
├── admin/                  # Admin panel pages
│   └── index.php          # Admin dashboard
├── api/                   # API endpoints (optional)
├── assets/
│   ├── css/
│   │   ├── style.css      # Main stylesheet
│   │   └── admin.css      # Admin styles
│   ├── js/
│   │   └── main.js        # JavaScript functionality
│   └── images/            # Static images
├── config/
│   ├── config.php         # Application configuration
│   └── database.php       # Database connection
├── database/
│   ├── schema.sql         # Database structure
│   └── seed.sql           # Demo data
├── includes/
│   ├── header.php         # Page header
│   ├── footer.php         # Page footer
│   ├── navbar.php         # Navigation bar
│   ├── auth.php           # Authentication functions
│   ├── admin-auth.php     # Admin authentication
│   ├── functions.php      # Helper functions
│   └── csrf.php           # CSRF protection
├── uploads/
│   └── cars/              # Uploaded car images
├── index.php              # Homepage
├── cars.php               # Car listing
├── car-details.php        # Car details
├── reservation.php        # Make reservation
├── purchase.php           # Purchase car
├── payment.php            # Payment processing
├── reservation-success.php
├── purchase-success.php
├── login.php
├── signup.php
├── logout.php
├── dashboard.php          # User dashboard
├── my-reservations.php
├── my-reviews.php
├── add-review.php
├── profile.php
├── brands.php
├── about.php
├── contact.php
└── README.md
```

## 🗄️ Database Structure

### Tables
- **users** - User accounts (customers and admins)
- **brands** - Car manufacturers
- **cars** - Vehicle inventory
- **car_images** - Multiple images per car
- **reservations** - Rental bookings
- **purchases** - Car purchases
- **payments** - Payment transactions
- **reviews** - User reviews and ratings

### Key Relationships
- One brand has many cars
- One car has many images
- One user has many reservations/purchases/reviews
- One car has many reservations/reviews
- One reservation/purchase has one payment

## 🔒 Security Features

1. **SQL Injection Prevention**: All queries use PDO prepared statements
2. **Password Security**: Passwords hashed with `password_hash()` (bcrypt)
3. **CSRF Protection**: Token-based CSRF protection on all forms
4. **XSS Prevention**: Output escaping with `htmlspecialchars()`
5. **Authentication**: Session-based authentication with regeneration
6. **Authorization**: Role-based access control (user/admin)
7. **Input Validation**: Server-side validation for all inputs
8. **File Upload Security**: MIME type and extension validation
9. **Session Security**: HTTP-only cookies, secure session handling

## 💳 Fake Payment System

The platform uses a simulated PayPal payment system:
- No real payment APIs are integrated
- No real money is processed
- Transaction references are generated locally
- All payments are marked as "fake_paypal"
- Clear warnings displayed throughout the checkout process

**Format:** `FAKE-PAYPAL-YYYYMMDD-XXXXXX`

## 🎨 UI/UX Features

- Clean, modern design
- Responsive layouts (mobile, tablet, desktop)
- Card-based components
- Star rating system
- Image galleries
- Status badges
- Empty states
- Loading states
- Flash messages
- Modal dialogs
- Form validation feedback

## 🔍 Testing Checklist

- [x] User registration and login
- [x] Car browsing with filters
- [x] Car detail viewing
- [x] Reservation creation with date validation
- [x] Overlapping reservation prevention
- [x] Payment processing
- [x] Reservation cancellation
- [x] Review submission
- [x] Profile management
- [x] Admin dashboard access
- [x] Admin car/brand management
- [x] CSRF token validation
- [x] SQL injection prevention
- [x] Responsive design

## 🐛 Known Limitations

1. **Images**: Seed data references placeholder image paths. Upload real images through admin panel.
2. **Email**: Contact form doesn't send real emails (demo only)
3. **Payment**: No real payment gateway integration
4. **Notifications**: No email/SMS notifications implemented
5. **Search**: Basic search implementation (can be enhanced with full-text search)

## 🚀 Future Enhancements

- Email notifications for reservations
- Real payment gateway integration (Stripe/PayPal)
- Advanced search with filters
- Car comparison feature
- Wishlist/favorites
- Multi-language support
- PDF invoice generation
- SMS notifications
- Google Maps integration
- Calendar view for availability
- Advanced admin analytics with charts

## 📝 Business Rules

1. Users must be logged in to rent or purchase
2. Cars marked as "sold" cannot be rented or purchased
3. Cars cannot have overlapping reservations
4. Reservation start date must be in the future
5. End date must be after start date
6. Minimum rental period: 1 day
7. Payment is calculated server-side (never trust client calculations)
8. Cancelled reservations remain in database for history
9. Users can only cancel pending/confirmed reservations
10. Users can review each car only once

## 🤝 Support

This is an educational project. For questions or issues:
1. Check the code comments
2. Review the database schema
3. Verify configuration settings
4. Check PHP error logs
5. Ensure database is properly imported

## 📄 License

This is a semester project created for educational purposes.

## 👨💻 Development

**Developed as a university semester project to demonstrate:**
- Full-stack web development
- Database design and relationships
- User authentication and authorization
- Secure coding practices
- Payment system integration (simulated)
- Responsive web design
- CRUD operations
- Business logic implementation

**Technologies chosen for simplicity and educational value:**
- No complex frameworks
- Vanilla JavaScript (no React/Vue)
- Simple but secure architecture
- Clear code structure
- Extensive comments
- Real-world features

---

## 🎓 Academic Note

This project demonstrates understanding of:
- Web application architecture
- Database design and normalization
- Security best practices
- User experience design
- Business logic implementation
- Error handling
- Data validation
- Transaction management

**Date:** August 2026  
**Version:** 1.0  
**Status:** Demo/Educational Project

---

**⚠️ IMPORTANT DISCLAIMER**

This is a demonstration project for educational purposes. All payment processing is simulated. No real financial transactions occur. Do not use real payment credentials or sensitive information.
