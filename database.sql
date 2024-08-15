DROP DATABASE IF EXISTS tagihan_nusput;
CREATE DATABASE tagihan_nusput;
USE tagihan_nusput;

CREATE TABLE `administrations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `admin_code` VARCHAR(30) UNIQUE NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `created_at` timestamp DEFAULT NOW()
);

CREATE TABLE `levels` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(5) UNIQUE NOT NULL,
  `monthly_bills` decimal NOT NULL,
  `late_bills` decimal NOT NULL
);

INSERT INTO `levels` (`name`, `monthly_bills`, `late_bills`) VALUES
('PG', 50000, 5000),
('TKA', 65000, 5000),
('TKB', 75000, 5000),
('SD', 100000, 10000),
('SMP', 150000, 10000),
('SMA', 165000, 10000),
('ADMIN', 0, 0);

CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nis` VARCHAR(10) UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `status` VARCHAR(10) NOT NULL,
  `level` int NOT NULL,
  `phone_number` VARCHAR(16) DEFAULT NULL,
  `email_address` VARCHAR(255) DEFAULT NULL,
  `parent_phone` VARCHAR(16) NOT NULL,
  `latest_bill` datetime DEFAULT NULL,
  `virtual_account` VARCHAR(16) NOT NULL,
  `period` VARCHAR(9) NOT NULL,
  `semester` VARCHAR(5) NOT NULL,
  `password` VARCHAR(255) NOT NULL
);

CREATE TABLE `bills` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nis` VARCHAR(10) NOT NULL,
  `trx_id` VARCHAR(20),
  `virtual_account` VARCHAR(16) NOT NULL,
  `student_name` VARCHAR(255) NOT NULL,
  `parent_phone` VARCHAR(16) NOT NULL,
  `student_phone` VARCHAR(16) DEFAULT NULL,
  `student_email` VARCHAR(255) DEFAULT NULL,
  `trx_amount` decimal NOT NULL,
  `trx_status` VARCHAR(15) NOT NULL,
  `late_bills` decimal DEFAULT 0,
  `description` text DEFAULT NULL,
  `level` VARCHAR(5) NOT NULL,
  `period` VARCHAR(9) NOT NULL,
  `semester` VARCHAR(5) NOT NULL,
  `payment_due` timestamp NOT NULL
);

CREATE TABLE `payments` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `sender` int NOT NULL,
  `virtual_account` VARCHAR(16) NOT NULL,
  `bill_id` int NOT NULL,
  `trx_id` VARCHAR(20) NOT NULL,
  `trx_amount` decimal NOT NULL,
  `notes` text DEFAULT NULL,
  `trx_timestamp` timestamp DEFAULT NOW()
);

ALTER TABLE `users` ADD FOREIGN KEY (`level`) REFERENCES `levels` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`sender`) REFERENCES `users` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

INSERT INTO `users` (
  `nis`, `name`, `status`, 
  `level`, `phone_number`, `email_address`, 
  `parent_phone`, `latest_bill`, `virtual_account`, 
  `period`, `semester`, `password`) VALUES
('0000', 'admin', 'active', 7, '000000000000', 'admin@nusput.com', '000000000000', NULL, 'admin', '2023/2024', 'Gasal', '$2y$10$nzDoKrPD37M3E3xivsR7H.K6W4o1q28L3T11aB6ia3EVtxbc2tTsu'),
('5048', 'Angel Ravelynta', 'active', 6, NULL, NULL, '087731335955', NULL, '9881105622235048', '2024/2025', 'Gasal', '$2y$10$nzDoKrPD37M3E3xivsR7H.K6W4o1q28L3T11aB6ia3EVtxbc2tTsu'),
('5049', 'Wira Anggara', 'active', 6, NULL, NULL, '081329171920', NULL, '9881105622235049', '2024/2025', 'Gasal', '$2y$10$nzDoKrPD37M3E3xivsR7H.K6W4o1q28L3T11aB6ia3EVtxbc2tTsu');

INSERT INTO `bills` (`nis`, `trx_id`, `virtual_account`, `student_name`, `parent_phone`, `student_phone`, `student_email`, `trx_amount`, `trx_status`, `late_bills`, `description`, `level`, `period`, `semester`, `payment_due`) VALUES
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Gasal', '2023-07-31 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'late', 0, '', 'SMA', '2023/2024', 'Gasal', '2023-08-31 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'late', 0, '', 'SMA', '2023/2024', 'Gasal', '2023-09-29 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'late', 0, '', 'SMA', '2023/2024', 'Gasal', '2023-10-31 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Gasal', '2023-11-30 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Gasal', '2023-12-29 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Genap', '2024-01-31 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Genap', '2024-02-29 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'late', 0, '', 'SMA', '2023/2024', 'Genap', '2024-03-29 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Genap', '2024-04-30 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'paid', 0, '', 'SMA', '2023/2024', 'Genap', '2024-05-31 23:59:59'),
('5048', 'SMA/11/5/1/5048', '9881105622235048', 'Angel Ravelynta', '087731335955', NULL, NULL, 165000, 'not paid', 10000, '', 'SMA', '2023/2024', 'Genap', '2024-06-28 23:59:59');


INSERT INTO `payments` (`sender`, `virtual_account`, `bill_id`, `trx_id`, `trx_amount`, `trx_timestamp`) VALUES
(2, '9881105622235048', 1, 'SMA/11/5/1/5048', 165000, '2023-06-14 12:32:10'),
(2, '9881105622235048', 5, 'SMA/11/5/1/5048', 690000, '2023-11-16 15:53:16'),
(2, '9881105622235048', 6, 'SMA/11/5/1/5048', 165000, '2023-12-12 11:25:58'),
(2, '9881105622235048', 7, 'SMA/11/5/1/5048', 165000, '2024-01-24 10:52:13'),
(2, '9881105622235048', 8, 'SMA/11/5/1/5048', 165000, '2024-02-15 18:35:17'),
(2, '9881105622235048', 10, 'SMA/11/5/1/5048', 340000, '2024-04-27 20:34:36'),
(2, '9881105622235048', 11, 'SMA/11/5/1/5048', 165000, '2024-05-23 19:27:48');