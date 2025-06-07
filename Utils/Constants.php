<?php
/**
 * Constants for AppMobTimeTouch module
 */

// Timeclock record statuses
define('TIMECLOCK_STATUS_DRAFT', 0);
define('TIMECLOCK_STATUS_VALIDATED', 1);
define('TIMECLOCK_STATUS_ACTIVE', 2);
define('TIMECLOCK_STATUS_COMPLETED', 3);
define('TIMECLOCK_STATUS_CANCELLED', 9);

// Default configuration values
define('TIMECLOCK_DEFAULT_MAX_HOURS_PER_DAY', 12);
define('TIMECLOCK_DEFAULT_OVERTIME_THRESHOLD', 8);
define('TIMECLOCK_DEFAULT_BREAK_DURATION', 30);

?>