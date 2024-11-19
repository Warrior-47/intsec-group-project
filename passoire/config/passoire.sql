-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Oct 07, 2024 at 05:50 PM
-- Server version: 5.7.44
-- PHP Version: 8.2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `passoire`
--

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `ownerid` int(11) NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `type`, `ownerid`, `date`, `path`) VALUES
(1, 'image/svg+xml', 1, '2024-09-19 15:59:05', 'uploads/Tux.svg'),
(2, 'application/octet-stream', 1, '2024-10-07 20:15:36', 'uploads/encrypted'),
(3, 'application/octet-stream', 2, '2024-10-07 20:17:54', 'uploads/secret');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `fileid` int(11) NOT NULL,
  `secret` bigint(20) NOT NULL,
  `hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`fileid`, `secret`, `hash`) VALUES
(1, 0, '325564ec67ffa043539b4d451bc132649f16f3c3'),
(2, 0, '0654a0823c2015b72407132de1940cbf94404434'),
(3, 0, '38282ca5e65e63746498ef5b39a2fe71c6448df4');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `authorid` int(11) NOT NULL,
  `content` text NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `authorid`, `content`, `date`) VALUES
(1, 1, 'Hello everyone, this is John! Excited to join this platform.', '2024-09-17 09:15:00'),
(2, 2, 'Hey, this is Jane! Looking forward to connecting with you all!', '2024-09-17 10:00:00'),
(3, 1, 'Hey Jane, have you thought about how critical it is to store passwords securely?', '2024-09-18 15:32:22'),
(4, 1, 'I mean, using simple hash functions like MD5 or SHA-1 is really not enough.', '2024-09-18 15:32:22'),
(5, 1, 'It\'s important to use algorithms specifically designed for password hashing, like Bcrypt or Argon2.', '2024-09-18 15:32:22'),
(6, 1, 'The key is to use a slow hashing algorithm to make brute-force attacks more difficult.', '2024-09-18 15:32:22'),
(7, 1, 'Also, we should always use a unique salt for each password to prevent rainbow table attacks.', '2024-09-18 15:32:22'),
(8, 1, 'The salt ensures that even if two users have the same password, their hashes will be different.', '2024-09-18 15:32:22'),
(9, 1, 'Do you know if our system is using a secure hashing algorithm?', '2024-09-18 15:32:22'),
(10, 1, 'Another thing to consider is the key stretching technique to further enhance security.', '2024-09-18 15:32:22'),
(11, 1, 'I think it would be useful to review our current password storage practices.', '2024-09-18 15:32:22'),
(12, 1, 'Regularly updating our hashing algorithm is also a good practice to stay ahead of potential threats.', '2024-09-18 15:32:22'),
(13, 2, 'Hi John, yes, I agree. I think Bcrypt is a solid choice for hashing passwords.', '2024-09-18 15:32:22'),
(14, 2, 'We need to make sure that our hashing function is resistant to brute-force attacks and has built-in salting.', '2024-09-18 15:32:22'),
(15, 2, 'I\'ve heard good things about Argon2 as well. It\'s a newer algorithm and provides even more security.', '2024-09-18 15:32:22'),
(16, 2, 'It\'s also crucial to enforce strong password policies to complement our hashing strategy.', '2024-09-18 15:32:22'),
(17, 2, 'Using multi-factor authentication can further protect user accounts from unauthorized access.', '2024-09-18 15:32:22'),
(18, 2, 'We should also consider implementing rate limiting to prevent excessive login attempts.', '2024-09-18 15:32:22'),
(19, 2, 'Do you have any suggestions on how we can test our current hashing implementation?', '2024-09-18 15:32:22'),
(20, 2, 'Conducting security audits and penetration testing might help us identify any vulnerabilities.', '2024-09-18 15:32:22'),
(21, 2, 'I\'m also interested in learning about recent advancements in password hashing techniques.', '2024-09-18 15:32:22'),
(22, 2, 'Ensuring that we stay informed about new security practices is key to maintaining a secure system.', '2024-09-18 15:32:22'),
(23, 1, 'Great points, Jane. I\'ll look into Argon2 and see if it fits our needs.', '2024-09-18 15:32:22'),
(24, 1, 'For testing our hashing implementation, we can use tools designed for password cracking to see how resistant it is.', '2024-09-18 15:32:22'),
(25, 1, 'It\'s essential to keep up with security research and adjust our practices as necessary.', '2024-09-18 15:32:22'),
(26, 1, 'I\'m also considering implementing a password manager for our team to ensure that we use unique passwords everywhere.', '2024-09-18 15:32:22'),
(27, 1, 'We could also explore options for encrypting passwords in transit to add another layer of security.', '2024-09-18 15:32:22'),
(28, 1, 'Let\'s schedule a meeting to discuss these improvements and set up a plan for implementation.', '2024-09-18 15:32:22'),
(29, 1, 'I\'ll start researching the latest recommendations for password storage and get back to you.', '2024-09-18 15:32:22'),
(30, 1, 'In the meantime, let\'s review our existing documentation to ensure that it\'s up to date.', '2024-09-18 15:32:22'),
(31, 1, 'Thanks for the insights, Jane. I\'m looking forward to making our system more secure.', '2024-09-18 15:32:22'),
(32, 1, 'Let\'s keep this discussion going and continuously improve our security practices.', '2024-09-18 15:32:22'),
(33, 2, 'Sounds like a good plan, John. I\'ll help with researching Argon2 and the latest best practices.', '2024-09-18 15:32:22'),
(34, 2, 'I agree, regular reviews and updates are crucial for maintaining security.', '2024-09-18 15:32:22'),
(35, 2, 'Implementing encryption for passwords in transit is definitely something we should consider.', '2024-09-18 15:32:22'),
(36, 2, 'I\'ll also start working on the documentation and ensure that all our security practices are well-documented.', '2024-09-18 15:32:22'),
(37, 2, 'Looking forward to our meeting and discussing these improvements further.', '2024-09-18 15:32:22'),
(38, 2, 'Thanks for initiating this conversation. It\'s great to collaborate on enhancing our security.', '2024-09-18 15:32:22'),
(39, 2, 'Let\'s stay proactive and keep up with new developments in cybersecurity.', '2024-09-18 15:32:22'),
(40, 2, 'We should aim to build a robust security culture within our team.', '2024-09-18 15:32:22'),
(41, 2, 'Thanks for your efforts in making our system more secure, John.', '2024-09-18 15:32:22'),
(42, 2, 'Let\'s ensure that our implementation is both effective and user-friendly.', '2024-09-18 15:32:22'),
(43, 2, 'I have a file named secret, do you think it\'s safe?', '2024-10-07 20:19:26'),
(44, 1, 'As long as you don\'t post the link, no one should be able to access it but you, don\'t you think?', '2024-10-07 20:20:16'),
(45, 1, 'Here is a file for you, it\'s encrypted with an algorithm of my own invention. I sent you the key by text message. Here is the <a href=\"link.php?file=0654a0823c2015b72407132de1940cbf94404434\">link </a>.', '2024-10-07 20:22:28');

-- --------------------------------------------------------

--
-- Table structure for table `userinfos`
--

CREATE TABLE `userinfos` (
  `userid` int(11) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `bio` text,
  `avatar` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `userinfos`
--

INSERT INTO `userinfos` (`userid`, `birthdate`, `location`, `bio`, `avatar`) VALUES
(1, '1991-05-15', 'New York, USA', 'John loves coding and hiking.', 'uploads/avatar_1.png'),
(2, '1985-08-22', 'London, UK', 'Jane is a photographer and traveler.', 'img/avatar6.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pwhash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `email`, `pwhash`) VALUES
(1, 'john_doe', 'john@example.com', '7c4a8d09ca3762af61e59520943dc26494f8941b'),
(2, 'jane_smith', 'jane@example.com', '7c222fb2927d828af22f592134e8932480637c0d'),
(3, 'flag_5', 'see-password-hash@that-is-the-fl.ag', 'afac709479ebe7085d08a825d2d347b158c80752'),
(4, 'admin', 'admin@example.com', '6e1a438cfe5a6c9e2165665f8c2258849ccc43f0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ownerid` (`ownerid`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`fileid`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `authorid` (`authorid`);

--
-- Indexes for table `userinfos`
--
ALTER TABLE `userinfos`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`ownerid`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`fileid`) REFERENCES `files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`authorid`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `userinfos`
--
ALTER TABLE `userinfos`
  ADD CONSTRAINT `userinfos_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
