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

-- Default data for AppMobTimeTouch module

-- Insert default timeclock types
INSERT INTO llx_timeclock_types (entity, datec, code, label, description, color, position, active, module) VALUES
(1, NOW(), 'OFFICE', 'Office Work', 'Regular office work at company premises', '#4CAF50', 1, 0, 'appmobtimetouch'),
(1, NOW(), 'REMOTE', 'Remote Work', 'Work from home or remote location', '#2196F3', 2, 0, 'appmobtimetouch'),
(1, NOW(), 'CHANTIER', 'CHANTIER Mission', 'chantier sur le site du client ou mission externe', '#FF9800', 3, 1, 'appmobtimetouch'),
(1, NOW(), 'TRAINING', 'Training', 'Training sessions and professional development', '#9C27B0', 4, 0, 'appmobtimetouch'),
(1, NOW(), 'MEETING', 'External Meeting', 'Meetings outside office premises', '#607D8B', 5, 0, 'appmobtimetouch');

-- Insert default configuration parameters
INSERT INTO llx_timeclock_config (entity, datec, name, value, type, note, active) VALUES
(1, NOW(), 'AUTO_BREAK_MINUTES', '60', 'int', 'Automatic break duration in minutes after continuous work', 1),
(1, NOW(), 'MAX_HOURS_PER_DAY', '12', 'int', 'Maximum allowed working hours per day', 1),
(1, NOW(), 'REQUIRE_LOCATION', '0', 'boolean', 'Require GPS location for clock in/out', 1),
(1, NOW(), 'ALLOW_MANUAL_EDIT', '1', 'boolean', 'Allow users to manually edit their time records', 1),
(1, NOW(), 'VALIDATION_REQUIRED', '1', 'boolean', 'Require manager validation for time records', 1),
(1, NOW(), 'OVERTIME_THRESHOLD', '8', 'float', 'Daily hours threshold before overtime calculation', 1),
(1, NOW(), 'WEEKLY_HOURS_THRESHOLD', '40', 'float', 'Weekly hours threshold before overtime calculation', 1),
(1, NOW(), 'AUTO_CLOCK_OUT_HOURS', '24', 'int', 'Auto clock out after X hours if not manually clocked out', 1),
(1, NOW(), 'BREAK_REMINDER_MINUTES', '240', 'int', 'Remind user to take break after X minutes of continuous work', 1),
(1, NOW(), 'ALLOW_FUTURE_CLOCKIN', '0', 'boolean', 'Allow clock in for future dates', 1),
(1, NOW(), 'MINIMUM_BREAK_MINUTES', '5', 'int', 'Minimum break duration in minutes', 1),
(1, NOW(), 'LOCATION_RADIUS_METERS', '100', 'int', 'Allowed radius in meters from registered work locations', 1);

-- Insert rights definitions for the module
-- These will be automatically handled by the DolibarrModules class, but we can add custom ones here if needed

-- Insert default work locations (optional - can be configured later)
-- This table structure would need to be added if we want predefined work locations
-- INSERT INTO llx_timeclock_locations (entity, datec, name, address, latitude, longitude, radius, active) VALUES
-- (1, NOW(), 'Main Office', 'Your company address', 48.8566, 2.3522, 100, 1);

-- Add menu entries that are not automatically handled
-- These are handled by the DolibarrModules class in most cases