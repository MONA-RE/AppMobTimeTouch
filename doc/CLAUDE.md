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

### 🎯 CURRENT STATUS (Décembre 2025)
- **Sprint 2 MVP 3.3**: ✅ COMPLETED - Complete validation management with advanced filtering
- **Batch Validation**: ✅ COMPLETED - Multi-record validation with checkboxes and batch actions
- **Advanced Filtering**: ✅ COMPLETED - Dedicated list page with comprehensive filters
- **ViewRecord() Employee**: ✅ COMPLETED - Full navigation from RecordsList to details
- **Function Directory**: ✅ COMPLETED - Complete function directory for code reuse (200+ functions)
- **Internationalization**: ✅ COMPLETED - Full bilingual support (FR/EN) for all MVP 3.3 features
- **Next Priority**: Sprint 3 - Advanced reporting and analytics

### 📋 Recent Session Summary (6 Décembre 2025)
**Major MVP 3.3 Implementation Completed:**
1. ✅ **Dedicated validation list page**: Complete `list-all.tpl` with responsive filter interface
2. ✅ **Advanced filtering system**: Status, user, date range, anomaly, and sorting filters
3. ✅ **ValidationService.getFilteredRecords()**: Comprehensive SQL filtering with security
4. ✅ **Navigation workflow upgrade**: Dashboard → Filter Page → Record Details
5. ✅ **Batch validation interface**: Checkboxes, batch actions, confirmation dialogs
6. ✅ **Real-time statistics**: 5-column dashboard with color-coded metrics
7. ✅ **Complete internationalization**: 23 new translations for FR/EN

**Key Technical Achievements:**
- Implemented SOLID-compliant ValidationService.getFilteredRecords() with advanced SQL filtering
- Created responsive filter interface with collapsible panels and mobile optimization
- Updated navigation from AJAX loading to dedicated page for better UX
- Added comprehensive error handling and empty state management
- Maintained full architectural consistency with existing SOLID principles
- Complete bilingual support ensuring professional deployment readiness

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
- **Framework**: OnsenUI for mobile UI components ⚠️ **MANDATORY** - Always use OnsenUI components and APIs
- **Main entry**: `home.php` serves the mobile interface
- **Validation entry**: `validation.php` serves manager validation interface
- **Employee details**: `employee-record-detail.php` for employee record viewing
- **Template**: `tpl/home.tpl` contains the mobile UI structure
- **Validation templates**: `Views/validation/` contains manager interface components
- **JavaScript**: `js/timeclock-api.js` handles API interactions
- **Navigation**: `js/navigation.js` handles mobile navigation and page loading
- **CSS**: OnsenUI components + custom styles in `css/`
- **Component Guidelines**: See "OnsenUI Framework Guidelines" section for mandatory usage patterns

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

### ✅ For Managers (MVP 3.3 Complete):
- **Dashboard validation** with real-time statistics and 5-column metrics
- **Advanced filtering system** with dedicated list page supporting:
  - Status filtering (pending, approved, rejected, partial)
  - User-specific filtering from team members
  - Date range filtering (from/to dates)
  - Anomaly filtering (with/without anomalies)
  - Multiple sorting options (date, user, status)
- **Batch validation** with checkboxes and grouped actions (approve/reject all)
- **Individual validation** (approve/reject/partial) with comments
- **Automatic anomaly detection** (overtime, missing clock-out, extended breaks)
- **Mobile responsive interface** with smooth navigation workflow
- **Professional filtering interface** with collapsible panels and real-time statistics
- **Complete navigation flow**: Dashboard → Filter Page → Record Details
- **AJAX actions** with immediate user feedback and error handling

### ✅ For Employees:
- **Detailed consultation** of their own records with full validation status
- **Seamless navigation** from RecordsList.viewRecord() to comprehensive details
- **Anomaly display** with clear indicators and explanations
- **Validation status consultation** with manager feedback and comments
- **Secure interface** with access control limited to own data
- **Mobile-optimized** viewing experience

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

### OnsenUI Framework Guidelines

⚠️ **CRITICAL**: Always use OnsenUI native APIs and methods for UI interactions. Never use standard HTML/DOM methods when OnsenUI equivalents exist.

#### Initialization and Lifecycle
- **Always use** `ons.ready()` instead of `DOMContentLoaded` for OnsenUI component initialization
- **Check OnsenUI availability** with `typeof ons !== 'undefined'` before using OnsenUI APIs
- **Implement fallback logic** for script loading order issues:
```javascript
if (typeof ons !== 'undefined') {
  ons.ready(initializeFunction);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    function waitForOns() {
      if (typeof ons !== 'undefined') {
        ons.ready(initializeFunction);
      } else {
        setTimeout(waitForOns, 100);
      }
    }
    waitForOns();
  });
}
```

#### Component Interaction Best Practices

##### Checkboxes (`ons-checkbox`)
- **Use** `ons-checkbox` elements instead of standard HTML checkboxes
- **Access state** via `checkbox.checked` property (OnsenUI API)
- **Attach events** using `addEventListener('change', ...)` on OnsenUI elements
- **Never use** inline `onchange` attributes - always use proper event listeners
- **Select elements** with `document.querySelectorAll('ons-checkbox.classname')`
- **Data attributes** preferred over `value` attribute for record IDs:
```html
<!-- ✅ Correct -->
<ons-checkbox class="record-checkbox" data-record-id="123"></ons-checkbox>

<!-- ❌ Avoid -->
<ons-checkbox class="record-checkbox" value="123" onchange="someFunction()"></ons-checkbox>
```

##### Buttons (`ons-button`)
- **Use** `ons-button` for all interactive buttons
- **Disable/enable** via `button.disabled = true/false`
- **Style states** using `button.style.opacity` and `button.style.cursor` for visual feedback
- **Handle loading states** by disabling buttons during async operations

##### Modals (`ons-modal`)
- **Show/hide** using `modal.show()` and `modal.hide()` methods
- **Access modals** via `document.getElementById('modal-id')`
- **Clear content** when hiding modals for better UX

##### Notifications
- **Use OnsenUI notifications** for user feedback:
  - `ons.notification.alert(message)` for alerts
  - `ons.notification.confirm({message, callback})` for confirmations  
  - `ons.notification.toast(message, {timeout})` for temporary messages

#### Event Handling Patterns
```javascript
// ✅ Correct OnsenUI pattern
ons.ready(function() {
  const checkbox = document.getElementById('my-checkbox');
  checkbox.addEventListener('change', function() {
    console.log('Checkbox state:', this.checked);
  });
});

// ❌ Avoid standard DOM patterns
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('my-checkbox').onchange = function() {
    // This may not work correctly with OnsenUI
  };
});
```

#### Common Pitfalls to Avoid
1. **Script loading order**: OnsenUI must be loaded before using `ons` object
2. **Mixed APIs**: Don't mix standard HTML form APIs with OnsenUI component APIs
3. **Inline handlers**: Avoid `onclick`, `onchange` in HTML - use `addEventListener`
4. **Missing fallbacks**: Always handle cases where OnsenUI isn't loaded yet
5. **CSS selectors**: Use OnsenUI-specific selectors (`ons-checkbox`, not `input[type="checkbox"]`)

#### Template Development Rules
- **Component consistency**: All interactive elements should use OnsenUI components
- **Event delegation**: Attach events after `ons.ready()` to ensure components are initialized
- **State management**: Use OnsenUI component properties for state (not HTML attributes)
- **Responsive design**: Leverage OnsenUI's built-in responsive utilities

#### Debugging OnsenUI Issues
- **Check console** for `ons is not defined` errors
- **Verify initialization** with `console.log('OnsenUI ready')` in `ons.ready()`
- **Test component state** via browser DevTools: `$0.checked` for checkboxes
- **Validate selectors** ensure you're selecting OnsenUI elements, not wrapper HTML

This framework-first approach ensures consistent behavior across all mobile interfaces and prevents JavaScript errors from DOM/OnsenUI API conflicts.

## Next Development Priorities

### 🚀 Sprint 3 - Advanced Reporting and Analytics (HIGH PRIORITY)
**Objective**: Comprehensive reporting system for managers and administrators
- **Time tracking reports**: Daily, weekly, monthly views with export capabilities
- **Team productivity analytics**: Performance metrics and trends analysis
- **Anomaly reporting**: Automated detection patterns and alerts
- **Custom dashboard widgets**: Configurable manager dashboards
- **Export functionality**: PDF, Excel, CSV export options
- **Advanced filtering**: Multi-criteria report generation

### MVP 4.1 - Advanced Anomaly Management (MEDIUM PRIORITY)
**Objective**: Intelligent anomaly detection and management
- **Machine learning patterns**: Automated anomaly pattern recognition
- **Custom anomaly rules**: Configurable business rule engine
- **Escalation workflows**: Automatic manager notifications for critical anomalies
- **Anomaly resolution tracking**: Follow-up and resolution status

### MVP 4.2 - Enhanced Mobile Experience (MEDIUM PRIORITY)
**Objective**: Advanced mobile capabilities and offline support
- **Offline synchronization**: Work without internet connectivity
- **Push notifications**: Real-time alerts for managers and employees
- **Geofencing**: Location-based automatic clock in/out
- **Mobile app wrapper**: PWA to native app conversion

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
| Validation Views | `Views/validation/` | `Views/validation/dashboard.tpl`, `Views/validation/list-all.tpl` |
| Helpers | `Helpers/` | `Helpers/TimeHelper.php` |
| Constants | `Constants/` | `Constants/ValidationConstants.php` |
| API | `api/` | `api/validation.php` |
| JavaScript | `js/` | `js/navigation.js`, `js/timeclock-api.js` |
| Languages | `langs/` | `langs/fr_FR/appmobtimetouch.lang`, `langs/en_US/appmobtimetouch.lang` |
| Database Config | `../../conf/` | `conf/conf.php` |

Remember: **Always follow SOLID principles and consult the function directory before creating new functions** - they make the code maintainable, testable, and extensible! 🎯