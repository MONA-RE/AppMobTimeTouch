
<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
dol_include_once('/appmobtimetouch/class/timeclockconfig.class.php');

class ConfigService 
{
    private $db;
    private $cache = [];
    
    public function __construct($db) 
    {
        $this->db = $db;
    }
    
    public function getRequireLocation() 
    {
        return $this->getValue('REQUIRE_LOCATION', 0);
    }
    
    public function getMaxHoursPerDay() 
    {
        return $this->getValue('MAX_HOURS_PER_DAY', TIMECLOCK_DEFAULT_MAX_HOURS_PER_DAY);
    }
    
    public function getOvertimeThreshold() 
    {
        return $this->getValue('OVERTIME_THRESHOLD', TIMECLOCK_DEFAULT_OVERTIME_THRESHOLD);
    }
    
    private function getValue($key, $default) 
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = TimeclockConfig::getValue($this->db, $key, $default);
        }
        return $this->cache[$key];
    }
}