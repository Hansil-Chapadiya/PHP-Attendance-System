-- Production Database Setup Script
-- Create a dedicated database user with limited privileges

-- 1. Create database (if not exists)
CREATE DATABASE IF NOT EXISTS attendance_system_production 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. Create dedicated user (CHANGE THE PASSWORD!)
CREATE USER IF NOT EXISTS 'attendance_user'@'localhost' 
IDENTIFIED BY 'CHANGE_THIS_TO_SECURE_PASSWORD';

-- 3. Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE 
ON attendance_system_production.* 
TO 'attendance_user'@'localhost';

-- 4. Apply changes
FLUSH PRIVILEGES;

-- 5. Verify user
SELECT User, Host FROM mysql.user WHERE User = 'attendance_user';

-- 6. Show granted privileges
SHOW GRANTS FOR 'attendance_user'@'localhost';
