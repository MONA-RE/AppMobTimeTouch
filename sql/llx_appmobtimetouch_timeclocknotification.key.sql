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

-- Keys and indexes for llx_timeclock_notifications

ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_fk_user (fk_user);
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_entity (entity);
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_type (notification_type);
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_is_read (is_read);
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_created_date (created_date);
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_priority (priority);

-- Composite indexes for common queries
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_user_unread (fk_user, is_read);
ALTER TABLE llx_timeclock_notifications ADD INDEX idx_timeclock_notifications_type_date (notification_type, created_date);

-- Foreign key constraints
ALTER TABLE llx_timeclock_notifications ADD CONSTRAINT fk_timeclock_notifications_user 
    FOREIGN KEY (fk_user) REFERENCES llx_user(rowid) ON DELETE CASCADE;