-- Quick migration SQL commands for MVP 44.2.2
-- Execute these commands in your MySQL database

USE dev_smta;

-- Step 1: Add new columns
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid 
ADD COLUMN month integer COMMENT 'Month (1-12)' AFTER fk_user,
ADD COLUMN year integer COMMENT 'Year (ex: 2025)' AFTER month;

-- Step 2: Migrate existing data
UPDATE llx_appmobtimetouch_timeclockovertimepaid 
SET 
    year = CAST(SUBSTRING(month_year, 1, 4) AS UNSIGNED),
    month = CAST(SUBSTRING(month_year, 6, 2) AS UNSIGNED)
WHERE month_year IS NOT NULL AND month_year != '';

-- Step 3: Make columns NOT NULL
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid 
MODIFY COLUMN month integer NOT NULL COMMENT 'Month (1-12)',
MODIFY COLUMN year integer NOT NULL COMMENT 'Year (ex: 2025)';

-- Step 4: Drop old column
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid 
DROP COLUMN month_year;

-- Verify the migration
SELECT * FROM llx_appmobtimetouch_timeclockovertimepaid LIMIT 5;