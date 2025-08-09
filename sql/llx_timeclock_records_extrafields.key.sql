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

-- Keys and constraints for llx_timeclock_records_extrafields table

ALTER TABLE llx_timeclock_records_extrafields ADD INDEX idx_timeclock_records_extrafields_fk_object (fk_object);
ALTER TABLE llx_timeclock_records_extrafields ADD CONSTRAINT fk_timeclock_records_extrafields_fk_object FOREIGN KEY (fk_object) REFERENCES llx_timeclock_records (rowid) ON DELETE CASCADE;