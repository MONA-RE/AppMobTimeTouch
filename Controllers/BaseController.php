<?php

/**
 * BaseController - Abstract base class for all controllers
 * 
 * Provides common functionality for request handling, security checks,
 * message management, and template data preparation.
 */
abstract class BaseController 
{
    protected $db;
    protected $user;
    protected $langs;
    protected $errors = [];
    protected $messages = [];
    
    public function __construct($db, $user, $langs) 
    {
        $this->db = $db;
        $this->user = $user;
        $this->langs = $langs;
        
        $this->initializeController();
    }
    
    /**
     * Initialize controller with security checks
     */
    protected function initializeController() 
    {
        // Security checks
        if (!isModEnabled('appmobtimetouch')) {
            accessforbidden('Module not enabled');
        }
        
        if (!$this->user->rights->appmobtimetouch->timeclock->read) {
            accessforbidden();
        }
        
        $this->debugLog("Controller initialized for user: " . $this->user->id);
    }
    
    /**
     * Add error message to the error list
     * @param string $message Error message
     */
    protected function addError($message) 
    {
        $this->errors[] = $message;
        $this->debugLog("Error added: " . $message, LOG_WARNING);
    }
    
    /**
     * Add success/info message to the message list
     * @param string $message Success/info message
     */
    protected function addMessage($message) 
    {
        $this->messages[] = $message;
        $this->debugLog("Message added: " . $message);
    }
    
    /**
     * Get common data for all templates
     * @return array Common template data
     */
    protected function getCommonData() 
    {
        return [
            'errors' => $this->errors,
            'messages' => $this->messages,
            'user' => $this->user,
            'langs' => $this->langs,
            'title' => $this->langs->trans("TimeTracking")
        ];
    }
    
    /**
     * Check if user has specific permission
     * @param string $permission Permission name (read, write, readall, validate, export)
     * @return bool True if user has permission
     */
    protected function hasPermission($permission)
    {
        switch ($permission) {
            case 'read':
                return !empty($this->user->rights->appmobtimetouch->timeclock->read);
            case 'write':
                return !empty($this->user->rights->appmobtimetouch->timeclock->write);
            case 'readall':
                return !empty($this->user->rights->appmobtimetouch->timeclock->readall);
            case 'validate':
                return !empty($this->user->rights->appmobtimetouch->timeclock->validate);
            case 'export':
                return !empty($this->user->rights->appmobtimetouch->timeclock->export);
            default:
                return false;
        }
    }
    
    /**
     * Redirect with success message
     * @param string $messageKey Translation key for success message
     * @param string $url Target URL (defaults to current page)
     */
    protected function redirectWithSuccess($messageKey, $url = null)
    {
        if ($url === null) {
            $url = $_SERVER['PHP_SELF'];
        }
        
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $redirectUrl = $url . $separator . $messageKey . '_success=1';
        
        $this->debugLog("Redirecting with success: " . $messageKey . " to " . $redirectUrl);
        
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Redirect with error message
     * @param string $messageKey Translation key for error message
     * @param string $url Target URL (defaults to current page)
     */
    protected function redirectWithError($messageKey, $url = null)
    {
        if ($url === null) {
            $url = $_SERVER['PHP_SELF'];
        }
        
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $redirectUrl = $url . $separator . $messageKey . '_error=1';
        
        $this->debugLog("Redirecting with error: " . $messageKey . " to " . $redirectUrl, LOG_ERROR);
        
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Handle redirect success messages
     */
    protected function handleRedirectMessages()
    {
        // Check for success messages
        $successMessages = ['clockin', 'clockout'];
        foreach ($successMessages as $msg) {
            if (GETPOST($msg . '_success', 'int')) {
                $this->addMessage($this->langs->trans(ucfirst($msg) . "Success"));
            }
        }
        
        // Check for error messages
        $errorMessages = ['clockin', 'clockout'];
        foreach ($errorMessages as $msg) {
            if (GETPOST($msg . '_error', 'int')) {
                $this->addError($this->langs->trans(ucfirst($msg) . "Error"));
            }
        }
    }
    
    /**
     * Validate CSRF token
     * @return bool True if token is valid
     */
    protected function validateToken()
    {
        $token = GETPOST('token', 'alphanohtml');
        return newToken() === $token;
    }
    
    /**
     * Debug logging helper
     * @param string $message Log message
     * @param int $level Log level (LOG_DEBUG by default)
     */
    protected function debugLog($message, $level = LOG_DEBUG)
    {
        // Check if dol_syslog function exists before using it
        if (function_exists('dol_syslog')) {
            $className = get_class($this);
            dol_syslog("CONTROLLER DEBUG [{$className}]: " . $message, $level);
        }
    }
    
    /**
     * Get safe parameter value with type checking
     * @param string $key Parameter key
     * @param string $type Parameter type for GETPOST
     * @param mixed $default Default value if parameter is empty
     * @return mixed Parameter value or default
     */
    protected function getParam($key, $type = 'alphanohtml', $default = null)
    {
        $value = GETPOST($key, $type);
        return !empty($value) ? $value : $default;
    }
    
    /**
     * Prepare standard template variables
     * @param array $additionalData Additional data to merge
     * @return array Complete template data
     */
    protected function prepareTemplateData($additionalData = [])
    {
        $commonData = $this->getCommonData();
        return array_merge($commonData, $additionalData);
    }
}