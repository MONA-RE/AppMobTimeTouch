-- Copyright (C) 2025 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

-- Keys and index for module AppMobTimeTouch

-- Indexes for timeclock_records
ALTER TABLE llx_timeclock_records ADD INDEX idx_timeclock_records_ref (ref);
ALTER TABLE llx_timeclock_records ADD INDEX idx_timeclock_records_user_date (fk_user, clock_in_time);
ALTER TABLE llx_timeclock_records ADD INDEX idx_timeclock_records_status (status);
ALTER TABLE llx_timeclock_records ADD INDEX idx_timeclock_records_date (clock_in_time);
ALTER TABLE llx_timeclock_records ADD INDEX idx_timeclock_records_entity (entity);
ALTER TABLE llx_timeclock_records ADD INDEX idx_timeclock_records_type (fk_timeclock_type);

-- Indexes for timeclock_types
ALTER TABLE llx_timeclock_types ADD UNIQUE uk_timeclock_types_code (code, entity);
ALTER TABLE llx_timeclock_types ADD INDEX idx_timeclock_types_active (active);
ALTER TABLE llx_timeclock_types ADD INDEX idx_timeclock_types_position (position);

-- Indexes for timeclock_breaks
ALTER TABLE llx_timeclock_breaks ADD INDEX idx_timeclock_breaks_record (fk_timeclock_record);
ALTER TABLE llx_timeclock_breaks ADD INDEX idx_timeclock_breaks_start (break_start);
ALTER TABLE llx_timeclock_breaks ADD INDEX idx_timeclock_breaks_type (break_type);

-- Indexes for timeclock_config
ALTER TABLE llx_timeclock_config ADD UNIQUE uk_timeclock_config_name (name, entity);
ALTER TABLE llx_timeclock_config ADD INDEX idx_timeclock_config_active (active);

-- Indexes for timeclock_weekly_summary
ALTER TABLE llx_timeclock_weekly_summary ADD UNIQUE uk_timeclock_weekly_user_year_week (fk_user, year, week_number);
ALTER TABLE llx_timeclock_weekly_summary ADD INDEX idx_timeclock_weekly_user (fk_user);
ALTER TABLE llx_timeclock_weekly_summary ADD INDEX idx_timeclock_weekly_year (year);
ALTER TABLE llx_timeclock_weekly_summary ADD INDEX idx_timeclock_weekly_status (status);

-- Foreign key constraints
ALTER TABLE llx_timeclock_records ADD CONSTRAINT fk_timeclock_records_user FOREIGN KEY (fk_user) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_records ADD CONSTRAINT fk_timeclock_records_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_records ADD CONSTRAINT fk_timeclock_records_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_records ADD CONSTRAINT fk_timeclock_records_type FOREIGN KEY (fk_timeclock_type) REFERENCES llx_timeclock_types (rowid);
ALTER TABLE llx_timeclock_records ADD CONSTRAINT fk_timeclock_records_validated_by FOREIGN KEY (validated_by) REFERENCES llx_user (rowid);

ALTER TABLE llx_timeclock_breaks ADD CONSTRAINT fk_timeclock_breaks_record FOREIGN KEY (fk_timeclock_record) REFERENCES llx_timeclock_records (rowid) ON DELETE CASCADE;
ALTER TABLE llx_timeclock_breaks ADD CONSTRAINT fk_timeclock_breaks_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_breaks ADD CONSTRAINT fk_timeclock_breaks_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);

ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_user FOREIGN KEY (fk_user) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_validated_by FOREIGN KEY (validated_by) REFERENCES llx_user (rowid);