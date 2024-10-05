DROP DATABASE IF EXISTS tagihan_nusput;
CREATE DATABASE tagihan_nusput;
USE tagihan_nusput;

CREATE TABLE `administrations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `admin_code` VARCHAR(50) UNIQUE NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `created_at` datetime DEFAULT NOW()
);

CREATE TABLE `activity_log` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `activity_by` VARCHAR(255) NOT NULL,
  `activity` VARCHAR(255) NOT NULL,
  `created_at` datetime DEFAULT NOW()
);

CREATE TABLE `additional_payment_category` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `category_name` VARCHAR(255) NOT NULL
);

INSERT INTO `additional_payment_category` (`category_name`) VALUES
('Uang Praktik'),
('Uang Ekstra'),
('Daycare');

CREATE TABLE `classes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `level` VARCHAR(5) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `major` VARCHAR(50),
  `monthly_bills` decimal NOT NULL,
  `late_bills` decimal NOT NULL,
  `va_identifier` VARCHAR(4) NOT NULL,
  `va_prefix_name` VARCHAR(20) NOT NULL
);

INSERT INTO classes (name, major, level, monthly_bills, late_bills, va_identifier, va_prefix_name) VALUES
('PG', NULL, 'TK', 650000, 10000, '2220', 'SPP TK '),
('A' , NULL, 'TK', 650000, 10000, '2220', 'SPP TK '),
('B' , NULL, 'TK', 650000, 10000, '2220', 'SPP TK '),
('A' , 'Excel', 'TK', 900000, 10000, '2220', 'SPP TK '),
('B' , 'Excel', 'TK', 950000, 10000, '2220', 'SPP TK '),
('1' , NULL, 'SD', 650000, 10000, '2221', 'SPP SD '),
('2' , NULL, 'SD', 650000, 10000, '2221', 'SPP SD '),
('3' , NULL, 'SD', 655000, 10000, '2221', 'SPP SD '),
('4' , 'A', 'SD', 485000, 10000, '2221', 'SPP SD '),
('4' , 'B', 'SD', 485000, 10000, '2221', 'SPP SD '),
('5' , 'A', 'SD', 385000, 10000, '2221', 'SPP SD '),
('5' , 'B', 'SD', 385000, 10000, '2221', 'SPP SD '),
('6' , NULL, 'SD', 562000, 10000, '2221', 'SPP SD '),
('7' , 'A', 'SMP', 575000, 10000, '2222', 'SPP SMP '),
('7' , 'B', 'SMP', 575000, 10000, '2222', 'SPP SMP '),
('8' , NULL, 'SMP', 540000, 10000, '2222', 'SPP SMP '),
('9' , 'A', 'SMP', 835000, 10000, '2222', 'SPP SMP '),
('9' , 'B', 'SMP', 835000, 10000, '2222', 'SPP SMP '),
('X' , '1', 'SMA', 700000, 10000, '2223', 'SPP SMA '),
('X' , '2', 'SMA', 700000, 10000, '2223', 'SPP SMA '),
('XI', '1', 'SMA', 665000, 10000, '2223', 'SPP SMA '),
('XI', '2', 'SMA', 665000, 10000, '2223', 'SPP SMA '),
('XI', 'Excel', 'SMA', 950000, 10000, '2223', 'SPP SMA '),
('XII','IPS', 'SMA', 866000, 10000, '2223', 'SPP SMA '),
('XII','IPA', 'SMA', 776000, 10000, '2223', 'SPP SMA '),
('XII','EXCEL', 'SMA', 1226000, 10000, '2223', 'SPP SMA '),
('X MM','A', 'SMK 1', 520000, 10000, '2224', 'SPP SMK1 '),
('X MM','B', 'SMK 1', 520000, 10000, '2224', 'SPP SMK1 '),
('XI MM','A', 'SMK 1 ', 520000, 10000, '2224', 'SPP SMK1 '),
('XI MM','B', 'SMK 1 ', 520000, 10000, '2224', 'SPP SMK1 '),
('XII MM',NULL, 'SMK 1', 694000, 10000, '2224', 'SPP SMK1 '),
('X TKJ',NULL, 'SMK 1 ', 610000, 10000, '2224', 'SPP SMK1 '),
('XI TKJ',NULL, 'SMK 1', 625000, 10000, '2224', 'SPP SMK1 '),
('XII TKJ',NULL, 'SMK 1', 820000, 10000, '2224', 'SPP SMK1 ');

CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nis` VARCHAR(10) UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `birthdate` DATE DEFAULT NULL,
  `status` VARCHAR(10) NOT NULL,
  `class` int NOT NULL,
  `additional_fee_details` text DEFAULT NULL, -- JSON
  `phone_number` VARCHAR(16) DEFAULT NULL,
  `email_address` VARCHAR(255) DEFAULT NULL,
  `parent_phone` VARCHAR(16) NOT NULL,
  `virtual_account` VARCHAR(16) NOT NULL,
  `period` VARCHAR(9) NOT NULL,
  `semester` VARCHAR(5) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(10) DEFAULT 'STUDENT'
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
  `additional_fee_details` text DEFAULT NULL,  
  `additional_fee_amount` decimal DEFAULT 0,  
  `trx_status` VARCHAR(15) NOT NULL,
  `late_bills` decimal DEFAULT 0,
  `stored_late_bills` decimal DEFAULT 0,
  `description` text DEFAULT NULL,
  `class` INT NOT NULL,
  `period` VARCHAR(9) NOT NULL,
  `semester` VARCHAR(5) NOT NULL,
  `payment_due` datetime NOT NULL,
  `bill_disabled` datetime DEFAULT NULL, 
  `midtrans_trx_id` VARCHAR(50)
);

CREATE TABLE `payments` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `sender` int NOT NULL,
  `virtual_account` VARCHAR(16) NOT NULL,
  `bill_id` int NOT NULL,
  `trx_id` VARCHAR(20) NOT NULL,
  `trx_amount` decimal NOT NULL,
  `notes` text DEFAULT NULL,
  `trx_timestamp` datetime DEFAULT NOW()
);


CREATE TABLE `student_history` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nis` VARCHAR(10) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `class` int NOT NULL,
  `phone_number` VARCHAR(16) DEFAULT NULL,
  `email_address` VARCHAR(255) DEFAULT NULL,
  `parent_phone` VARCHAR(16) NOT NULL,
  `virtual_account` VARCHAR(16) NOT NULL,
  `period` VARCHAR(9) NOT NULL,
  `semester` VARCHAR(5) NOT NULL,
  `updated_at` DATETIME NOT NULL
);

ALTER TABLE `users` ADD FOREIGN KEY (`class`) REFERENCES `classes` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`sender`) REFERENCES `users` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

INSERT INTO users(
  nis, name, address, birthdate, status,
  class, phone_number, email_address, parent_phone, virtual_account,
  period, semester, password, role
) VALUES 
(
  '0000', 'Admin', 'Semarang', '2024-08-05', 'Inactive',
  1, '', '', '081329171920', 'admin',
  '2024/2025', 'Gasal', '25d55ad283aa400af464c76d713c07ad', 'ADMIN'
),
(
  '0001', 'Subadmin', 'Semarang', '2024-08-05', 'Inactive',
  1, '', '', '081329171920', 'subadmin',
  '2024/2025', 'Gasal', '25d55ad283aa400af464c76d713c07ad', 'SUBADMIN'
),
(
  '5048', 'Angel Ravelynta', 'Semarang', '2008-02-12', 'Active',
  25, '', '', '081329171920', '9881105622235048',
  '2024/2025', 'Gasal', '0b5088399ae4e9992ebcfb83bfd1da73', 'STUDENT'
);
