-- ============================================================
-- Digital Medical Imaging Management System
-- Database Schema
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- CREATE DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `medical_imaging_db` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `medical_imaging_db`;

-- ============================================================
-- TABLE: roles
-- ============================================================
CREATE TABLE `roles` (
  `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL,
  `role_description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uk_role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `account_status` ENUM('active','disabled','locked') NOT NULL DEFAULT 'active',
  `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_account_status` (`account_status`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) 
    REFERENCES `roles` (`role_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: patients
-- ============================================================
CREATE TABLE `patients` (
  `patient_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hospital_number` VARCHAR(30) NOT NULL,
  `first_name` VARCHAR(80) NOT NULL,
  `middle_name` VARCHAR(80) DEFAULT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `gender` ENUM('Male','Female','Other') NOT NULL,
  `national_id` VARCHAR(30) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `emergency_contact` VARCHAR(150) DEFAULT NULL,
  `record_status` ENUM('active','archived') NOT NULL DEFAULT 'active',
  `registered_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `uk_hospital_number` (`hospital_number`),
  KEY `idx_patient_name` (`last_name`, `first_name`),
  KEY `idx_record_status` (`record_status`),
  KEY `idx_registered_by` (`registered_by`),
  CONSTRAINT `fk_patients_user` FOREIGN KEY (`registered_by`) 
    REFERENCES `users` (`user_id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: medical_images
-- ============================================================
CREATE TABLE `medical_images` (
  `image_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` INT UNSIGNED NOT NULL,
  `imaging_type` ENUM('X-ray','CT','MRI','Ultrasound') NOT NULL,
  `study_date` DATE NOT NULL,
  `body_part` VARCHAR(100) NOT NULL,
  `referring_clinician` VARCHAR(150) DEFAULT NULL,
  `clinical_notes` TEXT DEFAULT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `original_file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_mime_type` VARCHAR(100) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `file_hash` CHAR(64) NOT NULL,
  `uploaded_by` INT UNSIGNED NOT NULL,
  `record_status` ENUM('active','archived') NOT NULL DEFAULT 'active',
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_file_hash` (`file_hash`),
  KEY `idx_imaging_type` (`imaging_type`),
  KEY `idx_study_date` (`study_date`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_record_status` (`record_status`),
  KEY `idx_uploaded_at` (`uploaded_at`),
  CONSTRAINT `fk_images_patient` FOREIGN KEY (`patient_id`) 
    REFERENCES `patients` (`patient_id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_images_user` FOREIGN KEY (`uploaded_by`) 
    REFERENCES `users` (`user_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE `audit_logs` (
  `log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(80) NOT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`user_id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: backups
-- ============================================================
CREATE TABLE `backups` (
  `backup_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED DEFAULT NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `backup_status` ENUM('success','failed') NOT NULL DEFAULT 'success',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`backup_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_backups_user` FOREIGN KEY (`created_by`) 
    REFERENCES `users` (`user_id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT INITIAL ROLES
-- ============================================================
INSERT INTO `roles` (`role_id`, `role_name`, `role_description`) VALUES
(1, 'admin', 'System Administrator - full access'),
(2, 'radiologist', 'Radiologist - imaging specialist'),
(3, 'technician', 'Radiology Technician - image capture'),
(4, 'doctor', 'Doctor - clinical image review'),
(5, 'records', 'Records Officer - patient registration');

-- ============================================================
-- INSERT DEMO USERS
-- Password for all demo users: ChangeMe123!
-- Hash generated with password_hash('ChangeMe123!', PASSWORD_BCRYPT)
-- ============================================================
INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `password_hash`, `role_id`, `account_status`) VALUES
(1, 'System Administrator', 'admin', 'admin@hospital.demo', '+254700000001', 
 '$2y$10$YourHashWillGoHereReplaceWithActualHash1', 1, 'active'),
(2, 'Dr. Sarah Radiologist', 'radiologist', 'radiologist@hospital.demo', '+254700000002', 
 '$2y$10$YourHashWillGoHereReplaceWithActualHash2', 2, 'active'),
(3, 'James Technician', 'technician', 'technician@hospital.demo', '+254700000003', 
 '$2y$10$YourHashWillGoHereReplaceWithActualHash3', 3, 'active'),
(4, 'Dr. Peter Doctor', 'doctor', 'doctor@hospital.demo', '+254700000004', 
 '$2y$10$YourHashWillGoHereReplaceWithActualHash4', 4, 'active'),
(5, 'Mary Records Officer', 'records', 'records@hospital.demo', '+254700000005', 
 '$2y$10$YourHashWillGoHereReplaceWithActualHash5', 5, 'active');

-- ============================================================
-- INSERT DEMO PATIENTS (FICTIONAL)
-- ============================================================
INSERT INTO `patients` (`patient_id`, `hospital_number`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `national_id`, `phone`, `address`, `emergency_contact`, `registered_by`) VALUES
(1, 'HOSP-2025-0001', 'John', 'M.', 'Kamau', '1985-03-15', 'Male', 'DEMO-ID-001', '+254711000001', '123 Demo Street, Nairobi', 'Jane Kamau +254711000010', 1),
(2, 'HOSP-2025-0002', 'Mary', 'W.', 'Wanjiku', '1990-07-22', 'Female', 'DEMO-ID-002', '+254711000002', '456 Sample Road, Nakuru', 'Peter Wanjiku +254711000011', 1),
(3, 'HOSP-2025-0003', 'Peter', 'O.', 'Otieno', '1978-11-08', 'Male', 'DEMO-ID-003', '+254711000003', '789 Test Avenue, Kisumu', 'Grace Otieno +254711000012', 1),
(4, 'HOSP-2025-0004', 'Grace', 'A.', 'Akinyi', '1995-01-30', 'Female', 'DEMO-ID-004', '+254711000004', '321 Demo Lane, Mombasa', 'John Akinyi +254711000013', 1);

-- ============================================================
-- INSERT DEMO IMAGING RECORDS
-- ============================================================
INSERT INTO `medical_images` (`patient_id`, `imaging_type`, `study_date`, `body_part`, `referring_clinician`, `clinical_notes`, `file_name`, `original_file_name`, `file_path`, `file_mime_type`, `file_size`, `file_hash`, `uploaded_by`) VALUES
(1, 'X-ray', '2025-01-15', 'Chest', 'Dr. Peter Doctor', 'Routine chest examination. No abnormalities detected.', 'demo_xray_001.jpg', 'chest_xray.jpg', 'uploads/medical_images/demo_xray_001.jpg', 'image/jpeg', 245760, SHA2('demo_xray_001_content', 256), 2),
(1, 'CT', '2025-02-20', 'Head', 'Dr. Peter Doctor', 'CT head scan following minor head injury. No fractures.', 'demo_ct_001.jpg', 'head_ct.jpg', 'uploads/medical_images/demo_ct_001.jpg', 'image/jpeg', 512000, SHA2('demo_ct_001_content', 256), 2),
(2, 'MRI', '2025-03-10', 'Spine', 'Dr. Sarah Radiologist', 'MRI lumbar spine. Disc bulge at L4-L5.', 'demo_mri_001.jpg', 'lumbar_mri.jpg', 'uploads/medical_images/demo_mri_001.jpg', 'image/jpeg', 780000, SHA2('demo_mri_001_content', 256), 2),
(3, 'Ultrasound', '2025-03-25', 'Abdomen', 'Dr. Peter Doctor', 'Abdominal ultrasound. Normal findings.', 'demo_us_001.jpg', 'abdomen_us.jpg', 'uploads/medical_images/demo_us_001.jpg', 'image/jpeg', 320000, SHA2('demo_us_001_content', 256), 3),
(4, 'X-ray', '2025-04-05', 'Left Knee', 'Dr. Peter Doctor', 'Knee X-ray. Mild osteoarthritis.', 'demo_xray_002.jpg', 'knee_xray.jpg', 'uploads/medical_images/demo_xray_002.jpg', 'image/jpeg', 198000, SHA2('demo_xray_002_content', 256), 3);

-- ============================================================
-- INSERT DEMO AUDIT LOGS
-- ============================================================
INSERT INTO `audit_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`) VALUES
(1, 'USER_LOGIN', 'user', 1, 'Administrator logged in', '127.0.0.1'),
(2, 'IMAGE_UPLOAD', 'medical_image', 1, 'Uploaded X-ray for patient ID 1', '127.0.0.1'),
(2, 'IMAGE_UPLOAD', 'medical_image', 2, 'Uploaded CT scan for patient ID 1', '127.0.0.1'),
(3, 'PATIENT_CREATE', 'patient', 3, 'Registered new patient: Peter Otieno', '127.0.0.1');

COMMIT;