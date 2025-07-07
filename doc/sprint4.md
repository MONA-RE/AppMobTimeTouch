# Sprint 4 Roadmap - Dolibarr Standard Manager Interface

## 🎯 Sprint 4 Objective

Create a complete Dolibarr-standard web interface for managers to manage team time records from their desktop computers, following Dolibarr's native UI conventions and architecture patterns.

## 📋 Functional Requirements

### Core Functionality
- **View team time records** with advanced filtering and sorting
- **Create new time records** for team members (manual entry)
- **Edit existing time records** with validation workflow
- **Delete time records** with appropriate permissions
- **Bulk operations** for multiple records management
- **Export capabilities** for reporting and analysis

### User Stories

#### Manager Stories
1. **As a manager**, I want to view all time records of my team members in a standard Dolibarr list interface
2. **As a manager**, I want to create a new time record for a team member who forgot to clock in/out
3. **As a manager**, I want to edit incorrect time records with proper audit trail
4. **As a manager**, I want to delete erroneous time records with confirmation
5. **As a manager**, I want to perform bulk validation of multiple time records
6. **As a manager**, I want to export time records data to Excel/CSV
7. **As a manager**, I want to search and filter time records by multiple criteria

#### Administrator Stories
1. **As an admin**, I want to manage all time records across the organization
2. **As an admin**, I want to configure time tracking parameters
3. **As an admin**, I want to view audit logs of all time record modifications

## 🏗️ Technical Architecture

### SOLID Architecture Compliance
- **Single Responsibility**: Each page handles one specific functionality
- **Open/Closed**: Extensible for future enhancements
- **Liskov Substitution**: Proper inheritance hierarchy
- **Interface Segregation**: Specific interfaces for different operations
- **Dependency Inversion**: Service injection pattern

### Dolibarr Standards Integration
- **Native UI Components**: Use Dolibarr's standard form builders and list views
- **Permission System**: Integrate with Dolibarr's rights management
- **Menu Integration**: Add pages to Dolibarr's menu system
- **Language Support**: Full multilingual support (FR/EN)
- **Database Integration**: Use Dolibarr's database abstraction layer

## 📁 File Structure

```
/                               # Module root
├── index.php                  # Enhanced main entry point with device detection
├── list.php                   # List view of time records (Dolibarr standard)
├── card.php                   # Create/Edit form for time records
├── delete.php                 # Delete confirmation page
├── export.php                 # Export functionality
├── bulk_actions.php           # Bulk operations handler
├── ajax_actions.php           # AJAX endpoints for dynamic operations
├── class/
│   ├── TimeclockRecordManager.class.php  # Manager-specific operations
│   └── TimeclockExporter.class.php       # Export functionality
├── lib/
│   ├── timeclock.lib.php      # Common functions library (enhanced)
│   └── timeclock_manager.lib.php  # Manager-specific functions
├── tpl/
│   ├── index-desktop.tpl      # Desktop dashboard template
│   ├── list.tpl               # List template (Dolibarr standard)
│   ├── card.tpl               # Form template
│   └── export.tpl             # Export template
└── core/
    └── actions_timeclock.inc.php  # Common actions handler
```

## 🎨 Dolibarr Integration Patterns

### Index.php Enhancement Pattern
Following `/compta/facture/index.php` structure:
```php
// Standard Dolibarr entry point pattern
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

// Security check
restrictedArea($user, 'appmobtimetouch');

// Device detection and redirection
if (isMobileDevice() || isSmallScreen()) {
    header('Location: home.php');
    exit;
}

// Desktop dashboard with statistics
llxHeader("", $langs->trans("TimeClockManagement"), "");
print load_fiche_titre($langs->trans("TimeClockManagement"), '', 'clock');
// Dashboard widgets...
```

### List.php Standard Pattern
Following `/compta/facture/list.php` structure:
```php
// Standard Dolibarr list pattern
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';

// Search parameters (following Dolibarr conventions)
$search_ref = GETPOST('search_ref', 'alpha');
$search_employee = GETPOST('search_employee', 'alpha');
$search_status = GETPOST('search_status', 'int');
$search_date_start = dol_mktime(0, 0, 0, $search_date_startmonth, $search_date_startday, $search_date_startyear);

// Pagination and sorting
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');

// Array fields configuration
$arrayfields = array(
    'tr.rowid' => array('label' => 'ID', 'checked' => 1, 'position' => 5),
    'tr.clock_in' => array('label' => 'ClockIn', 'checked' => 1, 'position' => 10),
    'tr.clock_out' => array('label' => 'ClockOut', 'checked' => 1, 'position' => 15),
    'u.login' => array('label' => 'Employee', 'checked' => 1, 'position' => 20),
    'tr.status' => array('label' => 'Status', 'checked' => 1, 'position' => 25),
);
```

## 🚀 MVP Development Plan

### MVP 4.1: Desktop Interface Foundation (Week 1)
**Objective**: Create the foundation for desktop interface with proper device detection and navigation

#### MVP 4.1.1: Enhanced Index.php with Device Detection
**Features**:
- Smart device detection and redirection
- Desktop interface with Dolibarr standard layout
- Left menu integration for time records management
- Proper permission handling for desktop interface
- Mobile/desktop toggle functionality

**Technical Implementation**:
- **index.php**: Enhanced with device detection following `/compta/facture/index.php` pattern
- **Desktop template**: `tpl/index-desktop.tpl` with Dolibarr standard layout
- **Menu integration**: Add timeclock management links to left menu
- **Permission checks**: Ensure proper access control for desktop interface
- **Device detection**: Improved mobile detection with fallback options

**Interface Elements**:
- Desktop dashboard with time records overview
- Left menu with timeclock management links
- Quick statistics cards (similar to invoice overview)
- Recent activities summary
- Navigation breadcrumbs
- Device detection with manual toggle

**Validation Criteria**:
- Mobile devices redirect to home.php
- Desktop users see standard Dolibarr interface
- Left menu shows timeclock management options
- Permission-based menu visibility
- Dashboard shows relevant statistics

#### MVP 4.1.2: Standard Dolibarr List Interface ✅ COMPLETED
**Status**: ✅ **IMPLEMENTED** - Basic list functionality working with database field corrections

**Features**:
- ✅ Standard Dolibarr list view following `/compta/facture/list.php` pattern
- ✅ Advanced filtering system with multiple criteria
- ✅ Pagination with configurable limits
- ✅ Sortable columns with proper SQL ordering
- ✅ Mass actions selection
- ✅ Export functionality integration

**Technical Implementation**:
- ✅ **list.php**: Main list page following Dolibarr list conventions
- ✅ **Database field mapping**: Updated all field references to match actual schema:
  - `clock_in` → `clock_in_time`
  - `clock_out` → `clock_out_time`
  - `duration` → `work_duration`
  - `location_name` → `location_in`
- ✅ **arrayfields**: Configurable column display system
- ✅ **extrafields**: Support for custom fields in list view

**Interface Elements**:
- ✅ Search filters panel (collapsible)
- ✅ Sortable column headers
- ✅ Action buttons per record (view, edit, delete)
- ✅ Bulk selection checkboxes
- ✅ Mass action dropdown
- ✅ Export button with format options
- ✅ Pagination controls (previous/next/page selector)
- ✅ Records per page selector

**Validation Criteria**:
- ✅ List displays time records with proper formatting
- ✅ Filters work correctly (date range, employee, status)
- ✅ Sorting functions properly on all columns
- ✅ Mass actions work for selected records
- ✅ Export generates proper files
- ✅ Pagination handles large datasets
- ✅ UI matches Dolibarr standards exactly

**Issues Resolved**:
- ✅ Fixed "Unknown column 't.clock_in'" database errors
- ✅ Updated SQL queries to use correct field names
- ✅ Fixed default sort field and ORDER BY clause
- ✅ Corrected field references in index.php statistics

**🎯 Next Critical Step**: Implement proper data display formatting for `clock_in_time` and `clock_out_time` fields in list.php to ensure dates show correctly in the interface

### MVP 4.2: Create/Edit Interface (Week 2)
**Objective**: Implement create and edit functionality with validation

#### Features:
- Create new time records for team members
- Edit existing time records
- Validation rules and business logic
- Audit trail for modifications
- Status workflow management

#### Technical Implementation:
- **card.php**: Create/Edit form page
- Form validation with Dolibarr's validation system
- Integration with existing validation workflow
- Audit logging for all modifications

#### Interface Elements:
- Employee selection dropdown
- Date/time pickers
- Work type selection
- Location fields
- Status management
- Validation comments
- Save/Cancel buttons

#### Validation Criteria:
- Forms validate correctly
- Data saves properly
- Audit trail works
- Status workflow functions
- Error handling is robust

### MVP 4.3: Advanced Operations (Week 3)
**Objective**: Add bulk operations and export functionality

#### Features:
- Bulk validation/rejection
- Bulk delete with confirmation
- Export to Excel/CSV
- Advanced search capabilities
- Quick actions toolbar

#### Technical Implementation:
- **bulk_actions.php**: Handler for bulk operations
- **export.php**: Export functionality
- **TimeclockExporter.class.php**: Export service
- AJAX endpoints for dynamic operations

#### Interface Elements:
- Bulk selection interface
- Export options dialog
- Advanced search form
- Quick actions toolbar
- Progress indicators for bulk operations

#### Validation Criteria:
- Bulk operations work correctly
- Export generates proper files
- Advanced search functions
- Performance is acceptable
- User feedback is clear

### MVP 4.4: Integration & Polish (Week 4)
**Objective**: Final integration and user experience improvements

#### Features:
- Menu integration
- Help documentation
- Performance optimization
- Mobile responsiveness
- Complete internationalization

#### Technical Implementation:
- Menu entries in module configuration
- Help system integration
- Performance profiling and optimization
- Responsive CSS adjustments
- Complete language files

#### Interface Elements:
- Navigation breadcrumbs
- Help tooltips
- Loading indicators
- Responsive design elements
- Accessibility improvements

#### Validation Criteria:
- All features integrated
- Performance meets standards
- Mobile interface works
- Documentation complete
- Full multilingual support

## 📊 Database Schema Enhancements

### New Tables
```sql
-- Audit trail for time record modifications
CREATE TABLE llx_timeclock_audit (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_values TEXT,
    new_values TEXT,
    modified_by INT NOT NULL,
    modified_date DATETIME NOT NULL,
    comment TEXT,
    INDEX idx_record_id (record_id),
    INDEX idx_modified_by (modified_by),
    INDEX idx_modified_date (modified_date)
);

-- Manager-specific configurations
CREATE TABLE llx_timeclock_manager_config (
    rowid INT AUTO_INCREMENT PRIMARY KEY,
    manager_id INT NOT NULL,
    team_members TEXT,
    default_filters TEXT,
    preferences TEXT,
    created_date DATETIME NOT NULL,
    updated_date DATETIME NOT NULL,
    INDEX idx_manager_id (manager_id)
);
```

### Modified Tables
```sql
-- Add manager operation flags to existing table
ALTER TABLE llx_timeclock_records 
ADD COLUMN created_by_manager INT DEFAULT 0,
ADD COLUMN last_modified_by INT DEFAULT 0,
ADD COLUMN modification_reason TEXT,
ADD INDEX idx_created_by_manager (created_by_manager),
ADD INDEX idx_last_modified_by (last_modified_by);
```

## 🔧 Service Layer Architecture

### TimeclockRecordManager Class
```php
interface TimeclockRecordManagerInterface {
    public function getTeamRecords(int $managerId, array $filters = []): array;
    public function createRecordForEmployee(int $employeeId, array $data): int;
    public function updateRecord(int $recordId, array $data, string $reason = ''): bool;
    public function deleteRecord(int $recordId, string $reason = ''): bool;
    public function bulkValidate(array $recordIds, int $status, string $comment = ''): bool;
    public function exportRecords(array $recordIds, string $format): string;
    public function getAuditTrail(int $recordId): array;
}
```

### TimeclockExporter Class
```php
interface TimeclockExporterInterface {
    public function exportToExcel(array $records, array $options = []): string;
    public function exportToCSV(array $records, array $options = []): string;
    public function exportToPDF(array $records, array $options = []): string;
}
```

## 🎨 UI/UX Guidelines

### Dolibarr Standards Compliance
- Use Dolibarr's native CSS classes and components
- Follow Dolibarr's form building patterns
- Implement standard Dolibarr list views
- Use Dolibarr's confirmation dialogs
- Integrate with Dolibarr's notification system

### Manager-Specific UX
- Dashboard widget for quick overview
- Contextual help for complex operations
- Keyboard shortcuts for power users
- Customizable default filters
- Recent actions history

## 🔐 Security & Permissions

### Permission Matrix
| Action | Required Permission | Additional Checks |
|--------|-------------------|------------------|
| View records | `timeclock.readall` | Team membership |
| Create records | `timeclock.write` + `timeclock.create_for_others` | Team management |
| Edit records | `timeclock.write` + `timeclock.edit_others` | Team management |
| Delete records | `timeclock.delete` | Team management + audit |
| Bulk operations | `timeclock.validate` | Team management |
| Export data | `timeclock.export` | Team membership |

### Security Measures
- CSRF protection on all forms
- Input validation and sanitization
- Audit logging for all modifications
- Session timeout management
- SQL injection prevention

## 🧪 Testing Strategy

### Unit Tests
- Service layer methods
- Data validation functions
- Permission checking logic
- Export functionality

### Integration Tests
- Database operations
- Dolibarr integration
- Permission system
- Audit trail functionality

### User Acceptance Tests
- Manager workflow scenarios
- Bulk operations testing
- Export functionality validation
- Permission boundary testing

## 📈 Performance Considerations

### Optimization Targets
- Page load time < 2 seconds
- Search results < 1 second
- Export operations < 30 seconds
- Bulk operations feedback < 5 seconds

### Optimization Strategies
- Database query optimization
- Pagination for large datasets
- AJAX for dynamic operations
- Caching for frequently accessed data
- Lazy loading for non-critical elements

## 🌐 Internationalization

### Language Support
- French (FR) - Primary
- English (EN) - Secondary
- Extensible for additional languages

### Translation Files
- `langs/fr_FR/timeclock_manager.lang`
- `langs/en_US/timeclock_manager.lang`

## 📚 Documentation Requirements

### User Documentation
- Manager user guide
- Feature overview
- Common workflows
- Troubleshooting guide

### Technical Documentation
- API documentation
- Database schema
- Architecture overview
- Deployment guide

## 🚀 Deployment Strategy

### Development Environment
- Local development setup
- Unit testing environment
- Integration testing setup

### Staging Environment
- Pre-production testing
- User acceptance testing
- Performance testing

### Production Deployment
- Module activation procedure
- Database migration scripts
- Configuration guidelines
- Rollback procedures

## 📊 Success Metrics

### Functional Metrics
- All user stories completed
- 100% test coverage
- Zero critical bugs
- Performance targets met

### User Adoption Metrics
- Manager training completion
- Feature usage statistics
- User satisfaction surveys
- Support ticket reduction

## 🔄 Post-Sprint Activities

### Maintenance
- Regular security updates
- Performance monitoring
- Bug fix releases
- Feature enhancement requests

### Future Enhancements
- Advanced reporting capabilities
- Mobile manager interface
- Integration with other Dolibarr modules
- Advanced workflow automation

---

**Sprint 4 Timeline**: 4 weeks
**Team Size**: 1 developer
**Complexity**: High
**Risk Level**: Medium

This roadmap ensures a structured approach to delivering a comprehensive Dolibarr-standard manager interface while maintaining the highest standards of code quality and user experience.