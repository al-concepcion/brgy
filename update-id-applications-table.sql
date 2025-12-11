-- Update script to add missing columns to id_applications table
-- Run this script to fix the "Database error: Unable to submit application" issue

USE barangay_db;

-- Add user_id column if it doesn't exist
ALTER TABLE id_applications 
ADD COLUMN IF NOT EXISTS user_id INT AFTER id;

-- Add preferred_pickup_date column if it doesn't exist
ALTER TABLE id_applications 
ADD COLUMN IF NOT EXISTS preferred_pickup_date DATE AFTER complete_address;

-- Add foreign key constraint for user_id
ALTER TABLE id_applications 
ADD CONSTRAINT fk_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add index for user_id
ALTER TABLE id_applications 
ADD INDEX IF NOT EXISTS idx_user (user_id);

SELECT 'Database update completed successfully!' AS message;
