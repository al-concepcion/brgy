-- Add preferred_date column to certification_requests table
ALTER TABLE certification_requests 
ADD COLUMN preferred_date DATE NULL AFTER claim_method;

-- Add preferred_pickup_date column to id_applications table
ALTER TABLE id_applications 
ADD COLUMN preferred_pickup_date DATE NULL AFTER complete_address;

-- Update existing records to set a default preferred date (optional)
-- UPDATE certification_requests SET preferred_date = DATE_ADD(created_at, INTERVAL 3 DAY) WHERE preferred_date IS NULL;
-- UPDATE id_applications SET preferred_pickup_date = DATE_ADD(created_at, INTERVAL 5 DAY) WHERE preferred_pickup_date IS NULL;
