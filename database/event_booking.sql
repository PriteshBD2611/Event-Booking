-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 06:24 PM
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
-- Database: `connect_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `seat_number` int(11) DEFAULT NULL,
  `payment_status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `seat_number`, `payment_status`) VALUES
(7, 3, 22, 14, 'Paid'),
(8, 3, 19, 13, 'Paid'),
(9, 3, 18, 13, 'Paid'),
(10, 3, 16, 2, 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `speaker` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `location_url` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `image_path`, `description`, `speaker`, `category`, `location_url`, `price`, `event_date`, `created_by`) VALUES
(6, 'F&O Trading', 'uploads/1769185349_download.jpg', 'The Futures & Options (F&O) Trading Seminar is designed to provide participants with a structured and practical understanding of derivative markets. This seminar focuses on building strong foundational knowledge while introducing real-world trading concepts used in professional financial markets.', 'Abhishek Sharma', 'Stock Market', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3774.031742286266!2d72.83083307464666!3d18.92998908224474!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7d1c363d83329%3A0xc3516cb22743963b!2sBombay%20Stock%20Exchange!5e0!3m2!1sen!2sin!4v1769185193664!5m2!1sen!2sin\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 100.00, '2026-03-12 14:00:00', 5),
(7, 'Stock Market Information', 'uploads/1769185584_download (1).jpg', 'The Stock Trading Seminar is designed to introduce participants to the fundamentals of equity markets and the principles of successful stock trading. This seminar provides a clear understanding of how stock markets operate, including market structure, price movements, and the factors that influence stock performance.', 'Rakesh Jhunjunwala', 'Stock Market', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3774.031742286266!2d72.83083307464666!3d18.92998908224474!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7d1c363d83329%3A0xc3516cb22743963b!2sBombay%20Stock%20Exchange!5e0!3m2!1sen!2sin!4v1769185193664!5m2!1sen!2sin\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 2000.00, '2026-03-14 12:00:00', 5),
(8, 'Forex Trading Seminar', 'uploads/1769185834_download (2).jpg', 'The Forex Trading Seminar, led by Rayner Teo, a globally recognized Forex trader and trading educator, is designed to provide participants with practical and structured knowledge of the foreign exchange market. Known for his clear teaching style and disciplined trading approach, Rayner Teo brings real-world market experience into this educational session.\r\n\r\nThis seminar covers essential Forex concepts, including currency pairs, market structure, technical analysis, price action, risk management, and trading psychology. Participants will gain insights into how professional traders analyze markets, manage risk, and maintain consistency in trading decisions.\r\n\r\nThe seminar is ideal for students, beginners, and aspiring Forex traders who want to learn from an experienced and respected figure in the trading community. By attending this session, participants will benefit from expert guidance, practical examples, and a professional mindset toward Forex trading.', 'Rayner Teo', 'Stock Market', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3774.031742286266!2d72.83083307464666!3d18.92998908224474!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7d1c363d83329%3A0xc3516cb22743963b!2sBombay%20Stock%20Exchange!5e0!3m2!1sen!2sin!4v1769185193664!5m2!1sen!2sin\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 5000.00, '2026-05-14 14:00:00', 5),
(9, 'Cryptocurrency Trading Seminar with Andreas M. Antonopoulos', 'uploads/1769186453_download (3).jpg', 'The Cryptocurrency Trading Seminar, led by Andreas M. Antonopoulos, a globally respected blockchain expert, author, and educator, is designed to provide participants with a deep and structured understanding of digital asset markets. Known for his expertise in Bitcoin, blockchain technology, and decentralized finance, Andreas brings clarity to one of the most rapidly evolving financial sectors.', 'Andreas M. Antonopoulos', 'Crypto', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Jio+World+Convention+Centre,+G+Block,+Bandra+Kurla+Complex,+Mumbai,+Maharashtra+400051&output=embed\">\r\n</iframe>', 1000.00, '2026-02-13 12:30:00', 5),
(10, 'Bitcoin-Only Seminar with Andreas M. Antonopoulos', 'uploads/1769186605_photo.avif', 'The Bitcoin-Only Seminar, led by Andreas M. Antonopoulos, a globally recognized Bitcoin educator and author, is designed to provide participants with a focused and in-depth understanding of Bitcoin as a decentralized monetary system. This seminar emphasizes Bitcoin’s technology, philosophy, and real-world use cases rather than general cryptocurrency speculation.', 'Andreas M. Antonopoulos', 'Crypto', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Bharat+Mandapam,+Pragati+Maidan,+New+Delhi,+Delhi+110001&output=embed\">\r\n</iframe>', 2000.00, '2026-02-18 15:00:00', 5),
(11, 'DeFi & Web3 Seminar with Vitalik Buterin', 'uploads/1769186735_images.jpg', 'The DeFi & Web3 Seminar, led by Vitalik Buterin, co-founder of Ethereum and a globally recognized thought leader in decentralized technologies, is designed to introduce participants to the evolving world of decentralized finance and Web3 ecosystems.', 'Vitalik Buterin', 'Crypto', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Yashobhoomi,+Sector+25,+Dwarka,+New+Delhi,+Delhi+110077&output=embed\">\r\n</iframe>', 5000.00, '2026-02-20 15:00:00', 5),
(12, 'Real Estate Investment Seminar with Grant Cardone', 'uploads/1769186881_download (4).jpg', 'The Real Estate Investment Seminar, led by Grant Cardone, an internationally recognized real estate investor and entrepreneur, is designed to provide participants with practical knowledge of real estate markets and investment strategies. This seminar focuses on understanding property investment as a long-term wealth-building tool.', 'Grant Cardone', 'Real Estate', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=India+Habitat+Centre,+Lodhi+Road,+New+Delhi,+Delhi+110003&output=embed\">\r\n</iframe>', 0.00, '2026-03-30 12:00:00', 5),
(13, 'Real Estate Market Analysis Seminar with Robert Kiyosaki', 'uploads/1769186987_download (5).jpg', 'The Real Estate Market Analysis Seminar, led by Robert Kiyosaki, bestselling author of Rich Dad Poor Dad and a renowned real estate investor, is designed to help participants understand real estate as a powerful asset class for long-term financial growth.', 'Robert Kiyosaki', 'Real Estate', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Bangalore+International+Exhibition+Centre,+10th+Mile,+Tumkur+Road,+Bengaluru,+Karnataka+562123&output=embed\">\r\n</iframe>', 0.00, '2026-03-15 11:00:00', 5),
(14, 'Modern Banking & Financial Systems Seminar with Raghuram Rajan', 'uploads/1769187170_download (6).jpg', 'The Modern Banking & Financial Systems Seminar, led by Dr. Raghuram Rajan, former Governor of the Reserve Bank of India and a globally respected economist, is designed to provide participants with a comprehensive understanding of the modern banking ecosystem.', 'Raghuram Rajan', 'Banking', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Jio+World+Convention+Centre,+G+Block,+Bandra+Kurla+Complex,+Mumbai,+Maharashtra+400051&output=embed\">\r\n</iframe>', 0.00, '2026-02-22 13:00:00', 5),
(15, 'Corporate Finance & Financial Planning Seminar with Aswath Damodaran', 'uploads/1769187374_download (7).jpg', 'The Corporate Finance & Financial Planning Seminar, led by Aswath Damodaran, a globally respected finance professor and valuation expert, is designed to provide participants with a clear and practical understanding of modern finance principles used in corporate and investment decision-making.', ' Aswath Damodara', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=IMC+Chamber+of+Commerce+and+Industry,+IMC+Marg,+Churchgate,+Mumbai,+Maharashtra+400020&output=embed\">\r\n</iframe>', 200.00, '2026-03-30 12:30:00', 5),
(16, 'Computer Networking & Network Security Seminar with Andrew S. Tanenbaum', 'uploads/1769187545_images (1).jpg', 'The Computer Networking & Network Security Seminar, led by Andrew S. Tanenbaum, a renowned computer scientist and author of Computer Networks, is designed to provide participants with a strong foundation in modern networking concepts and infrastructure.\r\n\r\nThis seminar covers core topics such as network architectures, TCP/IP models, routing and switching fundamentals, network security principles, and real-world networking applications. Participants will gain insights into how data is transmitted across networks, how large-scale systems operate, and how network reliability and security are maintained.', ' Andrew S. Tanenbaum', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Taj+Lands+End,+Byramji+Jeejeebhoy+Road,+Bandstand,+Bandra+West,+Mumbai,+Maharashtra+400050&output=embed\">\r\n</iframe>', 500.00, '2926-03-03 12:00:00', 5),
(17, 'Cloud Networking & Infrastructure Seminar with Werner Vogels', 'uploads/1769187635_download (8).jpg', 'The Cloud Networking & Infrastructure Seminar, led by Dr. Werner Vogels, Chief Technology Officer of Amazon and a globally recognized cloud computing expert, is designed to provide participants with a clear understanding of networking in cloud-based environments.', 'Werner Vogels', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=ITC+Grand+Chola,+63,+Anna+Salai,+Guindy,+Chennai,+Tamil+Nadu+600032&output=embed\">\r\n</iframe>', 2000.00, '2026-04-22 13:00:00', 5),
(18, 'Cybersecurity-Focused Networking Seminar with Bruce Schneier', 'uploads/1769187747_images (2).jpg', 'The Cybersecurity-Focused Networking Seminar, led by Bruce Schneier, a globally respected cybersecurity expert and author, is designed to provide participants with a deep understanding of securing modern network infrastructures against evolving cyber threats.', ' Bruce Schneier', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=IMC+Chamber+of+Commerce+and+Industry,+IMC+Marg,+Churchgate,+Mumbai,+Maharashtra+400020&output=embed\">\r\n</iframe>', 2000.00, '2026-05-22 13:00:00', 5),
(19, 'Emerging Technologies & Innovation Seminar with Sundar Pichai', 'uploads/1769187864_download (9).jpg', 'The Emerging Technologies & Innovation Seminar, led by Sundar Pichai, CEO of Google and Alphabet, is designed to provide participants with insights into how modern technologies are shaping industries and everyday life.', 'Sundar Pichai', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Bangalore+International+Exhibition+Centre,+10th+Mile,+Tumkur+Road,+Bengaluru,+Karnataka+562123&output=embed\">\r\n</iframe>', 1000.00, '2026-05-12 13:00:00', 5),
(20, 'Artificial Intelligence & Machine Learning Seminar with Andrew Ng', 'uploads/1769187989_download (10).jpg', 'The Artificial Intelligence & Machine Learning Seminar, led by Andrew Ng, a globally renowned AI researcher, educator, and co-founder of Coursera, is designed to provide participants with a clear and practical understanding of AI and machine learning technologies shaping the modern world.', 'Andrew Ng', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Hyderabad+International+Convention+Centre,+Novotel+%26+HICC+Complex,+Hyderabad,+Telangana+500081&output=embed\">\r\n</iframe>', 4000.00, '2026-05-26 12:30:00', 5),
(21, 'Data Science & Analytics Seminar with DJ Patil', 'uploads/1769188124_download (11).jpg', 'The Data Science & Analytics Seminar, led by DJ Patil, former Chief Data Scientist of the United States and a pioneer in the field of data science, is designed to provide participants with a practical and conceptual understanding of data-driven decision-making.', 'DJ Patil', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Yashobhoomi,+Sector+25,+Dwarka,+New+Delhi,+Delhi+110077&output=embed\">\r\n</iframe>', 4000.00, '2026-05-05 14:00:00', 5),
(22, 'Robotics & Intelligent Systems Seminar with Rodney Brooks', 'uploads/1769188223_download (12).jpg', 'The Robotics & Intelligent Systems Seminar, led by Rodney Brooks, a renowned roboticist and co-founder of iRobot and Rethink Robotics, is designed to introduce participants to the fundamentals and real-world applications of robotics and intelligent systems.\r\n\r\nThis seminar covers key topics such as robotic sensors and actuators, autonomous systems, artificial intelligence in robotics, human–robot interaction, and industrial and service robotics. Participants will gain insights into how intelligent machines are designed, programmed, and deployed across various industries.\r\n\r\nThis seminar is ideal for students, engineering enthusiasts, and technology professionals who want to explore the rapidly evolving field of robotics. By the end of the seminar, attendees will have a clear understanding of robotic systems and their impact on automation and innovation.', 'Rodney Brooks', 'Technology', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Jio+World+Convention+Centre,+G+Block,+Bandra+Kurla+Complex,+Mumbai,+Maharashtra+400051&output=embed\">\r\n</iframe>', 5000.00, '2026-05-02 12:00:00', 5),
(23, 'Career Guidance & Personal Development Seminar with Sandeep Maheshwari', 'uploads/1769188388_images (3).jpg', 'The Career Guidance & Personal Development Seminar, led by Sandeep Maheshwari, a renowned motivational speaker and entrepreneur, is designed to help students and young professionals gain clarity, confidence, and direction in their career journey.', ' Sandeep Maheshwari', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Jio+World+Convention+Centre,+G+Block,+Bandra+Kurla+Complex,+Mumbai,+Maharashtra+400051&output=embed\">\r\n</iframe>', 0.00, '2026-05-26 16:00:00', 5),
(24, 'Startup Networking & Entrepreneurship Seminar with Naval Ravikant', 'uploads/1769298622_images.jpg', 'The Startup Networking & Entrepreneurship Seminar, led by Naval Ravikant, entrepreneur, angel investor, and co-founder of AngelList, is designed to help aspiring founders, innovators, and professionals understand the power of networking in the startup ecosystem.', 'Naval Ravikant', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Jio+World+Convention+Centre,+G+Block,+Bandra+Kurla+Complex,+Mumbai,+Maharashtra+400051&output=embed\">\r\n</iframe>', 0.00, '2026-05-01 11:11:00', 5),
(25, 'Founder & Investor Meetup with Nikhil Kamath', 'uploads/1769298826_download.jpg', 'The Founder & Investor Meetup, featuring Nikhil Kamath, co-founder of Zerodha and a prominent Indian entrepreneur and investor, is an exclusive networking event designed to connect startup founders with investors, mentors, and industry leaders.', 'Nikhil Kamath', 'Stock Market', '<iframe \r\n  width=\"100%\" \r\n  height=\"450\" \r\n  frameborder=\"0\" \r\n  scrolling=\"no\" \r\n  marginheight=\"0\" \r\n  marginwidth=\"0\" \r\n  src=\"https://maps.google.com/maps?q=Yashobhoomi,+Sector+25,+Dwarka,+New+Delhi,+Delhi+110077&output=embed\">\r\n</iframe>', 0.00, '2026-06-01 15:00:00', 5);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'pritesh', 'noharashinchan588@gmail.com', '$2y$10$1TQrtteqpSA8x/Ru4yCsJe21tDEje.qDNSzMfnLJNDtmHjZAvO/MG', 'user'),
(2, 'admin', 'diwalepritesh@gmail.com', '$2y$10$RzEwYn3s0VbmdmE/Uj2Y3OPbH9uIlIAmyHLsf5YdPNbzg9TOU39Ei', 'admin'),
(3, 'User', 'user@example.com', '$2y$10$xem0hX1c6ZxTiiB0kvbq3ew7VHLnSHNOYJcPcnCs8TcWDiXJaUVPC', 'user'),
(5, 'admin', 'admin@example.com', '$2y$10$AbXgTfLs0I8wVeSXB2ekxe1Qa7fOKEPs0rP8bAeZxhdkABdfMOvGC', 'admin'),
(6, 'kaushik patil', 'kaushikpail0505@gmail.com', '$2y$10$1.by2.26F9mWzHd5C.ipx.lWAgctjupvrd18EMuTIZdN6/vzDlE6q', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
