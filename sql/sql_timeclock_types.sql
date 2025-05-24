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

CREATE TABLE llx_timeclock_types (
    -- Standard fields
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Type definition
    code varchar(32) NOT NULL,
    label varchar(255) NOT NULL,
    description text DEFAULT NULL,
    
    -- Display properties
    color varchar(7) DEFAULT '#4CAF50' COMMENT 'Hex color code for display',
    position integer DEFAULT 0 NOT NULL,
    
    -- Status
    active tinyint DEFAULT 1 NOT NULL,
    
    -- Module ownership
    module varchar(32) DEFAULT NULL COMMENT 'Module that created this type'
) ENGINE=innodb;