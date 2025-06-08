-- Copyright (C) 2025 SuperAdmin
--
-- Add validation workflow fields to existing timeclock_records table
-- Sprint 2: Validation workflow implementation

-- Add validation status fields if they don't exist
ALTER TABLE llx_timeclock_records 
ADD COLUMN IF NOT EXISTS validation_status tinyint DEFAULT 0 NOT NULL COMMENT '0=pending, 1=approved, 2=rejected, 3=partial',
ADD COLUMN IF NOT EXISTS validated_by integer DEFAULT NULL COMMENT 'User who validated this record',
ADD COLUMN IF NOT EXISTS validated_at datetime DEFAULT NULL COMMENT 'When validation was performed',
ADD COLUMN IF NOT EXISTS validation_comment text DEFAULT NULL COMMENT 'Manager comment during validation',
ADD COLUMN IF NOT EXISTS validation_deadline datetime DEFAULT NULL COMMENT 'Deadline for validation',
ADD COLUMN IF NOT EXISTS auto_validated tinyint DEFAULT 0 NOT NULL COMMENT '1 if automatically validated based on rules';

-- Add indexes for validation queries
ALTER TABLE llx_timeclock_records ADD INDEX IF NOT EXISTS idx_timeclock_records_validation_status (validation_status);
ALTER TABLE llx_timeclock_records ADD INDEX IF NOT EXISTS idx_timeclock_records_validated_by (validated_by);
ALTER TABLE llx_timeclock_records ADD INDEX IF NOT EXISTS idx_timeclock_records_validated_at (validated_at);
ALTER TABLE llx_timeclock_records ADD INDEX IF NOT EXISTS idx_timeclock_records_validation_deadline (validation_deadline);

-- Composite indexes for manager queries
ALTER TABLE llx_timeclock_records ADD INDEX IF NOT EXISTS idx_timeclock_records_user_validation (fk_user, validation_status);
ALTER TABLE llx_timeclock_records ADD INDEX IF NOT EXISTS idx_timeclock_records_status_validation (status, validation_status);

-- Add foreign key constraint for validator
ALTER TABLE llx_timeclock_records ADD CONSTRAINT IF NOT EXISTS fk_timeclock_records_validator 
    FOREIGN KEY (validated_by) REFERENCES llx_user(rowid) ON DELETE SET NULL;

-- Update existing records to have pending validation status (only completed records)
UPDATE llx_timeclock_records 
SET validation_status = 0 
WHERE status = 3 AND validation_status IS NULL;

-- Set validation deadline for existing pending records (3 days from clock_in_time)
UPDATE llx_timeclock_records 
SET validation_deadline = DATE_ADD(clock_in_time, INTERVAL 3 DAY)
WHERE validation_status = 0 AND validation_deadline IS NULL;