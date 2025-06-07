# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AppMobTimeTouch is a Dolibarr module for mobile time tracking and employee presence management. It's built as a custom module using the Dolibarr framework with a mobile-first interface using OnsenUI.

## Development Commands

### Testing
- **Run PHPUnit tests**: `cd test/phpunit && phpunit timeclockrecordTest.php`
- **Run API tests**: `php test/api_timeclock_test.php`
- **Run functional tests**: `cd test/phpunit && phpunit AppMobTimeTouchFunctionalTest.php`

### Database Operations
- **Install/Update tables**: SQL files are in `sql/` directory and are automatically executed during module activation
- **Reset data**: Execute `sql/dolibarr_allversions.sql` to recreate all tables
- **Check keys**: Review `sql/sql_timeclock_keys.sql` for database constraints

### Module Management
- **Enable module**: Go to Dolibarr admin → Modules → Search "AppMobTimeTouch" → Enable
- **Configure module**: Go to HR menu → TimeTracking → Setup
- **View logs**: Check Dolibarr syslog for debug information (all functions use `dol_syslog`)

## Architecture Overview

### Core Business Classes
- **TimeclockRecord**: Main entity for time tracking entries (`class/timeclockrecord.class.php`)
- **TimeclockType**: Work types (office, remote, mission) (`class/timeclocktype.class.php`)
- **TimeclockConfig**: Module configuration storage (`class/timeclockconfig.class.php`)
- **TimeclockBreak**: Break management (`class/timeclockbreak.class.php`)
- **WeeklySummary**: Weekly time aggregations (`class/weeklysummary.class.php`)

### Database Schema
- `llx_timeclock_records`: Main time entries with clock in/out, geolocation, status
- `llx_timeclock_types`: Configurable work types (office, remote, etc.)
- `llx_timeclock_config`: Module settings and parameters
- `llx_timeclock_breaks`: Break periods within work sessions
- `llx_timeclock_weekly_summary`: Pre-calculated weekly summaries

### API Architecture
- **REST API**: `api/timeclock.php` provides RESTful endpoints
- **Endpoints**: 
  - GET `/status` - Current user clock status
  - POST `/clockin` - Clock in with location
  - POST `/clockout` - Clock out
  - GET `/records` - Time records history
  - GET `/types` - Available work types
- **Security**: CSRF token validation, permission checks, input sanitization

### Mobile Interface
- **Framework**: OnsenUI for mobile UI components
- **Main entry**: `home.php` serves the mobile interface
- **Template**: `tpl/home.tpl` contains the mobile UI structure
- **JavaScript**: `js/timeclock-api.js` handles API interactions
- **CSS**: OnsenUI components + custom styles in `css/`

### Dolibarr Integration
- **Module class**: `core/modules/modAppMobTimeTouch.class.php` defines module structure
- **Permissions**: Hierarchical rights system (read, write, readall, validate, export)
- **Menus**: Integrated into Dolibarr menu system under HR section
- **Hooks**: Triggers for user events, time validation workflows
- **Extrafields**: Extensible field system support

## Key Configuration Constants
- `APPMOBTIMETOUCH_REQUIRE_LOCATION`: GPS location required for clock in/out
- `APPMOBTIMETOUCH_MAX_HOURS_DAY`: Maximum work hours per day
- `APPMOBTIMETOUCH_AUTO_BREAK_MINUTES`: Default break duration
- `APPMOBTIMETOUCH_VALIDATION_REQUIRED`: Manager validation workflow

## Development Patterns

### Time Handling
- All times stored as datetime in database
- Use `$db->jdate()` for database timestamp conversion
- Use `dol_now()` for current timestamp
- Helper functions `convertSecondsToReadableTime()` and `formatDuration()` for display

### Status Management
Time records have status workflow:
- 0: Draft
- 1: Validated  
- 2: In Progress (clocked in)
- 3: Completed (clocked out)
- 9: Cancelled

### Location Tracking
- Supports GPS coordinates (latitude/longitude)
- Optional location names for user reference
- IP address tracking for security
- Configurable location requirements

### Permission Model
- `timeclock.read`: View own records
- `timeclock.write`: Create/edit own records  
- `timeclock.readall`: View all users' records (managers)
- `timeclock.validate`: Approve time entries
- `timeclock.export`: Generate reports

## Common Development Tasks

### Adding New API Endpoints
1. Add method to `TimeclockAPI` class in `api/timeclock.php`
2. Add route in the switch statement for GET/POST
3. Follow existing pattern: permissions check → input validation → business logic → JSON response

### Adding New Time Record Fields
1. Modify `sql/llx_appmobtimetouch_timeclockrecord.sql`
2. Update `TimeclockRecord` class properties and methods
3. Add field to templates and JavaScript if user-facing

### Extending Mobile Interface
1. Modify `tpl/home.tpl` for UI components
2. Update `js/timeclock-api.js` for JavaScript logic  
3. Add new API endpoints as needed
4. Update `home.php` for server-side data preparation

### Debug Issues
- Enable Dolibarr debug logging
- Check browser console for JavaScript errors
- Review `home.php` extensive debug logging
- Use `dol_syslog()` for custom logging

## Code Quality Standards

### Error Handling
- Always check return values from database operations
- Use `$this->error` property to store error messages
- Return negative values for errors, positive for success
- Log errors with `dol_syslog(..., LOG_ERR)`

### Security
- Validate all user inputs with `GETPOST()`
- Check permissions before any data operations
- Use CSRF tokens for state-changing operations
- Escape output with appropriate Dolibarr functions

### Mobile Compatibility
- Test on multiple screen sizes
- Handle offline scenarios gracefully
- Optimize for touch interfaces
- Consider mobile data limitations for API calls