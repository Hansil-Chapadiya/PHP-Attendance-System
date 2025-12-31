-- Database schema updates for production security

-- Create rate_limit table for rate limiting
CREATE TABLE IF NOT EXISTS `rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `attempt_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier_time` (`identifier`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add created_at column to classes table if not exists
ALTER TABLE `classes` 
ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS `expires_at` TIMESTAMP NULL DEFAULT NULL;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_class_id ON `classes` (`class_id`);
CREATE INDEX IF NOT EXISTS idx_user_date ON `attendance` (`user_id`, `date`);
CREATE INDEX IF NOT EXISTS idx_class_date ON `attendance` (`class_id`, `date`);

-- Add semester column to students if missing
-- ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `semester` int(11) DEFAULT NULL;
