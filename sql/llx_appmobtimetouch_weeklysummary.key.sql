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

-- Indexes for timeclock_weekly_summary
ALTER TABLE llx_timeclock_weekly_summary ADD UNIQUE uk_timeclock_weekly_user_year_week (fk_user, year, week_number);
ALTER TABLE llx_timeclock_weekly_summary ADD INDEX idx_timeclock_weekly_user (fk_user);
ALTER TABLE llx_timeclock_weekly_summary ADD INDEX idx_timeclock_weekly_year (year);
ALTER TABLE llx_timeclock_weekly_summary ADD INDEX idx_timeclock_weekly_status (status);

-- Foreign key constraints

ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_user FOREIGN KEY (fk_user) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_weekly_summary ADD CONSTRAINT fk_timeclock_weekly_validated_by FOREIGN KEY (validated_by) REFERENCES llx_user (rowid);