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


-- BUSINESS INDEXES FOR OVERTIME MANAGEMENT
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_rowid (rowid);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_ref (ref);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_entity (entity);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_fk_user (fk_user);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_fk_user_manager (fk_user_manager);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_month_year (month_year);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_status (status);

-- FOREIGN KEY CONSTRAINTS
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD CONSTRAINT llx_appmobtimetouch_timeclockovertimepaid_fk_user FOREIGN KEY (fk_user) REFERENCES llx_user(rowid);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD CONSTRAINT llx_appmobtimetouch_timeclockovertimepaid_fk_user_manager FOREIGN KEY (fk_user_manager) REFERENCES llx_user(rowid);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD CONSTRAINT llx_appmobtimetouch_timeclockovertimepaid_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);

-- UNIQUE CONSTRAINT FOR BUSINESS RULE: One record per user per month
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD UNIQUE INDEX uk_appmobtimetouch_timeclockovertimepaid_user_month(fk_user, month_year, entity);

