-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 16, 2025 at 10:54 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elysian_stays`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_content`
--

CREATE TABLE `about_content` (
  `id` int NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_content`
--

INSERT INTO `about_content` (`id`, `section_name`, `title`, `content`, `display_order`, `created_at`, `updated_at`) VALUES
(3, 'main_heading', 'The Unparalleled Guardian of Grandeur', 'Built on a vision of grandeur, Elysian Stays conjures a panoply of superlative experiences that are envisioned to indulge and forge unforgettable memories.', 1, '2025-03-24 12:17:49', '2025-03-24 12:17:49'),
(4, 'our_story', 'Our Story', 'Elysian Stays began with a vision to redefine luxury travel, offering curated stays that transcend the ordinary.\r\nFounded with a commitment to connecting discerning travelers with the world\'s most exquisite hotels and villas, Elysian Stays has quickly become a global gateway to unparalleled elegance.\r\nFrom idyllic beachfront retreats to majestic city escapes, our mission is to create a seamless experience where every stay feels like a chapter of your dream journey.\r\nEvery day, Elysian Stays connects travelers with opulent accommodations that celebrate the art of hospitality, ensuring memories that linger long after the journey ends.', 2, '2025-03-24 12:17:49', '2025-03-24 12:17:49');

-- --------------------------------------------------------

--
-- Table structure for table `about_facts`
--

CREATE TABLE `about_facts` (
  `id` int NOT NULL,
  `statistic_value` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `footnote` varchar(255) DEFAULT NULL,
  `animation_class` varchar(50) DEFAULT 'animate__fadeIn',
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `about_facts`
--

INSERT INTO `about_facts` (`id`, `statistic_value`, `description`, `footnote`, `animation_class`, `display_order`, `created_at`, `updated_at`) VALUES
(33, '18M+', 'active listings worldwide', 'as of June 30, 2024', 'animate__fadeInLeft', 1, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(34, '100K+', 'cities and towns with active Elysian Stays listings', 'as of December 31, 2023', 'animate__fadeInRight', 2, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(35, '220+', 'countries and regions with Elysian Stays listings', 'as of December 31, 2023', 'animate__fadeInLeft', 3, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(36, '2B+', 'Elysian Stays guest arrivals all-time', 'as of September 30, 2024', 'animate__fadeInRight', 4, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(37, '5M+', 'hosts on Elysian Stays', 'as of December 31, 2023', 'animate__fadeInLeft', 5, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(38, '$250B+', 'earned by hosts, all-time', 'as of December 31, 2023', 'animate__fadeInRight', 6, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(39, '$14K', 'earned by the typical US host in 2023', 'as of December 31, 2023', 'animate__fadeInLeft', 7, '2025-03-25 10:55:30', '2025-03-25 10:55:30'),
(40, '$10B+', 'total taxes collected and remitted globally', 'as of December 31, 2023', 'animate__fadeInRight', 8, '2025-03-25 10:55:30', '2025-03-25 10:55:30');

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int NOT NULL,
  `hotel_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `hotel_id`, `name`, `description`, `icon`) VALUES
(1, NULL, 'Swimming Pool', 'Relax and unwind in our temperature-controlled swimming pool.', 'fas fa-swimming-pool'),
(2, NULL, 'Fine Dining', 'Enjoy gourmet dishes curated by world-class chefs.', 'fas fa-utensils'),
(3, NULL, 'Spa & Wellness', 'Pamper yourself with our rejuvenating spa treatments.', 'fas fa-spa'),
(4, NULL, 'Fitness Center', 'Stay fit with our state-of-the-art gym facilities.', 'fas fa-dumbbell'),
(5, NULL, 'Free Wi-Fi', 'Enjoy high-speed internet access throughout the hotel.', 'fas fa-wifi'),
(8, NULL, 'Fitness Center', 'Fully equipped gym with modern equipment', 'fas fa-dumbbell'),
(9, NULL, 'Spa Services', 'Full-service spa offering massages and treatments', 'fas fa-spa'),
(10, NULL, 'Room Service', '24-hour room service available', 'fas fa-concierge-bell'),
(11, NULL, 'Restaurant', 'On-site restaurant offering local and international cuisine', 'fas fa-utensils'),
(12, NULL, 'Bar/Lounge', 'Stylish bar serving drinks and light snacks', 'fas fa-glass-martini-alt'),
(13, NULL, 'Airport Shuttle', 'Complimentary airport transfers', 'fas fa-shuttle-van'),
(14, NULL, 'Parking', 'Free on-site parking for guests', 'fas fa-parking'),
(15, NULL, 'Breakfast Included', 'Complimentary breakfast included with stay', 'fas fa-coffee');

-- --------------------------------------------------------

--
-- Table structure for table `assistance_centers`
--

CREATE TABLE `assistance_centers` (
  `id` int NOT NULL,
  `city` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `display_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assistance_centers`
--

INSERT INTO `assistance_centers` (`id`, `city`, `address`, `phone`, `email`, `display_order`) VALUES
(1, 'AHMEDABAD', '405, Tilakraj Complex, Behind Suryarath Complex, Panchwati First lane, Ahmedabad, 380006', '+91 79264 65591 / 93', 'sales.ahmedabad@elysianstays.com', 1),
(2, 'AUSTRALIA : SYDNEY', 'PO BOX 364, Lindfield, New South Wales 2070', '+61 294 403 613', 'sales.sydney@elysianstays.com', 2),
(3, 'BANGALORE', '41, Race Course Rd, Sampangirama Nagar, High Grounds, Bengaluru, Karnataka 560001', '+91 80666 05660', 'sales.bengaluru@elysianstays.com', 3),
(4, 'CHENNAI', '37, Uthamar Gandhi Rd, Tirumurthy Nagar, Nungambakkam, Chennai, Tamil Nadu 600034', '+91 44660 02827', 'sales.chennai@elysianstays.com', 4),
(5, 'DENMARK: SCANDINAVIA', 'Atlantic Link, Kompagnistraede 34, 4th fl.DK-1208 Copenhagen K, Denmark', '+45 70 27 23 71', 'sales.scandinavia@elysianstays.com', 5),
(6, 'HYDERABAD', 'Rd Number 1, Mada Manzil, Banjara Hills, Hyderabad, Telangana 500034', '+91 40666 62323', 'sales.hyderabad@elysianstays.com', 6);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `room_id` int NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `guest_email` varchar(100) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `hotel_id`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in_date`, `check_out_date`, `total_price`, `status`, `created_at`) VALUES
(26, 16, 33, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-02', '2025-04-23', 31500.00, 'confirmed', '2025-04-03 12:54:43'),
(27, 16, 34, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-03', '2025-04-04', 1222.50, 'pending', '2025-04-03 13:10:36'),
(28, 18, 36, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-04', '2025-04-08', 250000.00, 'confirmed', '2025-04-04 09:55:08'),
(29, 18, 37, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-05', '2025-04-06', 2250.00, 'completed', '2025-04-04 10:00:58'),
(30, 16, 34, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-04', '2025-04-05', 1222.50, 'confirmed', '2025-04-04 10:05:46'),
(31, 20, 40, 'tester', 'dccord3@gmail.com', '989888888893', '2025-04-09', '2025-05-17', 427500.00, 'confirmed', '2025-04-08 08:05:02'),
(32, 18, 35, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-13', '2025-04-14', 31250.00, 'confirmed', '2025-04-09 03:44:06'),
(33, 18, 35, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2026-02-12', '2026-02-27', 375000.00, 'confirmed', '2025-04-09 03:48:00'),
(34, 22, 44, 'tester', 'dccord3@gmail.com', '989888888893', '2025-08-17', '2025-08-20', 318750.00, 'cancelled', '2025-04-09 04:12:27'),
(35, 21, 43, 'elysianstays user', '90pv6o2hd@mozmail.com', '9898989898', '2025-04-12', '2025-04-13', 15000.00, 'confirmed', '2025-04-12 08:45:11'),
(36, 16, 34, 'sdaf', 'sdaf@gmail.asdfo.asdf', 'dsf', '2025-05-01', '2025-05-02', 978.00, 'cancelled', '2025-04-13 06:17:16'),
(37, 18, 36, 'elysianstays_admin', 'tp921s3ed@mozmail.com', '9989898989', '2025-02-01', '2025-04-02', 3000000.00, 'confirmed', '2025-04-13 06:20:11'),
(38, 20, 41, 'elysianstays user', '90pv6o2hd@mozmail.com', '6898989893', '2025-04-14', '2025-04-15', 8750.00, 'confirmed', '2025-04-14 08:24:33'),
(39, 18, 37, 'tester', 'dccord3@gmail.com', '989888888893', '2025-04-14', '2025-04-15', 2250.00, 'confirmed', '2025-04-14 08:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `booking_steps`
--

CREATE TABLE `booking_steps` (
  `step_id` int NOT NULL,
  `step_number` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `animation_delay` varchar(20) NOT NULL DEFAULT '0s',
  `display_order` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `booking_steps`
--

INSERT INTO `booking_steps` (`step_id`, `step_number`, `title`, `description`, `animation_delay`, `display_order`) VALUES
(1, 1, 'Search', 'Start by searching for hotels based on your desired location, check-in, and check-out dates. You can filter by price, rating, and other preferences to find the perfect stay for your trip.', '0s', 1),
(2, 2, 'Choose', 'Browse through a list of hotels that match your search criteria. You can view detailed information about each hotel, including amenities, photos, and guest reviews. Select the hotel and room that best suits your needs.', '1s', 2),
(3, 3, 'Payment', 'Once you have selected your hotel and room, proceed to secure payment. You can pay using various methods such as credit/debit cards, online wallets, or bank transfer.', '2s', 3),
(4, 4, 'Confirmation', 'After completing the payment, you will receive a booking confirmation email with all your stay details. Enjoy your trip, and feel free to reach out if you need assistance!', '3s', 4);

-- --------------------------------------------------------

--
-- Table structure for table `contact_hero`
--

CREATE TABLE `contact_hero` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `search_placeholder` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_hero`
--

INSERT INTO `contact_hero` (`id`, `title`, `background_image`, `search_placeholder`) VALUES
(1, 'Contact', 'https://i.ibb.co/rRVx1brQ/Rambagh-Palace.webp', 'Click here to search for Destinations or Hotels.');

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_info`
--

INSERT INTO `contact_info` (`id`, `hotel_id`, `email`, `phone`, `address`) VALUES
(5, 16, 'contact@grandelysium.com', '(331) 234-5678', '10 Avenue des Champs-Élysées, 75008 Paris, France'),
(7, 18, 'reservations@burjalarab.com', '+971 4 301 7777', 'Burj Al Arab, Jumeirah Beach, Dubai, UAE'),
(8, 19, 'stay@atlantistheroyal.com', '(971) 442-6200', 'Crescent Rd, Palm Jumeirah, Dubai, United Arab Emirates'),
(9, 20, 'reservations@oberoigroup.com', '+91 294 243 3300', 'Haridasji Ki Magri, Udaipur, Rajasthan 313001, India'),
(10, 21, 'reservations@kokomoislandfiji.com', '+679 776 4441', 'Yaukuve Levu Island, Kadavu Islands, Fiji'),
(11, 22, 'info@palms.com', '1-866-942-7777', '4321 W. Flamingo Rd., Las Vegas, NV 89103');

-- --------------------------------------------------------

--
-- Table structure for table `contact_numbers`
--

CREATE TABLE `contact_numbers` (
  `id` int NOT NULL,
  `region` varchar(100) NOT NULL,
  `number` varchar(50) NOT NULL,
  `display_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_numbers`
--

INSERT INTO `contact_numbers` (`id`, `region`, `number`, `display_order`) VALUES
(1, 'Toll-free India', '1-800-111-825', 1),
(2, 'India Network', '00800-222-6030-1125', 2),
(3, 'USA & Canada', '1-866-969-1825', 3),
(4, 'Bahrain Toll Free', '80006488', 4),
(5, 'Brazil Toll Free', '08008912207', 5),
(6, 'Egypt Toll Free', '08000000425', 6),
(7, 'United Arab Emirates (UAE) Toll Free', '800-032-0477', 7),
(8, 'Other Countries', '00-800-4-588-1-825', 8);

-- --------------------------------------------------------

--
-- Table structure for table `earnings_estimates`
--

CREATE TABLE `earnings_estimates` (
  `id` int NOT NULL,
  `min_nights` int NOT NULL,
  `max_nights` int NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `average_nightly_earnings` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `earnings_estimates`
--

INSERT INTO `earnings_estimates` (`id`, `min_nights`, `max_nights`, `base_price`, `average_nightly_earnings`, `created_at`, `updated_at`) VALUES
(1, 1, 30, 4898.00, 4500.00, '2025-03-24 15:27:39', '2025-03-24 15:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE `features` (
  `feature_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icon_url` varchar(255) NOT NULL,
  `display_order` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `special_class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `features`
--

INSERT INTO `features` (`feature_id`, `title`, `description`, `icon_url`, `display_order`, `is_active`, `special_class`) VALUES
(1, 'Best Deals', 'We offer competitive prices and exclusive discounts for the best value.', 'Images/best_deal.jpg', 1, 1, NULL),
(2, 'Secure Booking', 'Your bookings are safe and secured with advanced encryption.', 'Images/secure.png', 2, 1, 'unique'),
(3, '24/7 Support', 'Our customer support is always here to assist you anytime, anywhere.', 'Images/full_day.jpg', 3, 1, NULL),
(4, 'Wide Range of Options', 'Choose from thousands of hotels worldwide to suit your preferences.', 'Images/variety.png', 4, 1, NULL),
(5, 'Verified Reviews', 'Read honest reviews from verified guests to make informed decisions.', 'Images/reviews.jpeg', 5, 1, NULL),
(6, 'Loyalty Rewards', 'Earn points on every booking and redeem them for exclusive benefits.', 'Images/loyalty.png', 6, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `hotel` varchar(100) DEFAULT NULL,
  `date_of_stay` date DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `comments` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `hotel`, `date_of_stay`, `phone`, `comments`, `created_at`) VALUES
(24, 'ved', 'ved@gmail.com', '', NULL, '9888883333', 'sdf', '2025-04-13 06:58:19');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `hotel_id`, `image`, `alt_text`) VALUES
(38, 18, 'https://th.bing.com/th/id/OIP.TLED1Nt5N4-dXKv0jpxw8wHaEK?w=282&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 'Burj Al Arab Exterior'),
(39, 18, 'https://th.bing.com/th/id/OIP.mh437scEVaBDhFMjQk-aKQHaE8?w=272&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 'Royal Suite'),
(40, 18, 'https://th.bing.com/th/id/OIP.1ZP9b3kh0ZVOY_03NR7QJQHaEo?w=280&h=127&c=7&r=0&o=5&dpr=1.3&pid=1.7', 'Infinity Pool'),
(41, 16, 'https://th.bing.com/th/id/OIP.mIwijilmgm71i63y-K3HLQHaE8?pid=ImgDet&w=184&h=122&c=7&dpr=1.3', 'Pool'),
(42, 16, 'https://th.bing.com/th/id/OIP.OOa83AZhXTziy6o3pibRrQHaDF?w=327&h=146&c=7&r=0&o=5&dpr=1.3&pid=1.7', 'setting'),
(72, 19, 'https://assets.kerzner.com/api/public/content/7372f2126994401bb77c2115a1c9c9dc?v=f4de48db&t=w992', 'imag1'),
(73, 19, 'https://assets.kerzner.com/api/public/content/a442b49a70854240953e3913fdd1e332?v=ffba6bfe&t=w992', 'img2'),
(74, 19, 'https://assets.kerzner.com/api/public/content/0da16479c4ff40aba7b11fbd0cd84681?v=cc65c84d&t=w992', ''),
(75, 19, 'https://assets.kerzner.com/api/public/content/496985d6440b4ba797e9c0f7b36c7f8b?v=8a15b2e0', ''),
(76, 19, 'https://assets.kerzner.com/api/public/content/4096d4ac75794d9cad792148508a97f1?v=a4d7914c&t=w992', ''),
(77, 19, 'https://assets.kerzner.com/api/public/content/095ae94822b241bc91d632b7d8357397?v=f30daba5&t=w992', ''),
(87, 20, 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/the-oberoi-udaivilas-udaipur/dining/spotlight/desktop/udaivilas-dining-main-banner-1920x562.jpg?extension=webp', ''),
(88, 20, 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/the-oberoi-udaivilas-udaipur/dining/detail/restaurant-1.jpg?w=724&extension=webp&hash=0c3b19df446b91d18b1de9b7e78882e5', ''),
(89, 20, 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/the-oberoi-udaivilas-udaipur/dining/detail/restaurant-2.jpg?w=724&extension=webp&hash=74ffaf95e0937601c04b7cec92828658', ''),
(90, 20, 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/the-oberoi-udaivilas-udaipur/experiences/home/new/udaivilas-experience-dinner-at-lakeside-pavilion-777x529.jpg?w=777&extension=webp&hash=f9ab686841b6ec0c202cc901d7d3abee', ''),
(102, 21, 'https://www.kokomoislandfiji.com/img/pages/home/welcome-discover/xl.webp', ''),
(103, 21, 'https://www.kokomoislandfiji.com/img/pages/stay/villas/preview.jpg', ''),
(104, 21, 'https://www.kokomoislandfiji.com/img/pages/indulge/venues/walker-d-plank/thumb.jpg', ''),
(111, 22, 'https://www.palms.com/sites/default/files/2023-07/2021-12-05-Yaamava-Exterior_r1-1-480x480.jpg', ''),
(112, 22, 'https://www.palms.com/sites/default/files/2023-07/Scotch80-06-scaled.jpg', ''),
(113, 22, 'https://www.palms.com/sites/default/files/2023-07/slots.jpg', ''),
(114, 22, 'https://www.palms.com/sites/default/files/2023-07/Palms_VetriResturantHero_v4.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int NOT NULL,
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
  `owner_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `background_image` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `tagline`, `about_title`, `about_description1`, `about_description2`, `about_image`, `rooms_title`, `rooms_description`, `amenities_title`, `amenities_description`, `gallery_title`, `gallery_description`, `map_embed_url`, `owner_id`, `user_id`, `background_image`) VALUES
(16, 'The Grand Elysium', 'Experience Luxury Beyond Imagination', 'About The Grand Elysium', 'The Grand Elysium is a five-star luxury hotel located in the heart of Paris, offering world-class services, opulent accommodations, and exquisite dining experiences.', 'Our hotel combines classic European elegance with modern amenities, providing guests with an unforgettable stay. Whether you\'re visiting for business or leisure, we ensure a refined experience.', 'https://th.bing.com/th/id/OIP.OOa83AZhXTziy6o3pibRrQHaDF?w=327&h=146&c=7&r=0&o=5&dpr=1.3&pid=1.7', 'Luxury Rooms & Suites', 'Our hotel features a range of elegant rooms and suites, each designed with comfort and sophistication in mind. Enjoy plush bedding, state-of-the-art technology, and breathtaking city views.', 'Hotel Amenities', 'The Grand Elysium offers a variety of premium amenities, including a spa, rooftop infinity pool, Michelin-starred restaurants, and a 24-hour fitness center.', 'Photo Gallery', 'Our Exquisite Spaces', 'https://maps.google.com/example-location', 62, 62, 'https://images.pexels.com/photos/1134176/pexels-photo-1134176.jpeg?cs=srgb&dl=dug-out-pool-hotel-poolside-1134176.jpg&fm=jpg'),
(18, 'Burj Al Arab', 'The World\'s Most Luxurious Hotel', 'About Burj Al Arab', 'The Burj Al Arab is an iconic 7-star hotel located in Dubai, UAE, standing on its own artificial island. It is known for its opulent duplex suites, lavish gold-infused interiors, and unparalleled hospitality services.', 'The hotel offers personal butler service, chauffeur-driven Rolls-Royces, an infinity pool terrace, and access to a private beach. With its ultra-luxurious spa, award-winning restaurants, and breathtaking views of the Arabian Gulf, Burj Al Arab promises an extraordinary stay.', 'https://th.bing.com/th/id/OIP.ccu3uzwZ4Fac92Jq16PtJgHaGr?w=240&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 'he Ultimate Luxury Suites', 'The Burj Al Arab offers 198 luxurious duplex suites, featuring gold accents, sweeping staircases, plush furnishings, and floor-to-ceiling windows with stunning views of the Arabian Gulf. The Royal Suite, one of the world\'s most expensive suites, is a true palace in the sky.', 'World-Class Amenities', 'Guests at Burj Al Arab can indulge in 24-hour butler service, chauffeur-driven Rolls-Royces, a luxury spa, a private beach, nine exquisite restaurants, and an infinity pool terrace. The hotel also provides a dedicated helicopter service for arrivals and departures.', 'A Glimpse of Extravagance', 'Experience the grandeur of Burj Al Arab through stunning visuals capturing its legendary suites, fine dining experiences, and breathtaking skyline views.', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1735.369779255963!2d55.183690838638924!3d25.1415548444367!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f6a576414cf2d%3A0xb3da71b879f0e038!2sBurj%20Al%20Arab!5e1!3m2!1sen!2sin!4v1743691560961!5m2!1sen!2sin\" width=\"800\" height=\"600\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade', 64, 64, 'https://th.bing.com/th/id/OIP.7Tiph1JpJIz7xSjMoSFWYQHaEK?w=320&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7'),
(19, 'Atlantis The Royal', 'Where Ultra-Luxury Meets the Sky', 'About Atlantis The Royal', 'Atlantis The Royal in Dubai redefines luxury living, with its futuristic architecture, fine dining curated by celebrity chefs, and sky-high accommodations overlooking the Arabian Gulf.', 'Home to The Royal Mansion, the most expensive hotel suite in the world, the resort features world-class wellness retreats, private beaches, infinity sky pools, and bespoke guest services for an elite experience.', 'https://i.ibb.co/Kj9LBQwW/SKYPOOL-VILLA-420-1.webp', 'Lavish Sky Villas & Royal Mansions', 'The rooms and suites at Atlantis The Royal are architectural masterpieces. From sky pools in every villa to panoramic windows and artfully crafted interiors, every space is designed to indulge your senses.', 'Unmatched Luxury Amenities', 'Indulge in Michelin-star dining, a sky-high infinity pool, wellness therapies at the AWAKEN Spa, private butler service, VIP nightlife venues, and direct beach access. The hotel also boasts a Louis Vuitton custom Ping-Pong table and Hermès-branded essentials.', 'A Glimpse of Atlantis The Royal', 'Explore our stunning visuals — from sky villas to the opulent Royal Mansion, every corner exudes elegance and innovation.', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6941.585630800892!2d55.11759918790716!3d25.139681474965258!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f15086afe32c9%3A0xcc7618cd4530c0e1!2sAtlantis%20The%20Royal!5e1!3m2!1sen!2sin!4v1743837851120!5m2!1sen!2sin\" width=\"800\" height=\"600\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade', 66, 66, 'https://i.ibb.co/rRnZwGYN/ATR-Exterior-Building-Side-Sunset.webp'),
(20, 'The Oberoi Udaivilas', 'Experience the Grandeur of Mewar', 'About The Oberoi Udaivilas', 'Nestled on the banks of Lake Pichola in Udaipur, The Oberoi Udaivilas stands as a testament to the rich heritage of Rajasthan. Spread over 50 acres, the resort is built on the 200-year-old hunting grounds of the Maharana of Mewar and showcases the architectural splendor of a traditional Indian palace.', 'Guests can immerse themselves in the opulence of domed structures, intricate frescoes, and sprawling courtyards. The resort offers unparalleled views of the City Palace and Lake Pichola, ensuring a regal experience that echoes the grandeur of India\'s royal past.', 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/offers/2021/offer-images-sep21/udaivilas/banner/udaivilas-unforgettable-getaways-1366x523-53.jpg?extension=webp', 'Palatial Accommodations', 'The Oberoi Udaivilas offers a range of luxurious rooms and suites, each reflecting the rich cultural heritage of Rajasthan. From Premier Rooms with semi-private pools to the lavish Kohinoor Suite, every accommodation is designed to provide guests with an unforgettable royal experience.', 'World-Class Amenities', 'Guests can indulge in a variety of amenities, including:', 'A Visual Journey of The Oberoi Udaivilas', 'Explore the majestic beauty of The Oberoi Udaivilas through these captivating images', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3628.3422315590756!2d73.66986627519067!3d24.577382678115107!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x396ccf804ea9e2f3%3A0x21eb806bf9fc7e9a!2sThe%20Oberoi%20Udaivilas%2C%20Udaipur!5e0!3m2!1sen!2sin!4v1743842457669!5m2!1sen!2sin\" width=\"800\" height=\"600\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade', 67, 67, 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/the-oberoi-udaivilas-udaipur/overview/overview-banners/1366-x-523-new.jpg'),
(21, 'Kokomo Private Island Fiji', 'Where pristine reefs set the scene for an unrivalled', 'About Kokomo Private Island Fiji', 'Nestled in the heart of Fiji\'s Kadavu Islands, Kokomo Private Island is a luxurious retreat surrounded by the Great Astrolabe Reef, the world\'s fourth-largest barrier reef. This exclusive island resort offers unparalleled privacy, pristine beaches, and lush tropical landscapes.', 'Accommodations include 21 beachfront villas and 5 expansive residences, each featuring private infinity pools, tropical gardens, and direct beach access. The resort emphasizes sustainability and integrates traditional Fijian design with modern luxury.', 'https://www.kokomoislandfiji.com/img/pages/discover/overview/lang-walker/xl.webp', 'Luxurious Villas & Residences', 'Kokomo offers a selection of one to three-bedroom beachfront villas and three to six-bedroom residences, each designed to provide ultimate comfort and seclusion. All accommodations feature private infinity pools, spacious living areas, and panoramic ocean views.', 'World-Class Amenities', 'Guests can indulge in a variety of amenities, including:', 'Discover Kokomo Private Island', 'Explore the breathtaking beauty and luxury of Kokomo through these images', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14517.26448304078!2d178.52370061855078!3d-18.804868335651964!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6e1b9e2632dec5c7%3A0xe4b742c7ec6e5eec!2sKokomo%20Private%20Island%20Resort!5e1!3m2!1sen!2sin!4v1743844119610!5m2!1sen!2sin\" width=\"800\" height=\"600\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade', 68, 68, 'https://www.kokomoislandfiji.com/img/pages/discover/overview/location/xl.webp'),
(22, 'Palms Casino Resort', 'Unmatched Luxury and Entertainment Just Off the Strip.', 'About Palms Casino Resort​', 'Located adjacent to the Las Vegas Strip, Palms Casino Resort offers premier gaming, entertainment, and accommodations.', 'The resort features over 700 hotel rooms and suites across two distinct towers, a diverse mix of bars, restaurants, live entertainment venues, and extensive meeting and event spaces.', 'https://www.palms.com/sites/default/files/styles/coh_xx_large_landscape/public/2024-04/palmsblockr2.jpg?h=f0d95172&itok=G_j2szHU', 'Luxurious Accommodations​', 'Palms Casino Resort offers a variety of accommodations, including spacious suites and signature rooms with stunning city views.', 'World-Class Amenities', 'Guests can enjoy an array of amenities, including:', 'Experience Palms Casino Resort', 'Explore the elegance and excitement of Palms Casino Resort through these images:', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12388.882224088615!2d-115.19494659999998!3d36.114840799999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c8c69929a4e157%3A0x3408652dadfedf7e!2sPalms%20Casino%20Resort!5e1!3m2!1sen!2sin!4v1743846229700!5m2!1sen!2sin\" width=\"800\" height=\"600\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade', 69, 69, 'https://th.bing.com/th/id/R.7d96882800fc6c0df00a6dd604313275?rik=r9bip5xjlo%2bF5g&riu=http%3a%2f%2ftraciedomino.com%2fwp-content%2fuploads%2f2013%2f09%2fpalms_hotel_wedding.jpg&ehk=zeNm5ic62U7ki9UfW9ugZ182m8T3s44R7sPYNATwuUk%3d&risl=&pid=ImgRaw&r=0');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_amenities`
--

CREATE TABLE `hotel_amenities` (
  `hotel_id` int NOT NULL,
  `amenity_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hotel_amenities`
--

INSERT INTO `hotel_amenities` (`hotel_id`, `amenity_id`) VALUES
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(16, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(18, 3),
(19, 3),
(20, 3),
(21, 3),
(16, 4),
(18, 4),
(19, 4),
(16, 5),
(18, 5),
(19, 5),
(21, 5),
(22, 5),
(18, 8),
(19, 8),
(18, 9),
(19, 9),
(16, 10),
(18, 10),
(19, 10),
(20, 10),
(21, 10),
(18, 11),
(19, 11),
(20, 11),
(21, 11),
(22, 11),
(18, 12),
(19, 12),
(20, 12),
(21, 12),
(22, 12),
(18, 13),
(22, 13),
(18, 14),
(22, 14),
(16, 15),
(18, 15);

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE `page_content` (
  `id` int NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `display_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `page_content`
--

INSERT INTO `page_content` (`id`, `section_name`, `title`, `content`, `display_order`) VALUES
(1, 'contact_intro', 'Worldwide Reservation Centre', 'Elysian Stays Reservations Worldwide Centre is accessible 24/7. Toll-free contact numbers are below.', 1),
(2, 'care_section_title', 'CARE@Elysian Stays', NULL, 2),
(3, 'care_section_content', NULL, 'We take pride in crafting moments that stay with you forever. However, if there is anything we can do to make the time you spend in our care better, please do share your thoughts with us. Ensuring your contentment is at the forefront of our commitment.', 3);

-- --------------------------------------------------------

--
-- Table structure for table `page_hero`
--

CREATE TABLE `page_hero` (
  `id` int NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `search_placeholder` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `page_hero`
--

INSERT INTO `page_hero` (`id`, `page_name`, `title`, `background_image`, `search_placeholder`, `created_at`, `updated_at`) VALUES
(1, 'about', 'About Us', 'https://i.ibb.co/1tQs1J7z/Umaid-Bhawan-Palace-3840x1320.webp', 'Click here to search for Destinations or Hotels.', '2025-03-24 11:48:19', '2025-03-27 05:34:39');

-- --------------------------------------------------------

--
-- Table structure for table `page_settings`
--

CREATE TABLE `page_settings` (
  `id` int NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `effective_date` varchar(100) NOT NULL,
  `footer_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `page_settings`
--

INSERT INTO `page_settings` (`id`, `page_title`, `effective_date`, `footer_text`, `created_at`, `updated_at`) VALUES
(1, 'Terms of Service', 'January 2025', '&copy; 2025 Elysian Stays. All rights reserved. | <a href=\"privacy-policy.php\" target=\"_blank\">Privacy Policy</a> | <a href=\"ToS.php\" target=\"_blank\">Terms of Service</a> | <a href=\"TPS.php\" target=\"_blank\">Terms of Payments Service</a>', '2025-03-25 10:33:10', '2025-03-25 10:33:10');

-- --------------------------------------------------------

--
-- Table structure for table `payment_page_settings`
--

CREATE TABLE `payment_page_settings` (
  `id` int NOT NULL,
  `page_title` varchar(255) NOT NULL DEFAULT 'Payments Terms of Service',
  `effective_date` varchar(100) NOT NULL DEFAULT 'January 2025',
  `footer_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_page_settings`
--

INSERT INTO `payment_page_settings` (`id`, `page_title`, `effective_date`, `footer_text`, `created_at`, `updated_at`) VALUES
(1, 'Payments Terms of Service', 'January 2025', '&copy; 2025 Elysian Stays. All rights reserved. | <a href=\"privacy-policy.php\" target=\"_blank\">Privacy Policy</a> | <a href=\"ToS.php\" target=\"_blank\">Terms of Service</a> | <a href=\"TPS.php\" target=\"_blank\">Terms of Payments Service</a>', '2025-03-25 10:41:36', '2025-03-25 10:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `payment_terms`
--

CREATE TABLE `payment_terms` (
  `id` int NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `display_order` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_terms`
--

INSERT INTO `payment_terms` (`id`, `section_title`, `section_content`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '1. Introduction', 'Welcome to Elysian Stays! By using our payment services, you agree to these terms. Please read them carefully.', 1, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(2, '2. Payment Methods', 'We accept a variety of payment methods including credit cards, debit cards, and other digital payment options as displayed during checkout. All payments are securely processed.', 2, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(3, '3. Currency', 'All transactions will be processed in the currency specified at the time of booking.', 3, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(4, '4. Charges and Fees', 'Elysian Stays reserves the right to charge additional fees for processing payments. Any such fees will be disclosed during the payment process.', 4, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(5, '5. Refund Policy', 'Refunds, if applicable, will be processed according to our <a href=\"#\">Refund Policy</a>. Please ensure to review the cancellation terms of your booking.', 5, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(6, '6. Security', 'Your payment information is securely encrypted using the latest security protocols. We do not store full payment card details.', 6, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(7, '7. Disputes', 'If you encounter any issues with a payment, please contact our support team immediately at <a href=\"mailto:support@elysianstays.com\">support@elysianstays.com</a>.', 7, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(8, '8. Changes to Terms', 'Elysian Stays reserves the right to update these payment terms at any time. We encourage you to review this page periodically for updates.', 8, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36'),
(9, '9. Contact Us', 'For any questions or concerns, reach out to us at <a href=\"mailto:support@elysianstays.com\">support@elysianstays.com</a>.', 9, 1, '2025-03-25 10:41:36', '2025-03-25 10:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `protection_features`
--

CREATE TABLE `protection_features` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `elysian_has` tinyint(1) DEFAULT '1',
  `competitors_have` tinyint(1) DEFAULT '0',
  `display_order` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `protection_features`
--

INSERT INTO `protection_features` (`id`, `title`, `description`, `elysian_has`, `competitors_have`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Guest identity verification', 'Our comprehensive verification system checks details such as name, address, government ID, and more to confirm the identity of guests who book on Elysian Stays.', 1, 0, 1, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(2, 'Reservation screening', 'Our proprietary technology analyzes hundreds of factors in each reservation to help identify and flag higher-risk bookings before they occur.', 1, 0, 2, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(3, '$3m damage protection', 'AirCover ensures you\'re covered for guests or their pets damaging your home or items like specialized valuables.', 1, 0, 3, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(4, 'Art & valuables', 'Covers your valuable items such as artwork or collectibles from accidental damage by guests.', 1, 0, 4, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(5, 'Auto & boat', 'Protection includes damages caused to autos and boats in the case of hosting-related incidents.', 1, 0, 5, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(6, 'Pet damage', 'Covers damages caused by guests\' pets during their stay at your home.', 1, 0, 6, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(7, 'Income loss', 'Ensures compensation for income loss due to guest-caused damages or other hosting interruptions.', 1, 0, 7, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(8, 'Deep cleaning', 'Provides coverage for deep cleaning services required after hosting guests.', 1, 0, 8, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(9, '$1m USD liability insurance', 'This helps protect you in the event that a guest gets hurt or their belongings are damaged during a stay.', 1, 0, 9, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(10, '24-hour safety line', 'If you ever feel unsafe, our priority team steps in to support increased safety protocols, any day or night.', 1, 0, 10, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `registered_office`
--

CREATE TABLE `registered_office` (
  `id` int NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `map_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `registered_office`
--

INSERT INTO `registered_office` (`id`, `address`, `phone`, `email`, `map_link`) VALUES
(1, 'Mandlik House, Mandlik Road, Mumbai, Maharashtra 400 001 India', '+91 22-6137-1710', 'investorrelations@elysianstays.com', '#');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `review_date` varchar(50) NOT NULL,
  `review_text` text NOT NULL,
  `rating` int NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `hotel_id`, `author_name`, `review_date`, `review_text`, `rating`, `status`) VALUES
(11, 16, 'elysianstays user', 'April 2025', 'Eat 5 Star do nothing', 5, 'active'),
(12, 20, 'tester', 'April 2025', 'The Oberoi Udaivilas is a luxurious lakeside retreat, offering stunning views of Lake Pichola and City Palace.\n', 5, 'active'),
(13, 18, 'elysianstays user', 'April 2025', 'one of the best experience i had in burj al arab and specially the hospitality, and the view of the room is awesome.', 5, 'active'),
(14, 22, 'tester', 'April 2025', 'Great Trip! Must visit destination', 5, 'active'),
(15, 18, 'elysianstays admin', 'April 2025', 'Burj Al Arab is pure luxury—iconic design, top-tier service, and stunning views. A truly unforgettable experience.', 5, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int NOT NULL DEFAULT '2',
  `status` enum('available','booked') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `hotel_id`, `name`, `description`, `image`, `price`, `capacity`, `status`) VALUES
(33, 16, 'Presidential Suite', 'Experience the pinnacle of luxury with our Presidential Suite, featuring panoramic city views, a private lounge, a king-size bed, a marble bathroom with a Jacuzzi, and personalized butler service.', 'https://th.bing.com/th/id/OIP.Liszb6iyllbFqGCrOUCMBAHaFj?w=220&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 1500.00, 3, 'available'),
(34, 16, 'Deluxe Ocean View Room', 'Relax in our Deluxe Ocean View Room, featuring a spacious balcony with breathtaking sea views, a plush king-size bed, a modern en-suite bathroom, and complimentary access to the infinity pool and spa.', 'https://www.bing.com/th/id/OIP.PxxXYSbcOTsUO3DPCQQUswHaEH?w=178&h=185&c=8&rs=1&qlt=90&o=6&dpr=1.3&pid=3.1&rm=2', 978.00, 2, 'available'),
(35, 18, 'Royal Two-Bedroom Suite', 'Live like royalty in the Royal Two-Bedroom Suite — a lavish 7800 sq. ft. duplex suite featuring a private elevator, grand marble staircase, rotating canopy bed, cinema room, and a personal butler service. With 24-karat gold-plated fittings, Hermès amenities, and panoramic views of the Arabian Gulf, this suite redefines luxury.', 'https://th.bing.com/th/id/OIP.ck7LlluqaHXg6oUrD6wongHaE7?w=230&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 25000.00, 4, 'available'),
(36, 18, 'Manhattan Sky Suite', 'Perched on the 59th floor with breathtaking 360° views of Central Park and the Manhattan skyline, the Manhattan Sky Suite offers 4,200 sq. ft. of unmatched luxury. This three-bedroom suite features a private chef’s kitchen, personal spa room, grand piano, library, and a full-time dedicated butler. The suite also includes VIP access to hotel amenities and a custom art collection worth over $200,000.', 'https://th.bing.com/th/id/OIP.gghEY6Z0LpzUmuqyEdubjgHaEK?w=277&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 50000.00, 6, 'available'),
(37, 18, 'Deluxe Marina Suite', 'The Deluxe Marina Suite at Burj Al Arab offers a stunning 170 sq. m. of refined elegance with panoramic views of the tranquil Dubai Marina. Spread across two levels, it features a spacious living room, king-size bedroom, a luxury bathroom with a Jacuzzi, and Hermes amenities. Guests enjoy 24-hour butler service, a pillow menu, and exclusive access to the hotel’s private beach and premium lounges.', 'https://th.bing.com/th/id/OIP.1Ch8EoyT0Ezbp98MN6MXpgHaFj?w=248&h=187&c=7&r=0&o=5&dpr=1.3&pid=1.7', 1800.00, 2, 'available'),
(38, 19, 'The Royal Mansion', 'Welcome to the most expensive suite in the world — The Royal Mansion at Atlantis The Royal, Dubai. This 11,840 sq. ft. two-level penthouse offers the pinnacle of luxury with four master bedrooms, a private infinity pool overlooking the Arabian Gulf, floor-to-ceiling windows, a private escalator, and exclusive Louis Vuitton Ping-Pong table. Personal butler service, Hermès amenities, and a private entrance make this suite fit for royalty. Celebrities like Beyoncé and Jay-Z have stayed here.', 'https://th.bing.com/th/id/OIP.HiL9DH_uA-hTKWc0ufU48QHaFS?w=800&h=571&rs=1&pid=ImgDetMain', 100000.00, 8, 'available'),
(39, 19, 'Sky Pool Villa', 'The Sky Pool Villa at Atlantis The Royal offers an unforgettable stay suspended in the clouds. This private suite features a terrace with a personal infinity-edge plunge pool, a spacious living area, floor-to-ceiling ocean views, and marble bathrooms stocked with Hermès bath products. Guests enjoy a dedicated butler, in-room fine dining, and direct access to world-class amenities including the AWAKEN spa and celebrity chef restaurants.', 'https://th.bing.com/th/id/OIP.kY7yE2XUAJRDCwWstQ9k4gHaEK?w=303&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 10000.00, 4, 'available'),
(40, 20, 'Kohinoor Suite with Private Pool', 'The Kohinoor Suite is the crown jewel of The Oberoi Udaivilas. Overlooking the tranquil Lake Pichola, this palatial suite features private courtyards, a heated outdoor pool, Jacuzzi, and opulent interiors adorned with hand-painted frescoes and gold leaf detailing. Guests enjoy round-the-clock butler service, a personal dining area, a spa-style bathroom, and access to exclusive luxury experiences — all evoking the lifestyle of Rajasthani royalty.\n\n', 'https://www.oberoihotels.com/-/media/oberoi-hotels/website-images/the-oberoi-udaivilas-udaipur/room-and-suites/kohinoor-suite/detail/touv-kohinoor-suite-exterior-724x407.jpg?w=724&extension=webp&hash=6375a51c98ce36ba57d57f75ce165583', 9000.00, 3, 'available'),
(41, 20, 'Premier Room with Semi-Private Pool', 'Enjoy breathtaking views of the City Palace, across Lake Pichola from the comfort of your 56 square metres room and a semi-private pool. Accessed via the private terrace, the pool is a great place to refresh and unwind.', 'https://shorturl.at/07mIP', 7000.00, 2, 'available'),
(42, 21, 'Sunset 6-Bedroom Residence', 'The Sunset Residence is Kokomo’s most elite offering — a sprawling 6-bedroom private estate perched atop a secluded hilltop with unmatched panoramic views of the Pacific Ocean.', 'https://th.bing.com/th/id/OIP.qefs9vuiQgVqdVZckCGTDwHaDz?w=345&h=179&c=7&r=0&o=5&dpr=1.3&pid=1.7', 25000.00, 8, 'available'),
(43, 21, 'Hilltop 3-Bedroom Residence', 'Perched on an elevated point of the island, the Hilltop 3-Bedroom Residence offers breathtaking views of the ocean and lush surroundings. This ultra-private retreat includes three spacious ensuite bedrooms, a private infinity pool, open-air lounge and dining areas, a gourmet kitchen, and 24/7 dedicated staff. Designed for families or small groups, it perfectly blends seclusion with high-end comfort, delivering a tranquil escape in Fijian paradise.', 'https://th.bing.com/th/id/OIP.mFPZMrEoNWqPxXB10-BRrAHaE7?w=213&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7', 12000.00, 6, 'available'),
(44, 22, 'Empathy Suite', 'Designed by the renowned artist Damien Hirst, the Empathy Suite at Palms Casino Resort is one of the most exclusive and expensive hotel accommodations in the world. Spanning over 9,000 square feet, this two-story sky villa features', 'https://www.palms.com/sites/default/files/styles/coh_x_large/public/2023-05/Palms_DamienHirst_PoolWide_v5.jpg?itok=ZX2p9RUf', 100000.00, 3, 'available'),
(45, 22, 'Two-Story Sky Villa', 'The Two-Story Sky Villa is a luxurious 8,500 sq. ft. suite with stunning city views. It features a private glass-enclosed pool, outdoor terrace, massage room, sauna, two grand bedrooms with spa bathrooms, a media room, and butler service. Perfect for celebrities and exclusive events, it offers the ultimate in high-roller luxury.', 'https://www.palms.com/sites/default/files/styles/coh_x_large/public/2023-05/Palms_Villa_32_Master_v3.jpg?itok=R8E7LZyE', 50000.00, 4, 'available'),
(46, 22, 'Kingpin Suite', 'The Kingpin Suite is a 4,500 sq. ft. bowling-themed haven, featuring two full-size lanes, two king bedrooms, a lounge with curved TVs and a bar, a private media room, and 24-hour butler service. Perfect for bachelor parties and exclusive events, it blends luxury with retro fun.', 'https://www.palms.com/sites/default/files/styles/coh_x_large/public/2023-05/Palms_Kingpin_Hero_v4.jpg?itok=pn6KOdu4', 15000.00, 4, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `setup_features`
--

CREATE TABLE `setup_features` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `setup_features`
--

INSERT INTO `setup_features` (`id`, `title`, `description`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'One-to-one guidance from a Superhost', 'We\'ll match you with a Superhost in your area, who\'ll guide you from your first question to your first guest – by phone, video call or chat.', NULL, 1, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(2, 'An experienced guest for your first booking', 'For your first booking, you can choose to welcome an experienced guest who has at least three stays and a good track record on Elysian Stays.', NULL, 2, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39'),
(3, 'Specialised support from Elysian Stays', 'New Hosts get one-tap access to specially trained Community Support agents who can help with everything from account issues to billing support.', NULL, 3, 1, '2025-03-24 15:27:39', '2025-03-24 15:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `discount` int NOT NULL DEFAULT '0'
) ;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `coupon_code`, `is_active`, `created_at`, `discount`) VALUES
(1, 'Special Offer: 20% Off on Beachside Resorts!', 'BEACH20', 1, '2025-03-20 12:17:06', 20),
(2, 'Early Bird Discount: Save 15%', 'MOUNTAIN15', 1, '2025-03-20 12:17:06', 15),
(3, 'Weekend Getaway: 10% Off City Tours!', 'CITY10', 1, '2025-03-20 12:17:06', 0),
(7, 'testing15', 'test15', 0, '2025-03-21 02:53:15', 15);

-- --------------------------------------------------------

--
-- Table structure for table `terms_of_service`
--

CREATE TABLE `terms_of_service` (
  `id` int NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_content` text NOT NULL,
  `display_order` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `terms_type` enum('general','payments') DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `terms_of_service`
--

INSERT INTO `terms_of_service` (`id`, `section_title`, `section_content`, `display_order`, `is_active`, `created_at`, `updated_at`, `terms_type`) VALUES
(1, '1. Introduction', 'Welcome to Elysian Stays! These Terms of Service govern your access to and use of our website and services. By using our platform, you agree to comply with and be bound by these terms. Please read them carefully before proceeding.', 1, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(2, '2. Eligibility', 'To use our services, you must be at least 18 years old and capable of entering into legally binding agreements. By accessing our site, you confirm that you meet these criteria.', 2, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(3, '3. Use of Services', 'You agree to use Elysian Stays for lawful purposes only. Unauthorized use, including but not limited to scraping, hacking, or attempting to manipulate our services, is strictly prohibited.', 3, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(4, '4. User Accounts', 'Creating an account with Elysian Stays is optional but recommended for full access to our features. You are responsible for maintaining the confidentiality of your account credentials and ensuring that all activities conducted under your account comply with these terms.', 4, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(5, '5. Booking Policies', 'All bookings are subject to availability and confirmation from the respective hotel or property. Please review the cancellation and refund policies specific to your booking before confirming your reservation.', 5, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(6, '6. Intellectual Property', 'All content, trademarks, and materials on our website are the intellectual property of Elysian Stays or its licensors. Unauthorized reproduction, distribution, or use of our content is prohibited.', 6, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(7, '7. Limitation of Liability', 'Elysian Stays is not liable for any damages or losses resulting from your use of our services, including but not limited to booking errors, cancellations, or service interruptions.', 7, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(8, '8. Privacy', 'Your privacy is important to us. Please review our <a href=\"PrivacyPolicy.php\">Privacy Policy</a> to understand how we collect, use, and protect your personal information.', 8, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(9, '9. Termination', 'We reserve the right to terminate or suspend your access to our platform at our discretion if you violate these Terms of Service or engage in unlawful activities.', 9, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(10, '10. Governing Law', 'These terms are governed by and construed in accordance with the laws of the jurisdiction in which Elysian Stays operates.', 10, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(11, '11. Modifications', 'Elysian Stays may update these Terms of Service from time to time. You will be notified of significant changes, and your continued use of our services will constitute acceptance of the revised terms.', 11, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general'),
(12, '12. Contact Us', 'If you have any questions or concerns about these Terms of Service, please contact us at <a href=\"mailto:support@elysianstays.com\">support@elysianstays.com</a>.', 12, 1, '2025-03-25 10:33:10', '2025-03-25 10:33:10', 'general');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `role` enum('user','admin','hotel') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `phone`, `city`, `country`, `profile_pic`, `token`, `role`, `status`, `created_at`, `reset_token`, `reset_token_expiry`) VALUES
(48, 'elysianstays user', 'elysianstays_user', '90pv6o2hd@mozmail.com', '$2y$10$L2T411QC0dQjlws//14Ho.y5Z62sTfAAGK2V16pdKMH3Spp8Hlzpu', '6898989893', 'Rajkot', 'India', 'default-profile.png', NULL, 'user', 'active', '2025-03-20 08:51:03', NULL, NULL),
(50, 'elysianstays admin', 'elysianstays_admin', 'tp921s3ed@mozmail.com', '$2y$10$F3MNfvah7VOAvAvZhh44/ubbNMb2g7QiJIuxGTRYvFywzvH0ZNnpy', '9989898989', 'Rajkot', 'India', 'user-iconset-no-profile.jpg', NULL, 'admin', 'active', '2025-03-20 12:23:00', NULL, NULL),
(62, 'Nirad Patel', 'elysianstays_hotel', 'elysianstays_hotel@gmail.com', '$2y$10$WQ7DEcywE9V6k6yWIh9EteWQmwBuXURjTtZzLufjg0ueWgZ7ZAq/u', '9898988898', 'Rajkot', 'India', 'user-iconset-no-profile.jpg', NULL, 'hotel', 'active', '2025-04-03 11:54:08', NULL, NULL),
(64, 'nirad patel', 'elysianstays_hotel_2', 'elysianstays_hotel_2@gmail.com', '$2y$10$nbxp0.mPVbml9D.VLihWs.D9qmkDp4WOiVM9xLPsegmldsQxVp1zW', '989888898889', 'Rajkot', 'India', 'user-iconset-no-profile.jpg', NULL, 'hotel', 'active', '2025-04-03 14:35:31', NULL, NULL),
(65, 'tester', 'es_user', 'dccord3@gmail.com', '$2y$10$v.eC1rYVkS9Ecj6Q4L8GYO7tpoim8FFEfYd/FrhDdfVDgI/cnfQxy', '989888888893', NULL, NULL, NULL, NULL, 'user', 'active', '2025-04-04 10:11:19', NULL, NULL),
(66, 'nirad patel', 'elysianstays_hotel_3', 'elysianstays_hotel_3@gmail.com', '$2y$10$ibVA.c3F8zlAj/sVkqctCuXHNko1XGyDX2zvfgOUbnCR.5Zt/LnGm', '99888888888', 'Rajkot', 'India', 'user-iconset-no-profile.jpg', NULL, 'hotel', 'active', '2025-04-05 07:11:37', NULL, NULL),
(67, 'nirad patel', 'elysianstays_hotel_4', 'elysianstays_hotel_4@gmail.com', '$2y$10$E.2x0K.duoVDZ3k4oJLeV.R4T3ocxQHRmiRruj/noxZxR1jhRtttG', '98989898987', 'Rajkot', 'India', 'default-profile.png', NULL, 'hotel', 'active', '2025-04-05 08:24:43', NULL, NULL),
(68, 'nirad patel', 'elysianstays_hotel_5', 'elysianstays_hotel_5@gmail.com', '$2y$10$D90NXmg91lpTcvNpr5gdcevoUZYGRSa7cjwXs/szvj2wUIoTWCcKC', '9888777666656', 'LA', 'USA', 'user-iconset-no-profile.jpg', NULL, 'hotel', 'active', '2025-04-05 08:49:05', NULL, NULL),
(69, 'nirad patel', 'elysianstays_hotel_6', 'elysianstays_hotel_6@gmail.com', '$2y$10$y/ZJhoAtm6T6N5h5c3uIwO/1/sD20zdoBNiFUYiJwFQVb..1Q36l.', '988887777665', 'LA', 'USA', 'user-iconset-no-profile.jpg', NULL, 'hotel', 'active', '2025-04-05 09:18:57', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_content`
--
ALTER TABLE `about_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_facts`
--
ALTER TABLE `about_facts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `assistance_centers`
--
ALTER TABLE `assistance_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `booking_steps`
--
ALTER TABLE `booking_steps`
  ADD PRIMARY KEY (`step_id`);

--
-- Indexes for table `contact_hero`
--
ALTER TABLE `contact_hero`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `contact_numbers`
--
ALTER TABLE `contact_numbers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `earnings_estimates`
--
ALTER TABLE `earnings_estimates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`feature_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `hotel_amenities`
--
ALTER TABLE `hotel_amenities`
  ADD PRIMARY KEY (`hotel_id`,`amenity_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_hero`
--
ALTER TABLE `page_hero`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_settings`
--
ALTER TABLE `page_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_page_settings`
--
ALTER TABLE `payment_page_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_terms`
--
ALTER TABLE `payment_terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `protection_features`
--
ALTER TABLE `protection_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registered_office`
--
ALTER TABLE `registered_office`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `setup_features`
--
ALTER TABLE `setup_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `terms_of_service`
--
ALTER TABLE `terms_of_service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_content`
--
ALTER TABLE `about_content`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `about_facts`
--
ALTER TABLE `about_facts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `assistance_centers`
--
ALTER TABLE `assistance_centers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `booking_steps`
--
ALTER TABLE `booking_steps`
  MODIFY `step_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_hero`
--
ALTER TABLE `contact_hero`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_numbers`
--
ALTER TABLE `contact_numbers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `earnings_estimates`
--
ALTER TABLE `earnings_estimates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
  MODIFY `feature_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `page_hero`
--
ALTER TABLE `page_hero`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `page_settings`
--
ALTER TABLE `page_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_page_settings`
--
ALTER TABLE `payment_page_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_terms`
--
ALTER TABLE `payment_terms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `protection_features`
--
ALTER TABLE `protection_features`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `registered_office`
--
ALTER TABLE `registered_office`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `setup_features`
--
ALTER TABLE `setup_features`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms_of_service`
--
ALTER TABLE `terms_of_service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `amenities`
--
ALTER TABLE `amenities`
  ADD CONSTRAINT `amenities_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD CONSTRAINT `contact_info_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `hotel_amenities`
--
ALTER TABLE `hotel_amenities`
  ADD CONSTRAINT `hotel_amenities_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotel_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
