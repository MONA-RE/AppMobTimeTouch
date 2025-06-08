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

CREATE TABLE llx_timeclock_notifications (
    -- Standard fields
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Notification fields
    fk_user integer NOT NULL COMMENT 'User receiving the notification',
    notification_type varchar(50) NOT NULL COMMENT 'Type of notification (pending_validation, validation_status, anomaly_alert, etc.)',
    message text NOT NULL COMMENT 'Notification message content',
    notification_data text DEFAULT NULL COMMENT 'JSON data associated with notification',
    
    -- Status fields
    is_read tinyint DEFAULT 0 NOT NULL COMMENT '0=unread, 1=read',
    created_date datetime NOT NULL COMMENT 'When notification was created',
    read_date datetime DEFAULT NULL COMMENT 'When notification was read',
    
    -- Priority and categorization
    priority enum('low','normal','medium','high','critical') DEFAULT 'normal' COMMENT 'Notification priority level',
    category varchar(32) DEFAULT 'general' COMMENT 'Notification category for filtering'
    
) ENGINE=innodb COMMENT='Notifications for timeclock validation workflow';