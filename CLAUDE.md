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

## SOLID Architecture Overview

⚠️ **IMPORTANT**: This module now uses a complete SOLID architecture. Always respect these principles when making changes.

### 🏗️ Architecture Layers (SOLID Compliant)

#### **1. Controllers (SRP + OCP + DIP)**
- **BaseController**: Abstract base with common functionality (`Controllers/BaseController.php`)
- **HomeController**: Page-specific logic with dependency injection (`Controllers/HomeController.php`)
- **Principle**: Single responsibility, Open for extension, Dependency inversion

#### **2. Services (DIP + ISP)** 
- **TimeclockService**: Business logic for timeclock operations (`Services/TimeclockService.php`)
- **DataService**: Data access and retrieval operations (`Services/DataService.php`)
- **Interfaces**: Abstract contracts in `Services/Interfaces/`
- **Principle**: Dependency inversion through interfaces, Interface segregation

#### **3. View Components (SRP + ISP)**
- **Modular Templates**: Each component has single responsibility (`Views/components/`)
- **Messages.tpl**: Error/success message display only
- **StatusCard.tpl**: Timeclock status with sub-components
- **SummaryCard.tpl**: Daily summary display
- **WeeklySummary.tpl**: Weekly summary display
- **RecordsList.tpl**: Recent records list
- **Modal Components**: ClockInModal.tpl, ClockOutModal.tpl (Interface segregation)

#### **4. Helpers & Constants (SRP)**
- **TimeHelper**: Time calculation utilities (`Helpers/TimeHelper.php`)
- **TimeclockConstants**: Centralized configuration (`Constants/TimeclockConstants.php`)

#### **5. Legacy Classes (Dolibarr Entities)**
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

## 🎯 SOLID Development Guidelines

### When Adding New Features

#### 1. Follow Single Responsibility Principle (SRP)
- **Controllers**: Create specific controllers for new pages/sections
- **Services**: Separate business logic from data access
- **Components**: One template component = one UI responsibility
- **Example**: 
  ```php
  // ❌ Bad: Mixed responsibilities
  class HomeController {
      public function index() {
          // Database access + business logic + template rendering
      }
  }
  
  // ✅ Good: Separated responsibilities  
  class HomeController extends BaseController {
      private TimeclockServiceInterface $timeclockService;
      private DataServiceInterface $dataService;
  }
  ```

#### 2. Respect Open/Closed Principle (OCP)
- **Extend** existing classes instead of modifying them
- **Add** new methods to interfaces rather than changing signatures
- **Create** new components instead of modifying existing ones
- **Example**:
  ```php
  // ✅ Good: Extending BaseController
  class ReportsController extends BaseController {
      // New functionality without modifying base
  }
  
  // ✅ Good: New template component
  <?php include 'Views/components/NewFeature.tpl'; ?>
  ```

#### 3. Apply Interface Segregation Principle (ISP)
- **Create** specific interfaces for new services
- **Avoid** adding unrelated methods to existing interfaces
- **Split** large interfaces into smaller, focused ones
- **Example**:
  ```php
  // ✅ Good: Focused interface
  interface ReportServiceInterface {
      public function generateReport(int $userId, string $period): array;
      public function exportReport(array $data, string $format): string;
  }
  ```

#### 4. Use Dependency Inversion Principle (DIP)
- **Inject** dependencies through constructor
- **Depend** on interfaces, not concrete implementations
- **Use** abstract contracts for all service dependencies
- **Example**:
  ```php
  // ✅ Good: Dependency injection
  class ReportsController extends BaseController {
      public function __construct(
          $db, $user, $langs, $conf,
          ReportServiceInterface $reportService,
          DataServiceInterface $dataService
      ) {
          parent::__construct($db, $user, $langs, $conf);
          $this->reportService = $reportService;
          $this->dataService = $dataService;
      }
  }
  ```

### Component Development Best Practices

#### Template Components
- **Location**: Always in `Views/components/`
- **Naming**: Descriptive names ending in `.tpl`
- **Dependencies**: Receive data via included variables
- **Example**:
  ```php
  <?php
  /**
   * Composant ReportCard - Responsabilité unique : Affichage rapport
   * 
   * Respecte le principe SRP : Seule responsabilité l'affichage d'un rapport
   * Respecte le principe ISP : Interface spécialisée pour les rapports
   */
  ?>
  <ons-card>
    <div class="title"><?php echo $report_title; ?></div>
    <div class="content"><?php echo $report_content; ?></div>
  </ons-card>
  ```

#### Service Implementation
- **Interface First**: Always create interface before implementation
- **Single Purpose**: One service = one business domain
- **Error Handling**: Consistent error management
- **Example**:
  ```php
  class ReportService implements ReportServiceInterface {
      public function __construct(
          private DoliDB $db,
          private DataServiceInterface $dataService
      ) {}
      
      public function generateReport(int $userId, string $period): array {
          // Single responsibility: report generation logic only
      }
  }
  ```

### Database Access Patterns

#### Use DataService for Database Operations
```php
// ✅ Good: Through DataService
$records = $this->dataService->getRecordsByPeriod($userId, $startDate, $endDate);

// ❌ Bad: Direct database access in controller
$sql = "SELECT * FROM llx_timeclock_records WHERE...";
$result = $this->db->query($sql);
```

#### Timestamp Handling (Important!)
```php
// ✅ Good: Use helper method for safe conversion
$timestamp = $this->convertToUnixTimestamp($this->db->jdate($record->clock_in_time));
$date = date('Y-m-d', $timestamp);

// ❌ Bad: Direct conversion (can cause TypeError)
$date = date('Y-m-d', $this->db->jdate($record->clock_in_time));
```

### Template Refactoring Guidelines

#### Before Adding New UI Elements
1. **Check** if existing component can be extended
2. **Create** new component if different responsibility
3. **Update** main template to include new component
4. **Test** component isolation

#### Template Assembly Pattern
```php
<!-- Main template (home.tpl) should only assemble components -->
<?php include 'Views/components/Messages.tpl'; ?>
<?php include 'Views/components/StatusCard.tpl'; ?>
<?php include 'Views/components/SummaryCard.tpl'; ?>
<?php include 'Views/components/NewFeature.tpl'; ?>
```

### Testing New Features

#### Required Tests
- **Unit tests** for new services
- **Integration tests** for controller actions  
- **Component tests** for template rendering
- **API tests** for new endpoints

#### Test Commands
```bash
# Test new service
cd test/phpunit && phpunit NewServiceTest.php

# Test component rendering  
cd test/phpunit && phpunit ComponentRenderTest.php

# Test API endpoints
php test/api_new_feature_test.php
```

### Common Anti-Patterns to Avoid

#### ❌ Don't Do This
```php
// Mixed responsibilities in controller
class HomeController {
    public function index() {
        $sql = "SELECT * FROM..."; // Database access
        $result = $this->db->query($sql); // Direct DB
        echo "<div>"; // Template mixing
        // Business logic here
    }
}

// God object with multiple responsibilities
class TimeclockManager {
    public function clockIn() { }
    public function generateReport() { }
    public function sendEmail() { }
    public function validateData() { }
}
```

#### ✅ Do This Instead
```php
// Separated responsibilities
class HomeController extends BaseController {
    public function __construct(
        TimeclockServiceInterface $timeclockService,
        DataServiceInterface $dataService
    ) {
        // Dependency injection
    }
    
    public function index(): array {
        // Delegate to services
        $data = $this->dataService->getHomePageData($this->user->id);
        return $this->prepareTemplateData($data);
    }
}
```

### Quick Reference: File Locations

| Type | Location | Example |
|------|----------|---------|
| Controllers | `Controllers/` | `Controllers/ReportsController.php` |
| Services | `Services/` | `Services/ReportService.php` |
| Interfaces | `Services/Interfaces/` | `Services/Interfaces/ReportServiceInterface.php` |
| Components | `Views/components/` | `Views/components/ReportCard.tpl` |
| Helpers | `Helpers/` | `Helpers/ReportHelper.php` |
| Constants | `Constants/` | `Constants/ReportConstants.php` |

Remember: **Always follow SOLID principles** - they make the code maintainable, testable, and extensible! 🎯