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

CREATE TABLE llx_timeclock_weekly_summary (
    -- Standard fields
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer,
    fk_user_modif integer,
    
    -- Summary details
    fk_user integer NOT NULL,
    year integer NOT NULL,
    week_number integer NOT NULL COMMENT 'ISO week number (1-53)',
    week_start_date date NOT NULL,
    week_end_date date NOT NULL,
    
    -- Time calculations
    total_hours decimal(8,2) DEFAULT 0 NOT NULL COMMENT 'Total hours worked',
    total_breaks integer DEFAULT 0 NOT NULL COMMENT 'Total break minutes',
    expected_hours decimal(8,2) DEFAULT 0 NOT NULL COMMENT 'Expected hours for this week',
    overtime_hours decimal(8,2) DEFAULT 0 NOT NULL COMMENT 'Overtime hours',
    days_worked integer DEFAULT 0 NOT NULL,
    
    -- Status and validation
    status integer DEFAULT 0 NOT NULL COMMENT '0=in_progress, 1=completed, 2=validated, 9=cancelled',
    validated_by integer DEFAULT NULL,
    validated_date datetime DEFAULT NULL,
    
    -- Notes
    note text DEFAULT NULL
) ENGINE=innodb;