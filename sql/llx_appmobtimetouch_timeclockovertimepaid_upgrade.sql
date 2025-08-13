-- Copyright (C) 2025 SuperAdmin
-- Migration script for MVP 44.2.2 - Split month_year into month and year fields
--
-- This script migrates existing data from month_year (format YYYY-MM) to separate month and year fields

-- Add new columns
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid 
ADD COLUMN month integer COMMENT 'Month (1-12)' AFTER fk_user,
ADD COLUMN year integer COMMENT 'Year (ex: 2025)' AFTER month;

-- Migrate existing data from month_year to month and year
UPDATE llx_appmobtimetouch_timeclockovertimepaid 
SET 
    year = CAST(SUBSTRING(month_year, 1, 4) AS UNSIGNED),
    month = CAST(SUBSTRING(month_year, 6, 2) AS UNSIGNED)
WHERE month_year IS NOT NULL AND month_year != '';

-- Make the new columns NOT NULL after migration
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid 
MODIFY COLUMN month integer NOT NULL COMMENT 'Month (1-12)',
MODIFY COLUMN year integer NOT NULL COMMENT 'Year (ex: 2025)';

-- Remove old month_year column (uncomment after verifying migration)
-- ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid DROP COLUMN month_year;