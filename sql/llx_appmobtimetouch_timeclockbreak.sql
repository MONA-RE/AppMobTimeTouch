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

CREATE TABLE llx_timeclock_breaks (
    -- Standard fields
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer,
    fk_user_modif integer,
    
    -- Break details
    fk_timeclock_record integer NOT NULL,
    break_start datetime NOT NULL,
    break_end datetime DEFAULT NULL,
    break_type varchar(32) DEFAULT 'BREAK' NOT NULL COMMENT 'LUNCH, BREAK, PERSONAL, OTHER',
    duration integer DEFAULT NULL COMMENT 'Duration in minutes (calculated)',
    
    -- Additional info
    note varchar(255) DEFAULT NULL,
    location varchar(255) DEFAULT NULL,
    
    -- Status
    status integer DEFAULT 1 NOT NULL COMMENT '0=draft, 1=active, 2=completed'
) ENGINE=innodb;