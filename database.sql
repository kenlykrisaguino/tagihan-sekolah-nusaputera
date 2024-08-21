DROP DATABASE IF EXISTS tagihan_nusput;
CREATE DATABASE tagihan_nusput;
USE tagihan_nusput;

CREATE TABLE `administrations` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `admin_code` VARCHAR(30) UNIQUE NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `created_at` datetime DEFAULT NOW()
);

CREATE TABLE `classes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `level` VARCHAR(5) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `major` VARCHAR(50),
  `monthly_bills` decimal NOT NULL,
  `late_bills` decimal NOT NULL
);

INSERT INTO classes (name, major, level, monthly_bills, late_bills) VALUES
('PG', NULL, 'TK', 650000, 10000),
('A' , NULL, 'TK', 650000, 10000),
('B' , NULL, 'TK', 650000, 10000),
('A' , 'Excel', 'TK', 900000, 10000),
('B' , 'Excel', 'TK', 950000, 10000),
('1' , NULL, 'SD', 650000, 10000),
('2' , NULL, 'SD', 650000, 10000),
('3' , NULL, 'SD', 655000, 10000),
('4' , 'A', 'SD', 485000, 10000),
('4' , 'B', 'SD', 485000, 10000),
('5' , 'A', 'SD', 385000, 10000),
('5' , 'B', 'SD', 385000, 10000),
('6' , NULL, 'SD', 562000, 10000),
('7' , 'A', 'SMP', 575000, 10000),
('7' , 'B', 'SMP', 575000, 10000),
('8' , NULL, 'SMP', 540000, 10000),
('9' , 'A', 'SMP', 835000, 10000),
('9' , 'B', 'SMP', 835000, 10000),
('X' , '1', 'SMA', 700000, 10000),
('X' , '2', 'SMA', 700000, 10000),
('XI', '1', 'SMA', 665000, 10000),
('XI', '2', 'SMA', 665000, 10000),
('XI', 'Excel', 'SMA', 950000, 10000),
('XII','IPS', 'SMA', 866000, 10000),
('XII','IPA', 'SMA', 776000, 10000),
('XII','EXCEL', 'SMA', 1226000, 10000),
('X MM','A', 'SMK 1', 520000, 10000),
('X MM','B', 'SMK 1', 520000, 10000),
('XI MM','A', 'SMK 1 ', 520000, 10000),
('XI MM','B', 'SMK 1 ', 520000, 10000),
('XII MM',NULL, 'SMK 1', 694000, 10000),
('X TKJ',NULL, 'SMK 1 ', 610000, 10000),
('XI TKJ',NULL, 'SMK 1', 625000, 10000),
('XII TKJ',NULL, 'SMK 1', 820000, 10000);

CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nis` VARCHAR(10) UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `birthdate` DATE NOT NULL,
  `status` VARCHAR(10) NOT NULL,
  `class` int NOT NULL,
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
  `trx_status` VARCHAR(15) NOT NULL,
  `late_bills` decimal DEFAULT 0,
  `description` text DEFAULT NULL,
  `class` INT NOT NULL,
  `period` VARCHAR(9) NOT NULL,
  `semester` VARCHAR(5) NOT NULL,
  `payment_due` datetime NOT NULL
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

ALTER TABLE `users` ADD FOREIGN KEY (`class`) REFERENCES `classes` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`sender`) REFERENCES `users` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

INSERT INTO users(
  nis, name, address, birthdate, status,
  class, phone_number, email_address, parent_phone, virtual_account,
  period, semester, password, role
) VALUES 
(
  '0000', 'Admin', 'Semarang', NOW(), 'Inactive',
  1, '', '', '087731335955', 'admin',
  '2024/2025', 'Gasal', '$2y$10$nzDoKrPD37M3E3xivsR7H.K6W4o1q28L3T11aB6ia3EVtxbc2tTsu', 'ADMIN'
),
(
  '5048', 'Angel Ravelynta', 'Semarang', NOW(), 'Active',
  25, '', '', '087731335955', '9881105622235048',
  '2024/2025', 'Gasal', '$2y$10$nzDoKrPD37M3E3xivsR7H.K6W4o1q28L3T11aB6ia3EVtxbc2tTsu', 'STUDENT'
);
