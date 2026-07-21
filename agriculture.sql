-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 03:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agriculture`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Ladani Vatsal', 'ladanivatsal@gmail.com', '$2y$10$POPI6GMnscY51YQhtd7WzuuFzCfVP2Q4KFHiskWr64MvM4kG3cTju', 'admin', '2025-04-25 09:57:35');

-- --------------------------------------------------------

--
-- Table structure for table `cancel_orders`
--

CREATE TABLE `cancel_orders` (
  `cancellation_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `other_reason` text DEFAULT NULL,
  `refund_preference` varchar(50) NOT NULL,
  `cancellation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Processed','Rejected') NOT NULL DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancel_orders`
--

INSERT INTO `cancel_orders` (`cancellation_id`, `order_id`, `user_id`, `reason`, `other_reason`, `refund_preference`, `cancellation_date`, `status`, `admin_notes`) VALUES
(1, 7, 1, 'wrong_item', '', 'original_method', '2025-03-28 16:32:53', 'Processed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(31, 1, 6, 1, '2025-04-08 12:26:11'),
(32, 1, 2, 1, '2025-04-08 14:31:19'),
(41, 3, 3, 2, '2026-07-20 17:50:14'),
(42, 3, 1, 4, '2026-07-20 17:50:15'),
(43, 3, 5, 1, '2026-07-20 17:50:34'),
(44, 3, 2, 2, '2026-07-20 18:00:00'),
(45, 3, 4, 1, '2026-07-21 12:42:34');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Seeds'),
(2, 'Biological Fertilizers'),
(3, 'Fertilizer'),
(4, 'Water Soluble Fertilizers'),
(5, 'Pesticides'),
(6, 'Fungicide');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `expert_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `problem_description` text NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `communication_method` enum('whatsapp','phone','video_call') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `expert_id`, `user_name`, `user_email`, `user_phone`, `problem_description`, `preferred_date`, `preferred_time`, `communication_method`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'Uday', 'udayboda@gmail.com', '8780976406', 'I have some doubts about the methods that i am doing for the crop farming.', '2025-04-10', '05:00:00', 'whatsapp', 'approved', 'Thank You Very Much', '2025-04-08 12:19:38', '2025-04-08 18:47:05');

-- --------------------------------------------------------

--
-- Table structure for table `contactus`
--

CREATE TABLE `contactus` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `query` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contactus`
--

INSERT INTO `contactus` (`contact_id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `subject`, `query`, `created_at`) VALUES
(2, 1, 'Uday', 'Boda', 'udayboda@gmail.com', '8780976406', 'Nice Services ', 'thank you', '2025-03-04 13:47:46');

-- --------------------------------------------------------

--
-- Table structure for table `email_log`
--

CREATE TABLE `email_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `experts`
--

CREATE TABLE `experts` (
  `expert_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `Experts_email` varchar(50) NOT NULL,
  `Contact_no` varchar(15) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experts`
--

INSERT INTO `experts` (`expert_id`, `name`, `Experts_email`, `Contact_no`, `specialization`, `description`, `image_url`) VALUES
(1, 'Dipak Bhai Boda', 'Dipakboda@gmail.com', '1111111111', 'Crop Farming, Horticulture and Organic Farming', 'Nice Personality and easy to communicate with an experience period over an decade', '/Farming_meds/Uploads/Experts/expert2.jpeg'),
(2, 'Uday Boda', 'bodauday@gmail.com', '2233445566', 'Crop Farming', 'Very Nice guy with trusted guidence', '/Farming_meds/Uploads/Experts/expert1.jpeg'),
(5, 'Vatsal Ladani', 'vatsalpatel111@gmail.com', '2233445567', 'Pesticides, Farming Tools and Fertilizers', 'This expert is highly knowledgeable in modern pest control techniques, ensuring crops remain healthy and protected from harmful insects and diseases. With extensive experience in pesticide selection and safe application, they help farmers maximize yield while minimizing environmental impact.\n\nAdditionally, the expert specializes in farming tools and machinery, guiding farmers on the best equipment for plowing, sowing, irrigation, and harvesting. Their expertise in fertilizers ensures balanced soil nutrition, optimizing plant growth and improving crop productivity.', '/Farming_meds/Uploads/Experts/expert4.jpeg'),
(6, 'Kanani Nitin', 'nitin123@gmail.com', '1234567890', 'Fertilizers, Pesticides, Crops Specialist', 'Nitin, our Fertilizers, Pesticides, and Crops Specialist, brings extensive expertise in soil nutrition, pest management, and crop optimization. He works closely with farmers, providing tailored solutions to enhance productivity, sustainability, and farm profitability.', '/Farming_meds/Uploads/Experts/expert5.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` enum('product','expert') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `user_id`, `item_id`, `item_type`, `created_at`) VALUES
(1, 1, 1, 'product', '2025-03-05 09:53:36'),
(2, 1, 3, 'product', '2025-03-05 10:13:58'),
(3, 1, 4, 'product', '2025-03-05 10:14:01'),
(4, 1, 6, 'product', '2025-03-23 09:20:35'),
(12, 1, 2, 'product', '2025-04-08 08:32:30'),
(13, 1, 7, 'product', '2025-04-08 08:43:00'),
(15, 1, 5, 'product', '2025-04-08 10:01:38'),
(16, 3, 1, 'product', '2026-07-20 16:09:12'),
(17, 3, 1, 'expert', '2026-07-20 16:11:52'),
(18, 3, 1, 'expert', '2026-07-20 16:12:01'),
(19, 3, 5, 'product', '2026-07-20 16:38:17');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 4, 'The wheat seeds I purchased from Agromeds were of excellent quality. The packaging was secure, and the delivery was on time. The seeds germinated well, resulting in a healthy crop. Highly satisfied with the purchase and would recommend it to other farmers!', '2025-03-05 11:25:54'),
(2, 3, 5, 5, 'good', '2026-07-20 16:33:34');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `payment_id` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `customer_name`, `email`, `phone`, `pincode`, `address`, `total_amount`, `status`, `cancelled_at`, `order_date`, `payment_method`, `payment_status`, `payment_id`, `transaction_id`) VALUES
(4, 1, 'Redmi Note 13', 'udayboda@gmail.com', '2222222222', '361001', 'cccccc', 24800.00, 'Delivered', NULL, '2025-03-24 18:11:40', NULL, 'Completed', NULL, NULL),
(6, 1, 'Uday', 'udayboda@gmail.com', '2222222222', '361001', 'dxxdd', 803.00, 'Cancelled', '2025-03-28 10:52:14', '2025-03-24 20:17:29', NULL, 'Completed', NULL, NULL),
(7, 1, 'Uday', 'udayboda@gmail.com', '2222222222', '361001', 'abcdefghhhhh', 3680.00, 'Cancelled', '2025-03-28 11:02:53', '2025-03-27 17:37:37', 'UPI', 'Completed', NULL, '1'),
(8, 1, 'Uday', 'udayboda@gmail.com', '2222222222', '361001', 'Jamnagar, Shapar', 14720.00, 'Shipped', NULL, '2025-03-28 18:29:12', 'UPI', 'Completed', NULL, '2'),
(9, 1, 'Uday', 'udayboda@gmail.com', '2222222222', '361001', 'Jamnagar, Shapar', 0.00, 'Shipped', NULL, '2025-03-28 18:50:08', 'COD', 'Completed', NULL, NULL),
(10, 1, 'OnePlus 12', 'vatsal@gmail.com', '2222222222', '361001', 'Jamnagar, Shapar', 800.00, 'Processing', NULL, '2025-03-28 18:51:01', 'UPI', 'Completed', NULL, 'COD-e95ffd7d'),
(11, 2, 'Moto G85', 'admin123@gmail.com', '8780976406', '361001', 'Apurva heights, flat No:1, Saru-section road, Jamnagar', 850.00, 'Processing', NULL, '2025-03-28 19:01:56', 'UPI', 'Completed', NULL, 'COD-0b6d8e73'),
(12, 1, 'Uday', 'udayboda@gmail.com', '2222222222', '361001', 'Jamnagar, Shapar', 9160.00, 'Processing', NULL, '2025-03-29 09:42:51', 'UPI', '', NULL, NULL),
(13, 3, 'vatsal', 'ladanivatsal@gmail.com', '1111111111', '362001', 'junagadh', 2360.00, 'Processing', NULL, '2026-07-20 21:20:56', 'UPI', 'Pending', NULL, NULL),
(14, 3, 'vatsal', 'ladanivatsal@gmail.com', '1111111111', '362001', 'junagadh', 6980.00, 'Processing', NULL, '2026-07-20 23:18:05', 'Card', 'Pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`, `total`) VALUES
(10, 4, 1, 3, 400.00, 1200.00),
(11, 4, 3, 8, 850.00, 6800.00),
(12, 4, 4, 21, 800.00, 16800.00),
(14, 6, 4, 1, 800.00, 800.00),
(15, 6, 6, 1, 3.00, 3.00),
(16, 7, 6, 1, 3680.00, 3680.00),
(20, 8, 6, 4, 3680.00, 14720.00),
(21, 10, 4, 1, 800.00, 800.00),
(22, 11, 3, 1, 850.00, 850.00),
(23, 12, 4, 11, 800.00, 8800.00),
(24, 12, 5, 2, 180.00, 360.00),
(25, 13, 5, 1, 180.00, 180.00),
(26, 13, 2, 3, 460.00, 1380.00),
(27, 13, 1, 2, 400.00, 800.00),
(28, 14, 2, 3, 460.00, 1380.00),
(29, 14, 4, 4, 800.00, 3200.00),
(30, 14, 1, 6, 400.00, 2400.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL COMMENT 'upi, card, netbanking, cod',
  `payment_status` varchar(20) NOT NULL COMMENT 'pending, completed, failed',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_details` text DEFAULT NULL COMMENT 'JSON with method-specific details',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `user_id`, `amount`, `payment_method`, `payment_status`, `transaction_id`, `payment_details`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 3680.00, 'upi', 'Completed', '1', '{\"method\":\"upi\",\"confirmed_by\":\"admin\",\"transaction_id\":\"1\"}', '2025-03-27 18:24:38', '2025-03-28 23:10:29'),
(2, 8, 1, 14720.00, 'upi', 'Completed', '2', '{\"method\":\"upi\",\"confirmed_by\":\"admin\",\"transaction_id\":\"2\"}', '2025-03-28 18:30:16', '2025-03-28 23:10:14'),
(3, 10, 1, 800.00, 'cod', 'Completed', 'COD-e95ffd7d', '{\"method\":\"cod\"}', '2025-03-28 18:55:14', '2025-03-28 23:10:01'),
(4, 11, 2, 850.00, 'cod', 'Completed', 'COD-0b6d8e73', '{\"method\":\"cod\"}', '2025-03-28 19:03:37', '2025-03-28 19:33:53'),
(5, 12, 1, 9160.00, 'upi', 'completed', 'UPI-07E9LBC5', '{\"method\":\"upi\",\"confirmed_by\":\"admin\",\"confirmed_at\":\"2025-03-29T04:13:51.844Z\",\"transaction_id\":\"UPI-07E9LBC5\"}', '2025-03-29 09:43:51', '2025-03-29 09:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(100) NOT NULL,
  `image1` varchar(100) DEFAULT NULL,
  `image2` varchar(100) DEFAULT NULL,
  `image3` varchar(100) DEFAULT NULL,
  `weighting_ml` decimal(10,2) DEFAULT NULL,
  `weighting_kg` decimal(10,2) DEFAULT NULL,
  `weighting_packs` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `quantity`, `category_id`, `image_url`, `image1`, `image2`, `image3`, `weighting_ml`, `weighting_kg`, `weighting_packs`, `created_at`) VALUES
(1, 'Wheat: Triticum aestivum', 'Wheat is a staple cereal grain used to make flour for bread, pasta, and other food products. It is rich in carbohydrates and protein, and grows best in temperate climates.', 400.00, 9, 1, '\\Farming_meds/Uploads/Products/wheat_seeds.jpg', NULL, NULL, NULL, 0.00, 40.00, '0', '2025-03-28 15:09:32'),
(2, 'Rice: Oryza sativa', 'Rice is a staple cereal grain grown primarily in waterlogged conditions. It is the seed of the Oryza sativa plant and comes in varieties like white rice (polished, with the husk removed) and brown rice.', 460.00, 85, 1, '\\Farming_meds/Uploads/Products/paddy.jpg', NULL, NULL, NULL, 0.00, 20.00, '0', '2025-03-28 15:09:32'),
(3, 'Arcon Swaraj Cotton Seeds', 'Arcon Swaraj Cotton Seeds are renowned for their superior quality, offering high germination rates and robust growth. They are engineered to enhance yield potential, making them a preferred choice for farmers seeking reliable and productive cotton cultivation.', 850.00, 232, 1, '\\Farming_meds/Uploads/Products/cotton-seeds-1.webp', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-28 15:09:32'),
(4, 'Cotton seeds BG 2', 'Cotton Seeds BG 2 are high-yield hybrid seeds known for their resistance to pests like bollworms. They ensure robust growth, improved fiber quality, and higher profitability for farmers. Suitable for diverse climates, these seeds enhance productivity with superior germination and resilience.', 800.00, 104, 1, '\\Farming_meds/Uploads/Products/cotton-seeds-2.webp', NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-28 15:09:32'),
(5, 'Sovata® All insects Remover, Essential Organic Powerful Liquid Insecticide', 'Sovata® All Insects Remover is a powerful, organic liquid insecticide designed to eliminate a wide range of insects effectively. Made with essential natural ingredients, it provides a safe and eco-friendly solution for controlling pests in homes, gardens, and agricultural settings. Ideal for removing mosquitoes, flies, ants, cockroaches, and other common insects, this fast-acting formula ensures long-lasting protection while being safe for plants and pets when used as directed.', 180.00, 74, 5, '\\Farming_meds/Uploads/Products/Sovata.jpg', '\\Farming_meds/Uploads/Products/sovata1.jpg', '\\Farming_meds/Uploads/Products/Sovata2.jpg', '\\Farming_meds/Uploads/Products/sovata3.jpg', 100.00, 0.00, '0', '2025-03-28 15:09:32'),
(6, 'Premise Termite Control, Anti Termite Chemical For Construction | Termite Killer Chemical For Long Lasting Control', 'Premise Termite Control is a powerful anti-termite chemical designed for long-lasting protection against termites in construction. It effectively eliminates termites and prevents future infestations, making it ideal for pre-construction and post-construction treatments. This termite killer penetrates deep into the soil, creating a protective barrier that safeguards buildings and structures from termite damage.', 3680.00, 43, 5, '/Farming_meds/Uploads/Products/Premise_Termite_Control.jpg', '/Farming_meds/Uploads/Products/Premise_Termite_Control1.jpg', '/Farming_meds/Uploads/Products/Premise_Termite_Control2.jpg', '/Farming_meds/Uploads/Products/Premise_Termite_Control3.jpg', 1000.00, 0.00, '0', '2025-03-28 15:09:32'),
(7, 'Go Garden Water Soluble Neem Oil for Plant Pest Control - Organic Pesticide for Plants and Flowers use for Plants Insects pesticides', 'Water-Soluble Neem Oil is a natural and effective solution for pest control and plant protection. It is easily mixable with water, making it convenient for agricultural and home garden use. This neem-based formulation acts as an insect repellent, antifungal, and antibacterial agent, helping to protect crops and plants from harmful pests while promoting healthy growth. Safe for the environment, it is an eco-friendly alternative to chemical pesticides.', 149.00, 125, 5, '/Farming_meds/Uploads/Products/Water_Soluble_Neem_Oil.jpg', '/Farming_meds/Uploads/Products/Water_Soluble_Neem_Oil1.jpg', '/Farming_meds/Uploads/Products/Water_Soluble_Neem_Oil2.jpg', '/Farming_meds/Uploads/Products/Water_Soluble_Neem_Oil3.jpg', 250.00, 0.00, '0', '2025-03-28 15:09:32'),
(8, 'Go Garden Trichoderma Bio Fungicide for plants', 'Trichoderma Bio Fungicide is a natural, eco-friendly solution used to control soil-borne fungal diseases such as root rot, damping-off, and wilt. It contains beneficial *Trichoderma* fungi that work by outcompeting harmful pathogens, producing antifungal compounds, and enhancing plant growth. This bio-fungicide promotes healthier root systems, improves nutrient uptake, and strengthens plant immunity. Suitable for agriculture, horticulture, and gardening, it can be applied as a soil drench, seed treatment, or foliar spray. Being safe for beneficial organisms and compatible with organic farming, Trichoderma Bio Fungicide is an effective alternative to chemical fungicides.', 195.00, 300, 6, '/Farming_meds/Uploads/Products/Trichoderma_Bio_Fungicide.jpg', '/Farming_meds/Uploads/Products/Trichoderma_Bio_Fungicide1.jpg', '/Farming_meds/Uploads/Products/Trichoderma_Bio_Fungicide2.jpg', '/Farming_meds/Uploads/Products/Trichoderma_Bio_Fungicide3.jpg', 0.00, 0.00, '250', '2025-03-28 15:09:32'),
(9, 'Syngenta Exsectra For The Control Of Termites 100ml (Pack Of 1)', 'Syngenta Exsectra is a powerful termite control solution designed to protect structures and crops. This 100ml pack effectively eliminates termites, preventing damage with long-lasting action. Ideal for agricultural and residential use, it ensures reliable pest management and enhanced protection.', 265.00, 100, 5, '/Farming_meds/Uploads/Products/Syngenta.jpg', '/Farming_meds/Uploads/Products/Syngenta1.jpg', '/Farming_meds/Uploads/Products/Syngenta3.jpg', '/Farming_meds/Uploads/Products/Syngenta4.jpg', 100.00, 0.00, '', '2025-03-28 19:05:37'),
(10, 'Ugaoo', 'Fertilizer', 1.00, 1, 3, '/Farming_meds/Uploads/Products/Ugaoo_Organic_Vermicompost.jpg', '/Farming_meds/Uploads/Products/Ugaoo_Organi_Vermicompost2.jpg', '/Farming_meds/Uploads/Products/Ugaoo_Organic_Vermicompost3.jpg', '/Farming_meds/Uploads/Products/Ugaoo_Organic_Vermicompost.jpg', 0.00, 20.00, '', '2026-07-21 13:08:15');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `refund_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `expert_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `profession` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `testimonial` text NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `expert_id`, `client_name`, `profession`, `city`, `testimonial`, `rating`, `created_at`) VALUES
(1, 6, 'Uday Boda ', 'Organic Farmer', 'Jamnagar', 'Very Friendly and sweet guy, easy to communicate with. The Advices give to me by the experts was genuine and extremely helpful for me in my journey for the Organic Farming. I Am very thankful for this.', 5, '2025-04-08 12:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `pincode` varchar(6) NOT NULL,
  `profile_photo` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `logged_in` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `address`, `city`, `pincode`, `profile_photo`, `password`, `created_at`, `role`, `logged_in`) VALUES
(1, 'Uday Boda', 'udayboda@gmail.com', '3333333333', 'Jamnagar, Shapar', 'Jamnagar', '361001', '../Uploads/Users/Ch4.jpeg', '$2y$10$OfxwAn.elKrg.D6MleqJ/OVVQON2JNaOYPSl1q6p9A/7.cUesqnSW', '2024-12-22 09:51:06', 'user', 0),
(2, 'Uday Boda', 'admin123@gmail.com', '2222222222', 'Abcd', 'Jamnagar', '361001', '../Uploads/Users/Ch1.jpeg', '$2y$10$OkiGl5vL2pHArzZ/1Ap/5OTMVRNdpLZ0bHsXmNLNIukcJ9DaUD3De', '2024-12-22 09:56:20', 'user', 0),
(3, 'ladani vatsal', 'ladanivatsal@gmail.com', '1111111111', 'junagadh', 'junagadh', '362001', '../Uploads/Users/ch11.jpeg', '$2a$12$U403QSPoYpoZuZa5ytppLOk8r698YJGqdJiDwKio/WEDzj2VN9P8W', '2025-04-25 10:13:15', 'user', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expert_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorites`
--

INSERT INTO `user_favorites` (`id`, `user_id`, `expert_id`, `created_at`) VALUES
(4, 1, 2, '2025-04-08 20:23:22'),
(5, 1, 6, '2025-04-08 20:23:27'),
(6, 1, 5, '2025-04-08 20:23:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cancel_orders`
--
ALTER TABLE `cancel_orders`
  ADD PRIMARY KEY (`cancellation_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expert_id` (`expert_id`);

--
-- Indexes for table `contactus`
--
ALTER TABLE `contactus`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `email_log`
--
ALTER TABLE `email_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `experts`
--
ALTER TABLE `experts`
  ADD PRIMARY KEY (`expert_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`refund_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expert_id` (`expert_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_expert_unique` (`user_id`,`expert_id`),
  ADD KEY `fk_favorite_expert` (`expert_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cancel_orders`
--
ALTER TABLE `cancel_orders`
  MODIFY `cancellation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contactus`
--
ALTER TABLE `contactus`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_log`
--
ALTER TABLE `email_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `experts`
--
ALTER TABLE `experts`
  MODIFY `expert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`expert_id`);

--
-- Constraints for table `contactus`
--
ALTER TABLE `contactus`
  ADD CONSTRAINT `contactus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `email_log`
--
ALTER TABLE `email_log`
  ADD CONSTRAINT `email_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`expert_id`);

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `fk_favorite_expert` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`expert_id`),
  ADD CONSTRAINT `fk_favorite_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
