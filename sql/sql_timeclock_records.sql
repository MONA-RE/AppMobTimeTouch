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

CREATE TABLE llx_timeclock_records (
    -- Standard Dolibarr fields
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    ref varchar(30) NOT NULL,
    entity integer DEFAULT 1 NOT NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer,
    fk_user_modif integer,
    
    -- Time tracking specific fields
    fk_user integer NOT NULL,
    clock_in_time datetime NOT NULL,
    clock_out_time datetime DEFAULT NULL,
    break_duration integer DEFAULT 0 NOT NULL COMMENT 'Total break duration in minutes',
    work_duration integer DEFAULT NULL COMMENT 'Calculated work duration in minutes',
    
    -- Location tracking
    location_in varchar(255) DEFAULT NULL,
    location_out varchar(255) DEFAULT NULL,
    latitude_in decimal(10,8) DEFAULT NULL,
    longitude_in decimal(11,8) DEFAULT NULL,
    latitude_out decimal(10,8) DEFAULT NULL,
    longitude_out decimal(11,8) DEFAULT NULL,
    
    -- IP tracking
    ip_address_in varchar(45) DEFAULT NULL,
    ip_address_out varchar(45) DEFAULT NULL,
    
    -- Status and validation
    status integer DEFAULT 2 NOT NULL COMMENT '0=draft, 1=validated, 2=in_progress, 3=completed, 9=cancelled',
    fk_timeclock_type integer DEFAULT 1 NOT NULL,
    validated_by integer DEFAULT NULL,
    validated_date datetime DEFAULT NULL,
    
    -- Notes
    note_private text DEFAULT NULL,
    note_public text DEFAULT NULL,
    
    -- Import tracking
    import_key varchar(14) DEFAULT NULL,
    model_pdf varchar(255) DEFAULT NULL
) ENGINE=innodb;