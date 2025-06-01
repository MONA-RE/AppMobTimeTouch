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

CREATE TABLE llx_timeclock_config (
    -- Standard fields
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Configuration parameters
    name varchar(180) NOT NULL,
    value text DEFAULT NULL,
    type varchar(64) DEFAULT 'string' NOT NULL COMMENT 'string, int, float, boolean, json',
    note varchar(255) DEFAULT NULL,
    
    -- Status
    active tinyint DEFAULT 1 NOT NULL
) ENGINE=innodb;