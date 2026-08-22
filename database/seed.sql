-- Car Platform Seed Data
USE car_platform;

-- Insert demo users
-- Password for all demo users: Password123!
-- Previous seed used Laravel's sample hash for "password", which blocked demo logins.
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin User', 'admin@carplatform.com', '$2y$12$Pcef6aCIBVpsB8E9tevF9eQe5Ybd2HJI3QkAoV1MButgMhcmVs4RG', '+1234567890', 'admin'),
('John Smith', 'john@example.com', '$2y$12$Pcef6aCIBVpsB8E9tevF9eQe5Ybd2HJI3QkAoV1MButgMhcmVs4RG', '+1234567891', 'user'),
('Sarah Johnson', 'sarah@example.com', '$2y$12$Pcef6aCIBVpsB8E9tevF9eQe5Ybd2HJI3QkAoV1MButgMhcmVs4RG', '+1234567892', 'user'),
('Michael Brown', 'michael@example.com', '$2y$12$Pcef6aCIBVpsB8E9tevF9eQe5Ybd2HJI3QkAoV1MButgMhcmVs4RG', '+1234567893', 'user'),
('Emily Davis', 'emily@example.com', '$2y$12$Pcef6aCIBVpsB8E9tevF9eQe5Ybd2HJI3QkAoV1MButgMhcmVs4RG', '+1234567894', 'user');

-- Insert brands
INSERT INTO brands (name, logo, description) VALUES
('Toyota', 'toyota-logo.png', 'World-renowned Japanese automaker known for reliability and innovation'),
('BMW', 'bmw-logo.png', 'German luxury vehicle manufacturer offering performance and elegance'),
('Mercedes-Benz', 'mercedes-logo.png', 'Premium German automotive brand synonymous with luxury'),
('Audi', 'audi-logo.png', 'German automobile manufacturer of luxury vehicles'),
('Honda', 'honda-logo.png', 'Japanese manufacturer known for reliability and fuel efficiency'),
('Ford', 'ford-logo.png', 'American automotive giant with diverse vehicle lineup'),
('Hyundai', 'hyundai-logo.png', 'South Korean manufacturer offering value and warranty'),
('Kia', 'kia-logo.png', 'Korean automaker with stylish and affordable vehicles'),
('Nissan', 'nissan-logo.png', 'Japanese automotive manufacturer with innovative technology'),
('Volkswagen', 'volkswagen-logo.png', 'German automaker with versatile vehicle range'),
('Tesla', 'tesla-logo.png', 'American electric vehicle and clean energy company'),
('Chevrolet', 'chevrolet-logo.png', 'American automobile division of General Motors');

-- Insert cars
INSERT INTO cars (brand_id, model, year, `condition`, category, price, rental_price_per_day, mileage, transmission, fuel_type, engine, color, seats, description, status, featured) VALUES
-- Toyota vehicles
(1, 'Camry', 2023, 'new', 'sedan', 28500.00, 65.00, 0, 'automatic', 'petrol', '2.5L 4-Cylinder', 'Silver', 5, 'The Toyota Camry is a reliable and comfortable sedan perfect for daily commuting and long trips. Features advanced safety systems and excellent fuel economy.', 'available', true),
(1, 'RAV4', 2023, 'new', 'suv', 32000.00, 75.00, 0, 'automatic', 'hybrid', '2.5L Hybrid', 'Blue', 5, 'Popular compact SUV with hybrid efficiency. Spacious interior, AWD capability, and modern technology features.', 'available', true),
(1, 'Corolla', 2022, 'used', 'sedan', 22000.00, 55.00, 15000, 'automatic', 'petrol', '2.0L 4-Cylinder', 'White', 5, 'Pre-owned Toyota Corolla in excellent condition. Known for legendary reliability and low maintenance costs.', 'available', false),
(1, 'Highlander', 2023, 'new', 'suv', 42000.00, 90.00, 0, 'automatic', 'hybrid', '3.5L V6 Hybrid', 'Black', 7, 'Three-row family SUV with hybrid powertrain. Perfect for families needing space and efficiency.', 'available', false),

-- BMW vehicles
(2, '3 Series', 2023, 'new', 'sedan', 45000.00, 120.00, 0, 'automatic', 'petrol', '2.0L Turbo', 'Black', 5, 'Luxury sports sedan with elegant design and thrilling performance. Premium interior with latest technology.', 'available', true),
(2, 'X5', 2022, 'used', 'suv', 52000.00, 140.00, 22000, 'automatic', 'diesel', '3.0L Turbo Diesel', 'Gray', 5, 'Pre-owned luxury SUV with powerful engine and spacious cabin. Loaded with premium features.', 'available', true),
(2, 'X3', 2023, 'new', 'suv', 48000.00, 125.00, 0, 'automatic', 'petrol', '2.0L Turbo', 'White', 5, 'Compact luxury SUV combining sportiness with practicality. Advanced driver assistance systems.', 'available', false),

-- Mercedes-Benz vehicles
(3, 'C-Class', 2023, 'new', 'sedan', 47000.00, 130.00, 0, 'automatic', 'petrol', '2.0L Turbo', 'Silver', 5, 'Elegant luxury sedan with cutting-edge technology and refined performance. Premium comfort and style.', 'available', true),
(3, 'GLC', 2023, 'new', 'suv', 55000.00, 145.00, 0, 'automatic', 'petrol', '2.0L Turbo', 'Black', 5, 'Luxury compact SUV with sophisticated design and advanced features. Perfect blend of comfort and capability.', 'available', false),
(3, 'E-Class', 2022, 'used', 'sedan', 48000.00, 135.00, 18000, 'automatic', 'diesel', '2.0L Turbo Diesel', 'Blue', 5, 'Pre-owned executive sedan in pristine condition. Luxurious interior with premium materials.', 'available', false),

-- Audi vehicles
(4, 'A4', 2023, 'new', 'sedan', 44000.00, 115.00, 0, 'automatic', 'petrol', '2.0L Turbo', 'White', 5, 'Premium compact sedan with Quattro AWD. Sophisticated design and advanced infotainment system.', 'available', true),
(4, 'Q5', 2023, 'new', 'suv', 50000.00, 135.00, 0, 'automatic', 'petrol', '2.0L Turbo', 'Gray', 5, 'Versatile luxury SUV with spacious interior and cutting-edge technology. Smooth and refined driving experience.', 'available', false),
(4, 'A6', 2022, 'used', 'sedan', 46000.00, 125.00, 20000, 'automatic', 'diesel', '3.0L Turbo Diesel', 'Black', 5, 'Pre-owned executive sedan with powerful performance and luxurious amenities.', 'available', false),

-- Honda vehicles
(5, 'Civic', 2023, 'new', 'sedan', 26000.00, 60.00, 0, 'automatic', 'petrol', '2.0L 4-Cylinder', 'Red', 5, 'Sporty and efficient compact sedan. Modern design with excellent fuel economy and reliability.', 'available', true),
(5, 'CR-V', 2023, 'new', 'suv', 32000.00, 75.00, 0, 'automatic', 'petrol', '1.5L Turbo', 'Blue', 5, 'Popular compact SUV with spacious interior and versatile cargo space. Advanced safety features.', 'available', false),
(5, 'Accord', 2022, 'used', 'sedan', 28000.00, 65.00, 12000, 'automatic', 'hybrid', '2.0L Hybrid', 'Silver', 5, 'Pre-owned midsize sedan with hybrid efficiency. Comfortable ride and premium features.', 'available', false),

-- Ford vehicles
(6, 'Mustang', 2023, 'new', 'coupe', 38000.00, 110.00, 0, 'manual', 'petrol', '5.0L V8', 'Red', 4, 'Iconic American muscle car with powerful V8 engine. Thrilling performance and aggressive styling.', 'available', true),
(6, 'Explorer', 2023, 'new', 'suv', 40000.00, 95.00, 0, 'automatic', 'petrol', '3.0L V6', 'Black', 7, 'Full-size SUV with three rows of seating. Perfect for families needing space and capability.', 'available', false),
(6, 'F-150', 2022, 'used', 'pickup', 42000.00, 100.00, 25000, 'automatic', 'petrol', '5.0L V8', 'Blue', 5, 'Pre-owned pickup truck with powerful performance. Great for work and recreation.', 'available', false),

-- Hyundai vehicles
(7, 'Tucson', 2023, 'new', 'suv', 29000.00, 70.00, 0, 'automatic', 'petrol', '2.5L 4-Cylinder', 'White', 5, 'Modern compact SUV with bold design and generous warranty. Feature-packed at competitive price.', 'available', false),
(7, 'Elantra', 2023, 'new', 'sedan', 24000.00, 55.00, 0, 'automatic', 'petrol', '2.0L 4-Cylinder', 'Gray', 5, 'Stylish compact sedan with impressive fuel economy. Comfortable and well-equipped.', 'available', false),

-- Kia vehicles
(8, 'Sportage', 2023, 'new', 'suv', 30000.00, 72.00, 0, 'automatic', 'petrol', '2.4L 4-Cylinder', 'Silver', 5, 'Attractive compact SUV with modern technology and excellent value. Spacious and practical.', 'available', false),
(8, 'K5', 2023, 'new', 'sedan', 27000.00, 62.00, 0, 'automatic', 'petrol', '1.6L Turbo', 'Red', 5, 'Midsize sedan with sporty design and turbocharged performance. Premium features at affordable price.', 'available', false),

-- Nissan vehicles
(9, 'Altima', 2023, 'new', 'sedan', 27500.00, 63.00, 0, 'automatic', 'petrol', '2.5L 4-Cylinder', 'Blue', 5, 'Comfortable midsize sedan with advanced safety technology. Smooth ride and good fuel economy.', 'available', false),
(9, 'Rogue', 2023, 'new', 'suv', 31000.00, 73.00, 0, 'automatic', 'petrol', '2.5L 4-Cylinder', 'Black', 5, 'Popular compact SUV with spacious interior and family-friendly features.', 'available', false),

-- Volkswagen vehicles
(10, 'Golf', 2023, 'new', 'hatchback', 25000.00, 58.00, 0, 'automatic', 'petrol', '1.4L Turbo', 'White', 5, 'Versatile hatchback with European styling and fun driving dynamics. Practical and efficient.', 'available', false),
(10, 'Tiguan', 2022, 'used', 'suv', 29000.00, 70.00, 16000, 'automatic', 'petrol', '2.0L Turbo', 'Gray', 5, 'Pre-owned compact SUV with refined interior and smooth ride. Great all-around family vehicle.', 'available', false),

-- Tesla vehicles
(11, 'Model 3', 2023, 'new', 'electric', 45000.00, 125.00, 0, 'automatic', 'electric', 'Electric Motor', 'White', 5, 'All-electric sedan with impressive range and cutting-edge technology. Zero emissions driving.', 'available', true),
(11, 'Model Y', 2023, 'new', 'electric', 52000.00, 140.00, 0, 'automatic', 'electric', 'Dual Motor', 'Black', 5, 'Electric crossover SUV with versatility and performance. Advanced autopilot features.', 'available', false),

-- Chevrolet vehicles
(12, 'Malibu', 2023, 'new', 'sedan', 26500.00, 60.00, 0, 'automatic', 'petrol', '1.5L Turbo', 'Silver', 5, 'Comfortable midsize sedan with spacious interior. Good value with modern features.', 'available', false),
(12, 'Equinox', 2023, 'new', 'suv', 30000.00, 72.00, 0, 'automatic', 'petrol', '1.5L Turbo', 'Blue', 5, 'Compact SUV with efficient engine and comfortable ride. Family-friendly features.', 'available', false);

-- Insert car images (placeholder paths - in production these would be actual uploaded images)
INSERT INTO car_images (car_id, image_path, is_primary) VALUES
-- Toyota Camry
(1, 'uploads/cars/camry-1.jpg', true),
(1, 'uploads/cars/camry-2.jpg', false),
(1, 'uploads/cars/camry-3.jpg', false),
-- Toyota RAV4
(2, 'uploads/cars/rav4-1.jpg', true),
(2, 'uploads/cars/rav4-2.jpg', false),
(2, 'uploads/cars/rav4-3.jpg', false),
-- Toyota Corolla
(3, 'uploads/cars/corolla-1.jpg', true),
(3, 'uploads/cars/corolla-2.jpg', false),
-- Toyota Highlander
(4, 'uploads/cars/highlander-1.jpg', true),
(4, 'uploads/cars/highlander-2.jpg', false),
-- BMW 3 Series
(5, 'uploads/cars/bmw-3series-1.jpg', true),
(5, 'uploads/cars/bmw-3series-2.jpg', false),
(5, 'uploads/cars/bmw-3series-3.jpg', false),
-- BMW X5
(6, 'uploads/cars/bmw-x5-1.jpg', true),
(6, 'uploads/cars/bmw-x5-2.jpg', false),
-- BMW X3
(7, 'uploads/cars/bmw-x3-1.jpg', true),
(7, 'uploads/cars/bmw-x3-2.jpg', false),
-- Mercedes C-Class
(8, 'uploads/cars/mercedes-c-1.jpg', true),
(8, 'uploads/cars/mercedes-c-2.jpg', false),
(8, 'uploads/cars/mercedes-c-3.jpg', false),
-- Mercedes GLC
(9, 'uploads/cars/mercedes-glc-1.jpg', true),
(9, 'uploads/cars/mercedes-glc-2.jpg', false),
-- Mercedes E-Class
(10, 'uploads/cars/mercedes-e-1.jpg', true),
(10, 'uploads/cars/mercedes-e-2.jpg', false),
-- Audi A4
(11, 'uploads/cars/audi-a4-1.jpg', true),
(11, 'uploads/cars/audi-a4-2.jpg', false),
-- Audi Q5
(12, 'uploads/cars/audi-q5-1.jpg', true),
(12, 'uploads/cars/audi-q5-2.jpg', false),
-- Audi A6
(13, 'uploads/cars/audi-a6-1.jpg', true),
-- Honda Civic
(14, 'uploads/cars/civic-1.jpg', true),
(14, 'uploads/cars/civic-2.jpg', false),
-- Honda CR-V
(15, 'uploads/cars/crv-1.jpg', true),
(15, 'uploads/cars/crv-2.jpg', false),
-- Honda Accord
(16, 'uploads/cars/accord-1.jpg', true),
-- Ford Mustang
(17, 'uploads/cars/mustang-1.jpg', true),
(17, 'uploads/cars/mustang-2.jpg', false),
(17, 'uploads/cars/mustang-3.jpg', false),
-- Ford Explorer
(18, 'uploads/cars/explorer-1.jpg', true),
(18, 'uploads/cars/explorer-2.jpg', false),
-- Ford F-150
(19, 'uploads/cars/f150-1.jpg', true),
-- Hyundai Tucson
(20, 'uploads/cars/tucson-1.jpg', true),
-- Hyundai Elantra
(21, 'uploads/cars/elantra-1.jpg', true),
-- Kia Sportage
(22, 'uploads/cars/sportage-1.jpg', true),
-- Kia K5
(23, 'uploads/cars/k5-1.jpg', true),
-- Nissan Altima
(24, 'uploads/cars/altima-1.jpg', true),
-- Nissan Rogue
(25, 'uploads/cars/rogue-1.jpg', true),
-- VW Golf
(26, 'uploads/cars/golf-1.jpg', true),
-- VW Tiguan
(27, 'uploads/cars/tiguan-1.jpg', true),
-- Tesla Model 3
(28, 'uploads/cars/model3-1.jpg', true),
(28, 'uploads/cars/model3-2.jpg', false),
-- Tesla Model Y
(29, 'uploads/cars/modely-1.jpg', true),
-- Chevrolet Malibu
(30, 'uploads/cars/malibu-1.jpg', true),
-- Chevrolet Equinox
(31, 'uploads/cars/equinox-1.jpg', true);

-- Insert sample reservations
INSERT INTO reservations (user_id, car_id, start_date, end_date, number_of_days, rental_price, total_amount, status) VALUES
(2, 1, '2026-08-15', '2026-08-20', 5, 65.00, 325.00, 'completed'),
(2, 5, '2026-07-10', '2026-07-15', 5, 120.00, 600.00, 'completed'),
(3, 2, '2026-08-01', '2026-08-07', 6, 75.00, 450.00, 'completed'),
(3, 14, '2026-08-22', '2026-08-25', 3, 60.00, 180.00, 'confirmed'),
(4, 8, '2026-08-18', '2026-08-22', 4, 130.00, 520.00, 'confirmed'),
(5, 17, '2026-08-25', '2026-08-28', 3, 110.00, 330.00, 'pending'),
(2, 11, '2026-07-01', '2026-07-05', 4, 115.00, 460.00, 'cancelled');

-- Insert sample payments
INSERT INTO payments (reservation_id, purchase_id, user_id, amount, payment_method, transaction_reference, status) VALUES
(1, NULL, 2, 325.00, 'fake_paypal', 'FAKE-PAYPAL-20260815-A1B2C3', 'completed'),
(2, NULL, 2, 600.00, 'fake_paypal', 'FAKE-PAYPAL-20260710-D4E5F6', 'completed'),
(3, NULL, 3, 450.00, 'fake_paypal', 'FAKE-PAYPAL-20260801-G7H8I9', 'completed'),
(4, NULL, 3, 180.00, 'fake_paypal', 'FAKE-PAYPAL-20260822-J1K2L3', 'completed'),
(5, NULL, 4, 520.00, 'fake_paypal', 'FAKE-PAYPAL-20260818-M4N5O6', 'completed'),
(7, NULL, 2, 460.00, 'fake_paypal', 'FAKE-PAYPAL-20260701-P7Q8R9', 'refunded');

-- Insert sample reviews
INSERT INTO reviews (user_id, car_id, rating, comment) VALUES
(2, 1, 5, 'Excellent car! Very comfortable for long drives and great fuel economy. Highly recommend.'),
(2, 5, 5, 'Amazing luxury sedan. The performance is outstanding and the interior is top-notch.'),
(3, 2, 4, 'Great SUV for family trips. Spacious and the hybrid system works perfectly. Only minor issue was the infotainment learning curve.'),
(3, 14, 5, 'Perfect city car. Fun to drive and very economical. Will definitely rent again!'),
(4, 8, 5, 'Luxurious and smooth. The Mercedes C-Class exceeded all expectations. Worth every penny.'),
(2, 11, 4, 'Very nice car overall. The Quattro AWD is great in all conditions. Interior could use a bit more cargo space.');

-- Insert a sample purchase
INSERT INTO purchases (user_id, car_id, amount, status, payment_id) VALUES
(4, 3, 22000.00, 'completed', NULL);

-- Update the purchase with payment
INSERT INTO payments (reservation_id, purchase_id, user_id, amount, payment_method, transaction_reference, status) VALUES
(NULL, 1, 4, 22000.00, 'fake_paypal', 'FAKE-PAYPAL-20260805-S1T2U3', 'completed');

UPDATE purchases SET payment_id = 7 WHERE id = 1;

-- Public car status is derived from reservations and purchases
UPDATE cars SET status = 'sold' WHERE id = 3;
UPDATE cars SET status = 'rented' WHERE id IN (8, 14);
UPDATE cars SET status = 'reserved' WHERE id = 17;
