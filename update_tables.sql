-- Add completed_at column to cleaning_requests table
ALTER TABLE cleaning_requests
ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER status; 