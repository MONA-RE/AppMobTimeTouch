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
- **Install/Update tables**: SQL files are in `sql/` directory and are executed during module activation
- **Reset data**: Execute `sql/dolibarr_allversions.sql` to recreate all tables
- **Check keys**: Review `sql/sql_timeclock_keys.sql` for database constraints

### Module Management
- **Enable module**: Go to Dolibarr admin → Modules → Search "AppMobTimeTouch" → Enable
- **Configure module**: Go to HR menu → TimeTracking → Setup
- **View logs**: Check Dolibarr syslog for debug information (all functions use `dol_syslog`)

## Current Status & Development Context

### 🎯 CURRENT STATUS (Juin 2025)
- **Sprint 2 MVP 3.2**: ✅ COMPLETED - Validation manager with individual actions fully functional
- **ViewRecord() Employee**: ✅ COMPLETED - Full navigation from RecordsList to details
- **Function Directory**: ✅ COMPLETED - Complete function directory for code reuse (200+ functions)
- **Next Priority**: MVP 3.3 (Batch validation) - Validation en lot functionality

### 📋 Recent Session Summary (11 Juin 2025)
**Tasks Accomplished:**
1. ✅ **Validation buttons issue resolved**: Fixed approve/reject/partial buttons visibility
2. ✅ **Dashboard inconsistency fixed**: Records now show correct pending status
3. ✅ **Complete function directory created**: 200+ functions catalogued in `doc/annuaire_fonctions.md`
4. ✅ **Development workflow established**: Mandatory consultation before creating new functions

**Key Technical Fixes:**
- Corrected validation logic in ValidationController using proper `getPendingValidations()` method
- Fixed validation status logic: `validated_by > 0` for approved records
- Resolved PHP warnings by ensuring complete validation_status structure
- AJAX validation actions now fully functional with correct URLs and headers

## SOLID Architecture Overview

⚠️ **IMPORTANT**: This module uses complete SOLID architecture. Always respect these principles when making changes.

### 🏗️ Architecture Layers (SOLID Compliant)

#### **1. Controllers (SRP + OCP + DIP)**
- **BaseController**: Abstract base with common functionality (`Controllers/BaseController.php`)
- **HomeController**: Page-specific logic with dependency injection (`Controllers/HomeController.php`)
- **ValidationController**: Manager validation logic (`Controllers/ValidationController.php`)
- **Principle**: Single responsibility, Open for extension, Dependency inversion

#### **2. Services (DIP + ISP)** 
- **TimeclockService**: Business logic for timeclock operations (`Services/TimeclockService.php`)
- **DataService**: Data access and retrieval operations (`Services/DataService.php`)
- **ValidationService**: Validation workflow logic (`Services/ValidationService.php`)
- **NotificationService**: Notification management (`Services/NotificationService.php`)
- **Interfaces**: Abstract contracts in `Services/Interfaces/`
- **Principle**: Dependency inversion through interfaces, Interface segregation

#### **3. View Components (SRP + ISP)**
- **Modular Templates**: Each component has single responsibility (`Views/components/`)
- **Messages.tpl**: Error/success message display only
- **StatusCard.tpl**: Timeclock status with sub-components
- **SummaryCard.tpl**: Daily summary display
- **WeeklySummary.tpl**: Weekly summary display
- **RecordsList.tpl**: Recent records list
- **ValidationActions.tpl**: Manager validation actions (approve/reject/partial)
- **Modal Components**: ClockInModal.tpl, ClockOutModal.tpl (Interface segregation)

#### **4. Helpers & Constants (SRP)**
- **TimeHelper**: Time calculation utilities (`Helpers/TimeHelper.php`)
- **TimeclockConstants**: Centralized configuration (`Constants/TimeclockConstants.php`)
- **ValidationConstants**: Validation workflow constants (`Constants/ValidationConstants.php`)

#### **5. Legacy Classes (Dolibarr Entities)**
- **TimeclockRecord**: Main entity for time tracking entries (`class/timeclockrecord.class.php`)
- **TimeclockType**: Work types (office, remote, mission) (`class/timeclocktype.class.php`)
- **TimeclockConfig**: Module configuration storage (`class/timeclockconfig.class.php`)
- **TimeclockBreak**: Break management (`class/timeclockbreak.class.php`)
- **WeeklySummary**: Weekly time aggregations (`class/weeklysummary.class.php`)

## 📚 Function Directory and Code Reuse

⚠️ **MANDATORY**: Before creating any new function, consult the function directory to avoid duplication and promote reuse.

### Function Directory Location
- **Complete inventory**: `doc/annuaire_fonctions.md`
- **200+ functions cataloged** across all categories
- **Updated with each session** with new functions

### Development Workflow
1. **🔍 SEARCH FIRST**: Check function directory by name or functionality
2. **📋 VERIFY CATEGORY**: Look in appropriate functional domain (Navigation, API, Services, etc.)
3. **🎯 CHECK INTERFACES**: Identify available interfaces for extension
4. **⚡ REUSE WHEN POSSIBLE**: Prefer existing functions over new ones

### Decision Matrix
| Scenario | Action | Example |
|----------|--------|---------|
| **✅ Function exists** | Reuse directly | `TimeHelper::formatDuration($minutes)` |
| **🔧 Similar function exists** | **Propose 3 options**: <br>1. Extend with optional params<br>2. Create overloaded version<br>3. Refactor to generalize | Discuss modification strategy |
| **🆕 No similar function** | Create new following SOLID principles | Add to appropriate service/helper |

### Function Categories in Directory
- **🎯 Navigation JS** (10 functions) - Mobile navigation system
- **⏰ API TimeClock JS** (35+ methods) - Complete API module  
- **🏗️ Controllers SOLID** (21 methods) - Page controllers with DIP
- **🔧 Services Business** (45+ methods) - Core business logic
- **🛠️ Helpers/Utils** (25 static functions) - Reusable utilities
- **📊 Entities/Models** (15+ CRUD methods) - Data models
- **🌐 API/Endpoints** (12 endpoints) - REST API
- **🎯 SOLID Interfaces** (30 contracts) - DIP + ISP contracts

### Examples of Reuse
```php
// ✅ Reuse existing time formatting
$readable = TimeHelper::convertSecondsToReadableTime(3600); // "1h 00"

// ✅ Reuse existing validation logic  
$isValid = LocationHelper::validateCoordinates(48.8566, 2.3522);

// ✅ Reuse existing service methods
$pendingRecords = $validationService->getPendingValidations($managerId);
```

### When Creating New Functions
- **Follow SOLID principles** (see guidelines below)
- **Respect existing naming conventions**
- **Add appropriate interfaces** if needed
- **Include unit tests**
- **Update function directory**

## Database Schema
- `llx_timeclock_records`: Main time entries with clock in/out, geolocation, status
- `llx_timeclock_types`: Configurable work types (office, remote, etc.)
- `llx_timeclock_config`: Module settings and parameters
- `llx_timeclock_breaks`: Break periods within work sessions
- `llx_timeclock_weekly_summary`: Pre-calculated weekly summaries

### Status Management
Time records have status workflow:
- 0: Draft
- 1: Validated  
- 2: In Progress (clocked in)
- 3: Completed (clocked out)
- 9: Cancelled

### Validation Workflow
- **validated_by**: User ID of validator (0 = not validated, >0 = validated)
- **validated_date**: Timestamp of validation
- **validation_comment**: Optional comment from validator
- **validation_status**: Derived status (PENDING/APPROVED/REJECTED/PARTIAL)

## API Architecture
- **REST API**: `api/timeclock.php` provides RESTful endpoints
- **Validation API**: `api/validation.php` for manager validation workflows
- **Endpoints**: 
  - GET `/status` - Current user clock status
  - POST `/clockin` - Clock in with location
  - POST `/clockout` - Clock out
  - GET `/records` - Time records history
  - GET `/types` - Available work types
  - POST `/validate` - Validate time records (managers)
- **Security**: CSRF token validation, permission checks, input sanitization

## Mobile Interface
- **Framework**: OnsenUI for mobile UI components
- **Main entry**: `home.php` serves the mobile interface
- **Validation entry**: `validation.php` serves manager validation interface
- **Employee details**: `employee-record-detail.php` for employee record viewing
- **Template**: `tpl/home.tpl` contains the mobile UI structure
- **Validation templates**: `Views/validation/` contains manager interface components
- **JavaScript**: `js/timeclock-api.js` handles API interactions
- **Navigation**: `js/navigation.js` handles mobile navigation and page loading
- **CSS**: OnsenUI components + custom styles in `css/`

## Dolibarr Integration
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
- **Important**: Use `convertToUnixTimestamp()` method for safe timestamp conversion to avoid TypeError

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

## Operational Features

### ✅ For Managers (MVP 3.2 Complete):
- Dashboard validation with real-time statistics
- List of pending validations with priorities
- Individual validation (approve/reject/partial) with comments
- Automatic anomaly detection (overtime, missing clock-out, etc.)
- Mobile responsive interface with smooth navigation
- AJAX actions with immediate user feedback

### ✅ For Employees:
- Detailed consultation of their own records
- Navigation from RecordsList.viewRecord() to details
- Anomaly display for information
- Validation status consultation
- Secure interface (access only to own data)

## 🎯 SOLID Development Guidelines

### When Adding New Features

#### 1. Follow Single Responsibility Principle (SRP)
- **Controllers**: Create specific controllers for new pages/sections
- **Services**: Separate business logic from data access
- **Components**: One template component = one UI responsibility

#### 2. Respect Open/Closed Principle (OCP)
- **Extend** existing classes instead of modifying them
- **Add** new methods to interfaces rather than changing signatures
- **Create** new components instead of modifying existing ones

#### 3. Apply Interface Segregation Principle (ISP)
- **Create** specific interfaces for new services
- **Avoid** adding unrelated methods to existing interfaces
- **Split** large interfaces into smaller, focused ones

#### 4. Use Dependency Inversion Principle (DIP)
- **Inject** dependencies through constructor
- **Depend** on interfaces, not concrete implementations
- **Use** abstract contracts for all service dependencies

### Template Component Development
- **Location**: Always in `Views/components/`
- **Naming**: Descriptive names ending in `.tpl`
- **Dependencies**: Receive data via included variables
- **Responsibility**: Single UI responsibility only

### Service Implementation Best Practices
- **Interface First**: Always create interface before implementation
- **Single Purpose**: One service = one business domain
- **Error Handling**: Consistent error management
- **Database Access**: Use DataService for all database operations

## Common Development Tasks

### Adding New API Endpoints
1. Add method to appropriate API class (`api/timeclock.php` or `api/validation.php`)
2. Add route in the switch statement for GET/POST
3. Follow existing pattern: permissions check → input validation → business logic → JSON response

### Adding New Time Record Fields
1. Modify appropriate SQL file in `sql/` directory
2. Update corresponding entity class properties and methods
3. Add field to templates and JavaScript if user-facing

### Extending Mobile Interface
1. Create new component in `Views/components/` following SRP
2. Update appropriate template to include component
3. Update JavaScript navigation if needed
4. Add new API endpoints as needed
5. Update controller for server-side data preparation

### Debug Issues
- Enable Dolibarr debug logging
- Check browser console for JavaScript errors
- Review extensive debug logging in controllers
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
- Use CSRF tokens for state-changing operations with `newToken()`
- Escape output with appropriate Dolibarr functions

### Mobile Compatibility
- Test on multiple screen sizes
- Handle offline scenarios gracefully
- Optimize for touch interfaces
- Consider mobile data limitations for API calls

## Next Development Priorities

### 🚀 MVP 3.3 - Batch Validation (HIGH PRIORITY)
**Objective**: Enable simultaneous validation of multiple records
- Add checkboxes in dashboard.tpl for multiple selection
- Create batch validation interface with grouped actions
- Complete ValidationController.batchValidate() method
- Add "Approve All", "Reject All" buttons
- Confirmation interface for batch actions
- Testing and validation of interface

### MVP 4.3 - Anomaly Components (MEDIUM PRIORITY)
**Objective**: Specialized components for anomaly management
- Create AnomalyCard.tpl with priority levels
- Create ManagerAlert.tpl for manager notifications
- Integrate in dashboard with color codes
- Filtering system by anomaly type

### MVP 5.2 - Navigation and Quick Actions (MEDIUM PRIORITY)
**Objective**: Intuitive navigation between all sections
- Bottom navigation with icons
- Quick actions from dashboard
- Keyboard shortcuts for managers
- Mobile UX improvements

## Testing Commands
```bash
# Test services
cd test/phpunit && phpunit timeclockrecordTest.php

# Test API endpoints
php test/api_timeclock_test.php

# Test functional features
cd test/phpunit && phpunit AppMobTimeTouchFunctionalTest.php
```

## Important File Locations

| Type | Location | Example |
|------|----------|---------|
| Controllers | `Controllers/` | `Controllers/ValidationController.php` |
| Services | `Services/` | `Services/ValidationService.php` |
| Interfaces | `Services/Interfaces/` | `Services/Interfaces/ValidationServiceInterface.php` |
| Components | `Views/components/` | `Views/components/ValidationActions.tpl` |
| Validation Views | `Views/validation/` | `Views/validation/dashboard.tpl` |
| Helpers | `Helpers/` | `Helpers/TimeHelper.php` |
| Constants | `Constants/` | `Constants/ValidationConstants.php` |
| API | `api/` | `api/validation.php` |
| JavaScript | `js/` | `js/navigation.js`, `js/timeclock-api.js` |
| Database Config | `../../conf/` | `conf/conf.php` |

Remember: **Always follow SOLID principles and consult the function directory before creating new functions** - they make the code maintainable, testable, and extensible! 🎯