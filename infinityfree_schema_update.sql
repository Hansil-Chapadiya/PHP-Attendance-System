-- ============================================
-- InfinityFree Database Schema Updates
-- Run this in your InfinityFree phpMyAdmin
-- ============================================

-- 1. Ensure subject column exists in classes table
ALTER TABLE `classes` 
ADD COLUMN IF NOT EXISTS `subject` VARCHAR(100) DEFAULT NULL;

-- 2. Ensure semester column exists in students table
ALTER TABLE `students` 
ADD COLUMN IF NOT EXISTS `semester` INT(11) DEFAULT NULL;

-- 3. Add timestamp columns to classes if missing
ALTER TABLE `classes` 
ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS `expires_at` TIMESTAMP NULL DEFAULT NULL;

-- 4. Create schedule table for weekly timetable (if not exists)
CREATE TABLE IF NOT EXISTS `schedule` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `day_of_week` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `division` VARCHAR(10) NOT NULL,
  `semester` INT(11) DEFAULT NULL,
  `time_slot` VARCHAR(50) DEFAULT NULL,
  `faculty_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faculty_day` (`faculty_id`, `day_of_week`),
  KEY `idx_division_day` (`division`, `day_of_week`),
  KEY `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Add indexes for better performance
ALTER TABLE `classes` ADD INDEX IF NOT EXISTS `idx_class_id` (`class_id`);
ALTER TABLE `attendance` ADD INDEX IF NOT EXISTS `idx_user_date` (`user_id`, `date`);
ALTER TABLE `attendance` ADD INDEX IF NOT EXISTS `idx_class_date` (`class_id`, `date`);

-- 6. Create rate_limit table for security (if not exists)
CREATE TABLE IF NOT EXISTS `rate_limit` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(255) NOT NULL,
  `attempt_time` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier_time` (`identifier`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Verification Queries (run these to check)
-- ============================================

-- Check if subject column exists in classes
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'classes' AND COLUMN_NAME = 'subject';

-- Check if semester column exists in students
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'students' AND COLUMN_NAME = 'semester';

-- Check if schedule table exists
SHOW TABLES LIKE 'schedule';

-- Check classes table structure
DESCRIBE classes;

-- Check students table structure
DESCRIBE students;

-- ============================================
-- Sample Data Update (Optional)
-- Update existing records if needed
-- ============================================

-- Example: Set default semester for existing students if NULL
-- UPDATE students SET semester = 1 WHERE semester IS NULL;

-- Example: Set default subject for existing classes if NULL
-- UPDATE classes SET subject = 'General' WHERE subject IS NULL;

