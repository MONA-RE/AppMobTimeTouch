<?php
/**
 * Contrôleur base - Responsabilité unique : Fonctions communes
 * Ouvert extension : Nouveaux contrôleurs héritent
 * 
 * Respecte le principe SRP : Responsabilité unique pour les fonctions communes
 * Respecte le principe OCP : Ouvert à l'extension via héritage
 * Respecte le principe DIP : Dépend d'abstractions (interfaces Dolibarr)
 */

abstract class BaseController 
{
    protected $db;
    protected $user;
    protected $langs;
    protected $conf;
    
    /**
     * Constructor avec injection des dépendances Dolibarr
     * 
     * @param DoliDB $db Base de données Dolibarr
     * @param User $user Utilisateur courant
     * @param Translate $langs Gestionnaire de traductions
     * @param Conf $conf Configuration Dolibarr
     */
    public function __construct($db, $user, $langs, $conf) 
    {
        $this->db = $db;
        $this->user = $user;
        $this->langs = $langs;
        $this->conf = $conf;
    }
    
    /**
     * Vérification que le module est activé
     * 
     * @throws Exception Si module désactivé
     */
    protected function checkModuleEnabled(): void 
    {
        if (!isModEnabled('appmobtimetouch')) {
            accessforbidden('Module not enabled');
        }
    }
    
    /**
     * Vérification des droits utilisateur
     * 
     * @param string $permission Permission requise (read, write, readall, validate, export)
     * @throws Exception Si permission manquante
     */
    protected function checkUserRights(string $permission): void 
    {
        if (!isset($this->user->rights->appmobtimetouch->timeclock->$permission) || 
            !$this->user->rights->appmobtimetouch->timeclock->$permission) {
            accessforbidden("Missing $permission permission");
        }
    }
    
    /**
     * Gestion centralisée des erreurs
     * 
     * @param Exception $e Exception capturée
     * @return array Format standard de retour d'erreur
     */
    protected function handleError(Exception $e): array 
    {
        dol_syslog("Controller error: " . $e->getMessage(), LOG_ERROR);
        return [
            'error' => 1,
            'errors' => [$this->langs->trans($e->getMessage())]
        ];
    }
    
    /**
     * Préparation des données communes pour les templates
     * 
     * @param array $data Données spécifiques à merger
     * @return array Données complètes pour template
     */
    protected function prepareTemplateData(array $data = []): array 
    {
        return array_merge([
            'user' => $this->user,
            'langs' => $this->langs,
            'conf' => $this->conf,
            'db' => $this->db,
            'newToken' => function_exists('newToken') ? newToken() : '',
            'error' => 0,
            'errors' => [],
            'messages' => []
        ], $data);
    }
    
    /**
     * Validation des paramètres POST standards
     * 
     * @param array $requiredParams Liste des paramètres requis
     * @return array Paramètres validés ou erreurs
     */
    protected function validatePostParams(array $requiredParams): array 
    {
        $params = [];
        $errors = [];
        
        foreach ($requiredParams as $param => $type) {
            $value = GETPOST($param, $type);
            
            if ($type === 'int' && ($value === '' || $value === false)) {
                $errors[] = "Missing required parameter: $param";
            } elseif ($type !== 'int' && empty($value)) {
                $errors[] = "Missing required parameter: $param";
            } else {
                $params[$param] = $value;
            }
        }
        
        return [
            'params' => $params,
            'errors' => $errors
        ];
    }
    
    /**
     * Redirection avec message de succès
     * 
     * @param string $url URL de redirection
     * @param string $successParam Paramètre GET de succès
     */
    protected function redirectWithSuccess(string $url, string $successParam): void 
    {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        header('Location: ' . $url . $separator . $successParam . '=1');
        exit;
    }
    
    /**
     * Validation des permissions pour une action
     * 
     * @param string $action Action demandée
     * @return bool True si autorisé
     */
    protected function isActionAllowed(string $action): bool 
    {
        switch ($action) {
            case 'clockin':
            case 'clockout':
                return !empty($this->user->rights->appmobtimetouch->timeclock->write);
            
            case 'validate':
                return !empty($this->user->rights->appmobtimetouch->timeclock->validate);
                
            case 'export':
                return !empty($this->user->rights->appmobtimetouch->timeclock->export);
                
            default:
                return !empty($this->user->rights->appmobtimetouch->timeclock->read);
        }
    }
}