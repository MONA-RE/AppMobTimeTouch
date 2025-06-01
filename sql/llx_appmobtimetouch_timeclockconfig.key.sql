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


-- Indexes for timeclock_config
ALTER TABLE llx_timeclock_config ADD UNIQUE uk_timeclock_config_name (name, entity);
ALTER TABLE llx_timeclock_config ADD INDEX idx_timeclock_config_active (active);



-- Foreign key constraints
ALTER TABLE llx_timeclock_breaks ADD CONSTRAINT fk_timeclock_breaks_record FOREIGN KEY (fk_timeclock_record) REFERENCES llx_timeclock_records (rowid) ON DELETE CASCADE;
ALTER TABLE llx_timeclock_breaks ADD CONSTRAINT fk_timeclock_breaks_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user (rowid);
ALTER TABLE llx_timeclock_breaks ADD CONSTRAINT fk_timeclock_breaks_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user (rowid);