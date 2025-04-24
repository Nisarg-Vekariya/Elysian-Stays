CREATE DATABASE `Elysian_Stays`;

-- user 
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `city` VARCHAR(50),
    `country` VARCHAR(50),
    `profile_pic` VARCHAR(255) DEFAULT NULL,
    `token` VARCHAR(255) NOT NULL,  -- Used for email verification & password reset
    `role` ENUM('user', 'admin', 'hotel') DEFAULT 'user',  -- User roles
    `status` ENUM('active', 'inactive') DEFAULT 'inactive', -- Active means verified, Inactive means unverified
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users MODIFY token VARCHAR(100) NULL;

ALTER TABLE users ADD COLUMN reset_token VARCHAR(100);
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME;

CREATE TABLE sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    coupon_code VARCHAR(50) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- for testing sliders offers
INSERT INTO sliders (title, coupon_code, is_active) VALUES
('Special Offer: 20% Off on Beachside Resorts!', 'BEACH20', 1),
('Early Bird Discount: Save 15% on Mountain Adventures!', 'MOUNTAIN15', 1),
('Weekend Getaway: 10% Off City Tours!', 'CITY10', 1);

ALTER TABLE sliders
ADD COLUMN discount INT NOT NULL DEFAULT 0,
ADD CONSTRAINT check_discount CHECK (discount >= 0 AND discount <= 100);




-- Hotel Details Table
CREATE TABLE `hotels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `about_title` varchar(255) NOT NULL,
  `about_description1` text NOT NULL,
  `about_description2` text NOT NULL,
  `about_image` varchar(255) NOT NULL,
  `rooms_title` varchar(255) NOT NULL,
  `rooms_description` text NOT NULL,
  `amenities_title` varchar(255) NOT NULL,
  `amenities_description` text NOT NULL,
  `gallery_title` varchar(255) NOT NULL,
  `gallery_description` text NOT NULL,
  `map_embed_url` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hotel Rooms Table
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','booked') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add capacity column to rooms table if it doesn't exist
ALTER TABLE rooms 
ADD COLUMN capacity INT NOT NULL DEFAULT 2 
AFTER price;

-- Update the capacity values for existing rooms
UPDATE rooms SET capacity = 2 WHERE capacity = 0;

-- Update capacity for different room types
UPDATE rooms SET capacity = 1 WHERE name LIKE '%single%' OR name LIKE '%standard%';
UPDATE rooms SET capacity = 2 WHERE name LIKE '%double%' OR name LIKE '%queen%' OR name LIKE '%king%';
UPDATE rooms SET capacity = 3 WHERE name LIKE '%triple%' OR name LIKE '%family%';
UPDATE rooms SET capacity = 4 WHERE name LIKE '%quad%' OR name LIKE '%suite%' OR name LIKE '%villa%';


-- Hotel Amenities Table
                                                                                                    -- CREATE TABLE `amenities` (
                                                                                                    --   `id` int(11) NOT NULL AUTO_INCREMENT,
                                                                                                    --   `hotel_id` int(11) NOT NULL,
                                                                                                    --   `name` varchar(255) NOT NULL,
                                                                                                    --   `description` text NOT NULL,
                                                                                                    --   `icon` varchar(255) NOT NULL,
                                                                                                    --   PRIMARY KEY (`id`),
                                                                                                    --   KEY `hotel_id` (`hotel_id`),
                                                                                                    --   CONSTRAINT `amenities_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
                                                                                                    -- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- First, create the amenities table
CREATE TABLE `amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) DEFAULT NULL,  -- Changed to allow NULL initially
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `amenities_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert the default amenities (these will be templates, so hotel_id is NULL)
INSERT INTO `amenities` (`hotel_id`, `name`, `description`, `icon`) VALUES
(NULL, 'Swimming Pool', 'Relax and unwind in our temperature-controlled swimming pool.', 'fas fa-swimming-pool'),
(NULL, 'Fine Dining', 'Enjoy gourmet dishes curated by world-class chefs.', 'fas fa-utensils'),
(NULL, 'Spa & Wellness', 'Pamper yourself with our rejuvenating spa treatments.', 'fas fa-spa'),
(NULL, 'Fitness Center', 'Stay fit with our state-of-the-art gym facilities.', 'fas fa-dumbbell'),
(NULL, 'Free Wi-Fi', 'Enjoy high-speed internet access throughout the hotel.', 'fas fa-wifi');

-- Create a junction table for hotel amenities
CREATE TABLE `hotel_amenities` (
  `hotel_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL,
  PRIMARY KEY (`hotel_id`, `amenity_id`),
  KEY `amenity_id` (`amenity_id`),
  CONSTRAINT `hotel_amenities_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hotel_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- First, let's see if we have any data in hotel_amenities
SELECT COUNT(*) FROM hotel_amenities;

-- If no results or very few results, we need to populate it with existing amenities
-- This will match existing amenities with hotels
INSERT INTO hotel_amenities (hotel_id, amenity_id)
SELECT h.id, a.id 
FROM hotels h, amenities a 
WHERE a.hotel_id = h.id
ON DUPLICATE KEY UPDATE hotel_id = h.id;




-- Gallery Images Table
CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_text` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Guest Reviews Table
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `review_date` varchar(50) NOT NULL,
  `review_text` text NOT NULL,
  `rating` int(1) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact Information Table
CREATE TABLE `contact_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `contact_info_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Data for Waldorf Astoria Beverly Hills
INSERT INTO `hotels` (`name`, `tagline`, `about_title`, `about_description1`, `about_description2`, `about_image`, `rooms_title`, `rooms_description`, `amenities_title`, `amenities_description`, `gallery_title`, `gallery_description`, `map_embed_url`) VALUES
('Waldorf Astoria Beverly Hills', 'Experience luxury and comfort like never before', 'Waldorf Astoria Beverly Hills', 'Nestled in the heart of the city, Waldorf Astoria Beverly Hills Hotel offers the perfect blend of luxury, comfort, and convenience. With world-class amenities, breathtaking views, and impeccable service, we ensure an unforgettable stay for our guests.', 'Whether you\'re here for business or leisure, our exquisite rooms, fine dining experiences, and state-of-the-art facilities promise to make your visit truly exceptional.', 'Images/hotel1_2.jpg', 'Rooms and Suites', 'Discover the perfect room to match your style and needs. From cozy standard rooms to luxurious suites, each space is designed to provide maximum comfort and elegance.', 'Hotel Amenities', 'Waldorf Astoria Beverly Hills Hotel offers a range of premium amenities to make your stay comfortable and memorable.', 'Photo Gallery', 'Explore the beauty and elegance of LuxeStay Hotel through our gallery.', 'https://www.google.com/maps/embed?pb=YOUR_MAP_EMBED_URL');

-- Insert Sample Rooms Data
INSERT INTO `rooms` (`hotel_id`, `name`, `description`, `image`, `price`, `status`) VALUES
(1, 'Deluxe Corner Junior Suite – Two Queen Beds', 'Our corner two queen suite is located on the penthouse floor with unparalleled panoramic windows and 146 square-foot private terraces.', 'Images/hotel_room1.jpg', 599.00, 'available'),
(1, 'Superior Corner Junior Suite With Terrace', 'These king bed suites provide you with more than enough space to work and to play. You\'ll find these suites with views of sprawling cityscapes and stunning sunsets through enormous panoramic windows.', 'Images/hotel_room2.jpg', 699.00, 'available'),
(1, 'Deluxe Corner Junior Suite', 'These suites are a step up from our Superior Corner Junior Suites, with unobstructed views of sprawling cityscapes and stunning sunsets.', 'Images/hotel_room3.jpg', 549.00, 'available');

-- Insert Sample Amenities Data
INSERT INTO `amenities` (`hotel_id`, `name`, `description`, `icon`) VALUES
(1, 'Swimming Pool', 'Relax and unwind in our temperature-controlled swimming pool.', 'fas fa-swimming-pool'),
(1, 'Fine Dining', 'Enjoy gourmet dishes curated by world-class chefs.', 'fas fa-utensils'),
(1, 'Spa & Wellness', 'Pamper yourself with our rejuvenating spa treatments.', 'fas fa-spa'),
(1, 'Fitness Center', 'Stay fit with our state-of-the-art gym facilities.', 'fas fa-dumbbell'),
(1, 'Free Wi-Fi', 'Enjoy high-speed internet access throughout the hotel.', 'fas fa-wifi');

-- Insert Sample Gallery Images
INSERT INTO `gallery_images` (`hotel_id`, `image`, `alt_text`) VALUES
(1, 'Images/hotel1_gallery1.jpg', 'Luxury Suite'),
(1, 'Images/hotel1_gallery2.jpg', 'Swimming Pool'),
(1, 'Images/hotel1_gallery3.jpg', 'Fine Dining'),
(1, 'Images/hotel1_gallery4.jpg', 'Spa and Wellness'),
(1, 'Images/hotel1_gallery5.jpeg', 'Fitness Center'),
(1, 'Images/hotel1_gallery6.jpg', 'Scenic View'),
(1, 'Images/hotel1_gallery7.jpg', 'Scenic View'),
(1, 'Images/hotel1_gallery8.jpg', 'Scenic View');

-- Insert Sample Reviews
INSERT INTO `reviews` (`hotel_id`, `author_name`, `review_date`, `review_text`, `rating`, `status`) VALUES
(1, 'Sarah M.', 'March 2025', 'An amazing stay! The staff was incredibly friendly and the rooms were spotless.', 5, 'active'),
(1, 'John D.', 'February 2025', 'Beautiful hotel with excellent amenities. The view was breathtaking!', 5, 'active'),
(1, 'Emily R.', 'January 2025', 'Perfect location and wonderful service. Will definitely return!', 5, 'active');

-- Insert Sample Contact Information
INSERT INTO `contact_info` (`hotel_id`, `email`, `phone`, `address`) VALUES
(1, 'elysianstays@gmail.com', '+1 (800) 123-4567', '1234 Main Street, City, Country');


-- Create hotels table (if not exists)
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    address TEXT,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    check_in_time TIME,
    check_out_time TIME,
    image_path VARCHAR(255)
);

-- Create rooms table (if not exists)
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available',
    image_path VARCHAR(255),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    guest_email VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL,
    review_text TEXT,
    review_date DATE NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Insert sample hotel data
INSERT INTO hotels (
    name, 
    tagline, 
    about_title, 
    about_description1, 
    about_description2, 
    about_image,
    rooms_title,
    rooms_description,
    amenities_title,
    amenities_description,
    gallery_title,
    gallery_description,
    map_embed_url
) 
VALUES (
    'Grand Hotel Palace',
    'Luxury and Comfort in the Heart of the City',
    'About Our Hotel',
    'Experience luxury and comfort at our prestigious hotel located in the heart of the city. Featuring modern amenities and exceptional service.',
    'Our commitment to excellence ensures that every guest enjoys a memorable stay with world-class hospitality.',
    'uploads/hotel_about.jpg',
    'Our Luxurious Rooms',
    'Choose from our selection of elegantly designed rooms and suites, each crafted for your comfort.',
    'Hotel Amenities',
    'Enjoy our premium amenities including spa, fitness center, swimming pool, and fine dining restaurants.',
    'Photo Gallery',
    'Take a visual journey through our beautiful hotel and its facilities.',
    'https://www.google.com/maps/embed?pb=your-map-embed-code'
);

ALTER TABLE hotels ADD COLUMN user_id INT, ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(id);


CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    hotel VARCHAR(100),
    date_of_stay DATE,
    phone VARCHAR(15) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- index.php
-- Features table (for Why Choose Us section)
CREATE TABLE features (
    feature_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon_url VARCHAR(255) NOT NULL,
    display_order INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    special_class VARCHAR(50) DEFAULT NULL
);

-- Booking steps table
CREATE TABLE booking_steps (
    step_id INT AUTO_INCREMENT PRIMARY KEY,
    step_number INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    animation_delay VARCHAR(20) NOT NULL DEFAULT '0s',
    display_order INT NOT NULL
);

-- Insert sample features (Why Choose Us)
INSERT INTO features (title, description, icon_url, display_order, special_class) VALUES
('Best Deals', 'We offer competitive prices and exclusive discounts for the best value.', 'Images/best_deal.jpg', 1, NULL),
('Secure Booking', 'Your bookings are safe and secured with advanced encryption.', 'Images/secure.png', 2, 'unique'),
('24/7 Support', 'Our customer support is always here to assist you anytime, anywhere.', 'Images/full_day.jpg', 3, NULL),
('Wide Range of Options', 'Choose from thousands of hotels worldwide to suit your preferences.', 'Images/variety.png', 4, NULL),
('Verified Reviews', 'Read honest reviews from verified guests to make informed decisions.', 'Images/reviews.jpeg', 5, NULL),
('Loyalty Rewards', 'Earn points on every booking and redeem them for exclusive benefits.', 'Images/loyalty.png', 6, NULL);

-- Insert booking process steps
INSERT INTO booking_steps (step_number, title, description, animation_delay, display_order) VALUES
(1, 'Search', 'Start by searching for hotels based on your desired location, check-in, and check-out dates. You can filter by price, rating, and other preferences to find the perfect stay for your trip.', '0s', 1),
(2, 'Choose', 'Browse through a list of hotels that match your search criteria. You can view detailed information about each hotel, including amenities, photos, and guest reviews. Select the hotel and room that best suits your needs.', '1s', 2),
(3, 'Payment', 'Once you have selected your hotel and room, proceed to secure payment. You can pay using various methods such as credit/debit cards, online wallets, or bank transfer.', '2s', 3),
(4, 'Confirmation', 'After completing the payment, you will receive a booking confirmation email with all your stay details. Enjoy your trip, and feel free to reach out if you need assistance!', '3s', 4);





-- Table for about page content
CREATE TABLE IF NOT EXISTS about_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(50) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for facts/statistics
CREATE TABLE IF NOT EXISTS about_facts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    statistic_value VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    footnote VARCHAR(255),
    animation_class VARCHAR(50) DEFAULT 'animate__fadeIn',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for hero section
CREATE TABLE IF NOT EXISTS page_hero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    background_image VARCHAR(255),
    search_placeholder VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert hero section data
INSERT INTO page_hero (page_name, title, search_placeholder) 
VALUES ('about', 'About Us', 'Click here to search for Destinations or Hotels.');

-- Insert about content
INSERT INTO about_content (section_name, title, content, display_order) VALUES
('main_heading', 'The Unparalleled <br> Guardian of Grandeur', 'Built on a vision of grandeur, Elysian Stays conjures a panoply of superlative experiences that are envisioned to indulge and forge unforgettable memories.', 1),
('our_story', 'Our Story', 'Elysian Stays began with a vision to redefine luxury travel, offering curated stays that transcend the ordinary.\nFounded with a commitment to connecting discerning travelers with the world\'s most exquisite hotels and villas, Elysian Stays has quickly become a global gateway to unparalleled elegance.\nFrom idyllic beachfront retreats to majestic city escapes, our mission is to create a seamless experience where every stay feels like a chapter of your dream journey.\nEvery day, Elysian Stays connects travelers with opulent accommodations that celebrate the art of hospitality, ensuring memories that linger long after the journey ends.', 2);

-- Insert facts/statistics
INSERT INTO about_facts (statistic_value, description, footnote, animation_class, display_order) VALUES
('8M+', 'active listings worldwide', 'as of June 30, 2024', 'animate__fadeInLeft', 1),
('100K+', 'cities and towns with active Elysian Stays listings', 'as of December 31, 2023', 'animate__fadeInRight', 2),
('220+', 'countries and regions with Elysian Stays listings', 'as of December 31, 2023', 'animate__fadeInLeft', 3),
('2B+', 'Elysian Stays guest arrivals all-time', 'as of September 30, 2024', 'animate__fadeInRight', 4),
('5M+', 'hosts on Elysian Stays', 'as of December 31, 2023', 'animate__fadeInLeft', 5),
('$250B+', 'earned by hosts, all-time', 'as of December 31, 2023', 'animate__fadeInRight', 6),
('$14K', 'earned by the typical US host in 2023', 'as of December 31, 2023', 'animate__fadeInLeft', 7),
('$10B+', 'total taxes collected and remitted globally', 'as of December 31, 2023', 'animate__fadeInRight', 8);



-- contact us page

-- Contact page hero section
CREATE TABLE IF NOT EXISTS contact_hero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    background_image VARCHAR(255),
    search_placeholder VARCHAR(255) NOT NULL
);

-- Toll-free numbers table
CREATE TABLE IF NOT EXISTS contact_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region VARCHAR(100) NOT NULL,
    number VARCHAR(50) NOT NULL,
    display_order INT DEFAULT 0
);

-- Global assistance centers
CREATE TABLE IF NOT EXISTS assistance_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0
);

-- Registered office information
CREATE TABLE IF NOT EXISTS registered_office (
    id INT AUTO_INCREMENT PRIMARY KEY,
    address TEXT NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    map_link VARCHAR(255)
);

-- Feedback form submissions
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    hotel VARCHAR(100),
    date_of_stay DATE,
    phone VARCHAR(20) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Page content sections
CREATE TABLE IF NOT EXISTS page_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(50) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    display_order INT DEFAULT 0
);
-- Insert hero section data
INSERT INTO contact_hero (title, background_image, search_placeholder) 
VALUES ('Contact', '/images/contact-hero.jpg', 'Click here to search for Destinations or Hotels.');

-- Insert toll-free numbers
INSERT INTO contact_numbers (region, number, display_order) VALUES
('Toll-free India', '1-800-111-825', 1),
('India Network', '00800-222-6030-1125', 2),
('USA & Canada', '1-866-969-1825', 3),
('Bahrain Toll Free', '80006488', 4),
('Brazil Toll Free', '08008912207', 5),
('Egypt Toll Free', '08000000425', 6),
('United Arab Emirates (UAE) Toll Free', '800-032-0477', 7),
('Other Countries', '00-800-4-588-1-825', 8);

-- Insert assistance centers
INSERT INTO assistance_centers (city, address, phone, email, display_order) VALUES
('AHMEDABAD', '405, Tilakraj Complex, Behind Suryarath Complex, Panchwati First lane, Ahmedabad, 380006', '+91 79264 65591 / 93', 'sales.ahmedabad@elysianstays.com', 1),
('AUSTRALIA : SYDNEY', 'PO BOX 364, Lindfield, New South Wales 2070', '+61 294 403 613', 'sales.sydney@elysianstays.com', 2),
('BANGALORE', '41, Race Course Rd, Sampangirama Nagar, High Grounds, Bengaluru, Karnataka 560001', '+91 80666 05660', 'sales.bengaluru@elysianstays.com', 3),
('CHENNAI', '37, Uthamar Gandhi Rd, Tirumurthy Nagar, Nungambakkam, Chennai, Tamil Nadu 600034', '+91 44660 02827', 'sales.chennai@elysianstays.com', 4),
('DENMARK: SCANDINAVIA', 'Atlantic Link, Kompagnistraede 34, 4th fl.DK-1208 Copenhagen K, Denmark', '+45 70 27 23 71', 'sales.scandinavia@elysianstays.com', 5),
('HYDERABAD', 'Rd Number 1, Mada Manzil, Banjara Hills, Hyderabad, Telangana 500034', '+91 40666 62323', 'sales.hyderabad@elysianstays.com', 6);

-- Insert registered office
INSERT INTO registered_office (address, phone, email, map_link) VALUES
('Mandlik House, Mandlik Road, Mumbai, Maharashtra 400 001 India', '+91 22-6137-1710', 'investorrelations@elysianstays.com', '#');

-- Insert page content
INSERT INTO page_content (section_name, title, content, display_order) VALUES
('contact_intro', 'Worldwide Reservation Centre', 'Elysian Stays Reservations Worldwide Centre is accessible 24/7. Toll-free contact numbers are below.', 1),
('care_section_title', 'CARE@Elysian Stays', NULL, 2),
('care_section_content', NULL, 'We take pride in crafting moments that stay with you forever. However, if there is anything we can do to make the time you spend in our care better, please do share your thoughts with us. Ensuring your contentment is at the forefront of our commitment.', 3);






-- list your place
-- Database tables for Elysian Stays Partner Program

-- Create the earnings_estimates table
CREATE TABLE IF NOT EXISTS earnings_estimates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    min_nights INT NOT NULL,
    max_nights INT NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    average_nightly_earnings DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the setup_features table
CREATE TABLE IF NOT EXISTS setup_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(100),
    display_order INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the protection_features table
CREATE TABLE IF NOT EXISTS protection_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    elysian_has BOOLEAN DEFAULT TRUE,
    competitors_have BOOLEAN DEFAULT FALSE,
    display_order INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert earnings estimate
INSERT INTO earnings_estimates (min_nights, max_nights, base_price, average_nightly_earnings) 
VALUES (1, 30, 4898, 4500);

-- Insert setup features
INSERT INTO setup_features (title, description, display_order) VALUES 
('One-to-one guidance from a Superhost', 'We''ll match you with a Superhost in your area, who''ll guide you from your first question to your first guest – by phone, video call or chat.', 1),
('An experienced guest for your first booking', 'For your first booking, you can choose to welcome an experienced guest who has at least three stays and a good track record on Elysian Stays.', 2),
('Specialised support from Elysian Stays', 'New Hosts get one-tap access to specially trained Community Support agents who can help with everything from account issues to billing support.', 3);

-- Insert protection features
INSERT INTO protection_features (title, description, elysian_has, competitors_have, display_order) VALUES 
('Guest identity verification', 'Our comprehensive verification system checks details such as name, address, government ID, and more to confirm the identity of guests who book on Elysian Stays.', TRUE, FALSE, 1),
('Reservation screening', 'Our proprietary technology analyzes hundreds of factors in each reservation to help identify and flag higher-risk bookings before they occur.', TRUE, FALSE, 2),
('$3m damage protection', 'AirCover ensures you''re covered for guests or their pets damaging your home or items like specialized valuables.', TRUE, FALSE, 3),
('Art & valuables', 'Covers your valuable items such as artwork or collectibles from accidental damage by guests.', TRUE, FALSE, 4),
('Auto & boat', 'Protection includes damages caused to autos and boats in the case of hosting-related incidents.', TRUE, FALSE, 5),
('Pet damage', 'Covers damages caused by guests'' pets during their stay at your home.', TRUE, FALSE, 6),
('Income loss', 'Ensures compensation for income loss due to guest-caused damages or other hosting interruptions.', TRUE, FALSE, 7),
('Deep cleaning', 'Provides coverage for deep cleaning services required after hosting guests.', TRUE, FALSE, 8),
('$1m USD liability insurance', 'This helps protect you in the event that a guest gets hurt or their belongings are damaged during a stay.', TRUE, FALSE, 9),
('24-hour safety line', 'If you ever feel unsafe, our priority team steps in to support increased safety protocols, any day or night.', TRUE, FALSE, 10);




-- Tos.php
CREATE TABLE IF NOT EXISTS terms_of_service (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) NOT NULL,
    section_content TEXT NOT NULL,
    display_order INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS page_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_title VARCHAR(255) NOT NULL,
    effective_date VARCHAR(100) NOT NULL,
    footer_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Insert page settings
INSERT INTO page_settings (page_title, effective_date, footer_text) VALUES 
('Terms of Service', 'January 2025', '&copy; 2025 Elysian Stays. All rights reserved. | <a href="privacy-policy.php" target="_blank">Privacy Policy</a> | <a href="ToS.php" target="_blank">Terms of Service</a> | <a href="TPS.php" target="_blank">Terms of Payments Service</a>');

-- Insert terms sections
INSERT INTO terms_of_service (section_title, section_content, display_order) VALUES 
('1. Introduction', 'Welcome to Elysian Stays! These Terms of Service govern your access to and use of our website and services. By using our platform, you agree to comply with and be bound by these terms. Please read them carefully before proceeding.', 1),
('2. Eligibility', 'To use our services, you must be at least 18 years old and capable of entering into legally binding agreements. By accessing our site, you confirm that you meet these criteria.', 2),
('3. Use of Services', 'You agree to use Elysian Stays for lawful purposes only. Unauthorized use, including but not limited to scraping, hacking, or attempting to manipulate our services, is strictly prohibited.', 3),
('4. User Accounts', 'Creating an account with Elysian Stays is optional but recommended for full access to our features. You are responsible for maintaining the confidentiality of your account credentials and ensuring that all activities conducted under your account comply with these terms.', 4),
('5. Booking Policies', 'All bookings are subject to availability and confirmation from the respective hotel or property. Please review the cancellation and refund policies specific to your booking before confirming your reservation.', 5),
('6. Intellectual Property', 'All content, trademarks, and materials on our website are the intellectual property of Elysian Stays or its licensors. Unauthorized reproduction, distribution, or use of our content is prohibited.', 6),
('7. Limitation of Liability', 'Elysian Stays is not liable for any damages or losses resulting from your use of our services, including but not limited to booking errors, cancellations, or service interruptions.', 7),
('8. Privacy', 'Your privacy is important to us. Please review our <a href="PrivacyPolicy.php">Privacy Policy</a> to understand how we collect, use, and protect your personal information.', 8),
('9. Termination', 'We reserve the right to terminate or suspend your access to our platform at our discretion if you violate these Terms of Service or engage in unlawful activities.', 9),
('10. Governing Law', 'These terms are governed by and construed in accordance with the laws of the jurisdiction in which Elysian Stays operates.', 10),
('11. Modifications', 'Elysian Stays may update these Terms of Service from time to time. You will be notified of significant changes, and your continued use of our services will constitute acceptance of the revised terms.', 11),
('12. Contact Us', 'If you have any questions or concerns about these Terms of Service, please contact us at <a href="mailto:support@elysianstays.com">support@elysianstays.com</a>.', 12);


-- TPS.php
-- Create a separate table specifically for Payment Terms
CREATE TABLE IF NOT EXISTS payment_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_title VARCHAR(255) NOT NULL,
    section_content TEXT NOT NULL,
    display_order INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create a settings table for Payment Terms page (modified to fix the error)
CREATE TABLE IF NOT EXISTS payment_page_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_title VARCHAR(255) NOT NULL DEFAULT 'Payments Terms of Service',
    effective_date VARCHAR(100) NOT NULL DEFAULT 'January 2025',
    footer_text TEXT NOT NULL,  -- Removed DEFAULT clause for TEXT column
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings for Payment Terms page (now including footer_text in the VALUES)
INSERT INTO payment_page_settings (page_title, effective_date, footer_text) 
VALUES (
    'Payments Terms of Service', 
    'January 2025', 
    '&copy; 2025 Elysian Stays. All rights reserved. | <a href="privacy-policy.php" target="_blank">Privacy Policy</a> | <a href="ToS.php" target="_blank">Terms of Service</a> | <a href="TPS.php" target="_blank">Terms of Payments Service</a>'
);

-- Insert sample payment terms sections
INSERT INTO payment_terms (section_title, section_content, display_order) VALUES 
('1. Introduction', 'Welcome to Elysian Stays! By using our payment services, you agree to these terms. Please read them carefully.', 1),
('2. Payment Methods', 'We accept a variety of payment methods including credit cards, debit cards, and other digital payment options as displayed during checkout. All payments are securely processed.', 2),
('3. Currency', 'All transactions will be processed in the currency specified at the time of booking.', 3),
('4. Charges and Fees', 'Elysian Stays reserves the right to charge additional fees for processing payments. Any such fees will be disclosed during the payment process.', 4),
('5. Refund Policy', 'Refunds, if applicable, will be processed according to our <a href="#">Refund Policy</a>. Please ensure to review the cancellation terms of your booking.', 5),
('6. Security', 'Your payment information is securely encrypted using the latest security protocols. We do not store full payment card details.', 6),
('7. Disputes', 'If you encounter any issues with a payment, please contact our support team immediately at <a href="mailto:support@elysianstays.com">support@elysianstays.com</a>.', 7),
('8. Changes to Terms', 'Elysian Stays reserves the right to update these payment terms at any time. We encourage you to review this page periodically for updates.', 8),
('9. Contact Us', 'For any questions or concerns, reach out to us at <a href="mailto:support@elysianstays.com">support@elysianstays.com</a>.', 9);



SELECT * FROM contact_hero LIMIT 1;


-- Reset existing data if needed (be careful with this in production!)
-- DELETE FROM amenities;
-- DELETE FROM rooms;
-- DELETE FROM hotels;

-- Insert sample hotels with the correct column structure including owner_id
INSERT INTO hotels (
    name, 
    tagline, 
    about_title, 
    about_description1, 
    about_description2, 
    about_image, 
    rooms_title, 
    rooms_description, 
    amenities_title, 
    amenities_description, 
    gallery_title, 
    gallery_description, 
    map_embed_url,
    owner_id
) VALUES (
    'Beachfront Resort & Spa', 
    'Experience luxury by the ocean', 
    'About Our Beachfront Resort', 
    'Experience ultimate relaxation in our 5-star resort with breathtaking ocean views.', 
    'Our resort offers the perfect blend of luxury and comfort with direct beach access.', 
    'Images/hotel1_bg.webp',
    'Luxurious Accommodations',
    'Choose from our range of elegantly designed rooms and suites, each with stunning ocean views.',
    'Resort Amenities',
    'Enjoy our premium amenities designed for your comfort and relaxation.',
    'Photo Gallery',
    'Explore our beautiful resort through captivating images.',
    'https://www.google.com/maps/embed?pb=your-map-embed-code',
    1  -- Use the appropriate user ID for the owner
), (
    'Downtown Luxury Hotel', 
    'Stay in the heart of the city', 
    'About Our Downtown Hotel', 
    'Stay in the heart of the city with easy access to all attractions and business centers.', 
    'Perfect location for both business and leisure travelers looking for luxury in the city center.', 
    'Images/hotel1_gallery1.jpg',
    'Elegant City Accommodations',
    'Our rooms combine modern design with comfort, offering a peaceful sanctuary in the bustling city.',
    'Hotel Amenities',
    'Discover a world of luxury amenities designed for your convenience.',
    'Photo Gallery',
    'Take a tour of our sophisticated urban retreat.',
    'https://www.google.com/maps/embed?pb=your-map-embed-code',
    1  -- Use the appropriate user ID for the owner
), (
    'Mountain View Lodge', 
    'A rustic retreat in nature', 
    'About Our Mountain Lodge', 
    'A rustic yet elegant retreat nestled in the mountains offering spectacular views and outdoor activities.', 
    'Escape to the mountains and immerse yourself in nature while enjoying premium accommodations.', 
    'Images/hotel1_gallery2.jpg',
    'Mountain Accommodations',
    'Our cozy rooms and cabins offer a perfect blend of rustic charm and modern comfort.',
    'Lodge Amenities',
    'Experience our mountain-inspired amenities designed for relaxation and adventure.',
    'Photo Gallery',
    'See the beauty of our mountain retreat and surrounding landscape.',
    'https://www.google.com/maps/embed?pb=your-map-embed-code',
    1  -- Use the appropriate user ID for the owner
), (
    'Urban Boutique Hotel', 
    'Contemporary design & personalized service', 
    'About Our Boutique Hotel', 
    'A stylish boutique hotel featuring contemporary design and personalized service in an urban setting.', 
    'Each detail of our hotel has been carefully curated to provide a unique and memorable experience.', 
    'Images/hotel1_gallery3.jpg',
    'Designer Accommodations',
    'Our uniquely designed rooms reflect our commitment to style, comfort, and innovation.',
    'Hotel Amenities',
    'Discover curated amenities that complement our distinctive approach to hospitality.',
    'Photo Gallery',
    'Explore the artistic design and stylish spaces of our boutique hotel.',
    'https://www.google.com/maps/embed?pb=your-map-embed-code',
    1  -- Use the appropriate user ID for the owner
), (
    'Sunset Paradise Resort', 
    'Your island escape awaits', 
    'About Our Paradise Resort', 
    'An island paradise with pristine beaches and luxurious accommodations for a perfect getaway.', 
    'Immerse yourself in tropical beauty and relax in our exclusive resort designed for the ultimate vacation.', 
    'Images/hotel1_gallery4.jpg',
    'Tropical Accommodations',
    'Our villas and bungalows offer private luxury with stunning ocean and garden views.',
    'Resort Amenities',
    'Enjoy paradise-inspired amenities that create unforgettable vacation moments.',
    'Photo Gallery',
    'See the breathtaking beauty of our island paradise and luxurious accommodations.',
    'https://www.google.com/maps/embed?pb=your-map-embed-code',
    1  -- Use the appropriate user ID for the owner
);

-- Insert rooms for Beachfront Resort & Spa (ID 1)
INSERT INTO rooms (hotel_id, name, description, image, price, status) VALUES
(5, 'Ocean View Suite', 'Spacious suite with breathtaking ocean views and a private balcony.', 'Images/hotel_room1.jpg', 399.99, 'available'),
(5, 'Deluxe Beach Villa', 'Exclusive villa steps away from the beach with a private plunge pool.', 'Images/hotel_room2.jpg', 599.99, 'available'),
(5, 'Standard Room', 'Comfortable room with all essential amenities and garden views.', 'Images/hotel_room3.jpg', 199.99, 'available');

-- Insert rooms for Downtown Luxury Hotel (ID 2)
INSERT INTO rooms (hotel_id, name, description, image, price, status) VALUES
(6, 'Executive Suite', 'Upscale suite with city skyline views, separate living area, and executive amenities.', 'Images/hotel_room2.jpg', 459.99, 'available'),
(6, 'Deluxe King Room', 'Elegant room with a king-sized bed and premium furnishings.', 'Images/hotel_room1.jpg', 259.99, 'available'),
(6, 'Family Room', 'Spacious room designed for families with two queen beds and extra space.', 'Images/hotel_room3.jpg', 359.99, 'available');

-- Insert rooms for Mountain View Lodge (ID 3)
INSERT INTO rooms (hotel_id, name, description, image, price, status) VALUES
(7, 'Mountain Cabin', 'Rustic cabin with fireplace, kitchenette, and panoramic mountain views.', 'Images/hotel_room3.jpg', 299.99, 'available'),
(7, 'Deluxe Chalet', 'Two-bedroom chalet with full kitchen, living area, and private hot tub.', 'Images/hotel_room1.jpg', 499.99, 'available'),
(7, 'Standard Lodge Room', 'Cozy room with mountain-inspired decor and essential amenities.', 'Images/hotel_room2.jpg', 179.99, 'available');

-- Insert rooms for Urban Boutique Hotel (ID 4)
INSERT INTO rooms (hotel_id, name, description, image, price, status) VALUES
(8, 'Designer Suite', 'Uniquely designed suite with artistic elements and luxury amenities.', 'Images/hotel_room1.jpg', 379.99, 'available'),
(8, 'Urban Loft', 'Modern loft-style room with high ceilings and city views.', 'Images/hotel_room2.jpg', 279.99, 'available'),
(8, 'Compact Studio', 'Efficiently designed studio with smart furnishings and technology.', 'Images/hotel_room3.jpg', 159.99, 'available');

-- Insert rooms for Sunset Paradise Resort (ID 5)
INSERT INTO rooms (hotel_id, name, description, image, price, status) VALUES
(9, 'Overwater Bungalow', 'Exclusive bungalow built over crystal clear waters with direct ocean access.', 'Images/hotel_room2.jpg', 799.99, 'available'),
(9, 'Beachfront Suite', 'Luxurious suite with direct beach access and panoramic ocean views.', 'Images/hotel_room1.jpg', 599.99, 'available'),
(9, 'Garden Villa', 'Private villa surrounded by tropical gardens with outdoor shower.', 'Images/hotel_room3.jpg', 349.99, 'available');


-- Add a test booking for user ID 48 (elysianstays_user)
INSERT INTO bookings (
    hotel_id, 
    room_id, 
    guest_name, 
    guest_email, 
    guest_phone, 
    check_in_date, 
    check_out_date, 
    total_price, 
    status,
    created_at
) VALUES (
    5,                           -- hotel_id (Beachfront Resort & Spa)
    10,                          -- room_id (Ocean View Suite in Beachfront Resort)
    'elysianstays user',         -- guest_name (from user 48)
    '90pv6o2hd@mozmail.com',     -- guest_email (from user 48)
    '9898989898',                -- guest_phone (from user 48)
    '2023-12-15',                -- check_in_date
    '2023-12-20',                -- check_out_date
    1999.95,                     -- total_price (5 nights × $399.99 per night)
    'confirmed',                 -- status
    NOW()                        -- created_at
);



ALTER TABLE hotels ADD COLUMN background_image varchar(255) NOT NULL DEFAULT '';


-- Make sure we have some bookings with dates for testing
-- Check if there are bookings with dates
SELECT COUNT(*) FROM bookings WHERE check_in_date IS NOT NULL AND check_out_date IS NOT NULL;

-- If no results, create a few test bookings with dates
INSERT INTO bookings (hotel_id, room_id, guest_name, guest_email, guest_phone, check_in_date, check_out_date, total_price, status)
VALUES 
(5, 10, 'Test User 1', 'test1@example.com', '1234567890', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 599.97, 'confirmed'),
(6, 13, 'Test User 2', 'test2@example.com', '1234567891', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 1299.95, 'confirmed'),
(7, 16, 'Test User 3', 'test3@example.com', '1234567892', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 899.97, 'pending');

-- Make sure we have amenities properly set up
SELECT COUNT(*) FROM amenities WHERE hotel_id IS NULL;

-- If there are no global amenities, add some
INSERT INTO amenities (hotel_id, name, description, icon) 
VALUES 
(NULL, 'Free Wi-Fi', 'High-speed internet throughout the property', 'fa-wifi'),
(NULL, 'Swimming Pool', 'Outdoor swimming pool with loungers', 'fa-swimming-pool'),
(NULL, 'Fitness Center', 'Fully equipped gym with modern equipment', 'fa-dumbbell'),
(NULL, 'Spa Services', 'Full-service spa offering massages and treatments', 'fa-spa'),
(NULL, 'Room Service', '24-hour room service available', 'fa-concierge-bell'),
(NULL, 'Restaurant', 'On-site restaurant offering local and international cuisine', 'fa-utensils'),
(NULL, 'Bar/Lounge', 'Stylish bar serving drinks and light snacks', 'fa-glass-martini-alt'),
(NULL, 'Airport Shuttle', 'Complimentary airport transfers', 'fa-shuttle-van'),
(NULL, 'Parking', 'Free on-site parking for guests', 'fa-parking'),
(NULL, 'Breakfast Included', 'Complimentary breakfast included with stay', 'fa-coffee')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Connect hotels with amenities using the junction table
-- For each hotel, add 3-5 random amenities
INSERT INTO hotel_amenities (hotel_id, amenity_id)
SELECT h.id, a.id
FROM hotels h
CROSS JOIN amenities a
WHERE a.hotel_id IS NULL
AND RAND() < 0.5  -- This gives roughly a 50% chance of adding each amenity to each hotel
AND NOT EXISTS (SELECT 1 FROM hotel_amenities ha WHERE ha.hotel_id = h.id AND ha.amenity_id = a.id)
LIMIT 30;  -- Just to be safe