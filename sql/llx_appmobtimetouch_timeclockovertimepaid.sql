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


CREATE TABLE llx_appmobtimetouch_timeclockovertimepaid(
	-- PRIMARY FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	entity integer DEFAULT 1 NOT NULL,
	ref varchar(128) NOT NULL, 
	
	-- BUSINESS FIELDS FOR OVERTIME MANAGEMENT
	fk_user integer NOT NULL COMMENT 'Employee (user) concerned by paid overtime',
	month_year varchar(7) NOT NULL COMMENT 'Month and year format YYYY-MM',
	hours_paid decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Number of overtime hours paid (ex: 10.50)',
	fk_user_manager integer NOT NULL COMMENT 'Manager who entered the paid overtime',
	
	-- AUDIT AND TRACKING FIELDS
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	
	-- OPTIONAL FIELDS
	note_private text COMMENT 'Private note for internal use',
	import_key varchar(14), 
	status integer NOT NULL DEFAULT 1 COMMENT '0=Draft, 1=Active, 9=Disabled'
	
) ENGINE=innodb;
