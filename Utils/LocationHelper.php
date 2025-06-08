<?php
/**
 * Helpers géolocalisation - AppMobTimeTouch
 * Responsabilité unique : Validation et manipulation coordonnées GPS
 * Ouvert extension : Nouvelles fonctions géographiques sans modification
 * 
 * Principes SOLID appliqués :
 * - SRP : Responsabilité unique pour les opérations de géolocalisation
 * - OCP : Ouvert à l'extension (nouvelles méthodes de calcul géographique)
 * - LSP : Méthodes statiques substituables
 * - ISP : Interface spécialisée pour la géolocalisation uniquement
 * - DIP : Pas de dépendances externes, fonctions pures
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

/**
 * Classe helper pour les opérations de géolocalisation
 */
class LocationHelper 
{
    /**
     * Valide des coordonnées GPS
     * 
     * @param float|null $lat Latitude
     * @param float|null $lon Longitude
     * @return bool True si les coordonnées sont valides
     */
    public static function validateCoordinates(?float $lat, ?float $lon): bool 
    {
        if ($lat === null || $lon === null) {
            dol_syslog("LocationHelper::validateCoordinates - Null coordinates provided", LOG_DEBUG);
            return false;
        }
        
        $isValid = $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180;
        
        if (!$isValid) {
            dol_syslog("LocationHelper::validateCoordinates - Invalid coordinates: lat=$lat, lon=$lon", LOG_WARNING);
        } else {
            dol_syslog("LocationHelper::validateCoordinates - Valid coordinates: lat=$lat, lon=$lon", LOG_DEBUG);
        }
        
        return $isValid;
    }
    
    /**
     * Calcule distance entre deux points GPS (formule Haversine)
     * 
     * @param float $lat1 Latitude point 1
     * @param float $lon1 Longitude point 1
     * @param float $lat2 Latitude point 2
     * @param float $lon2 Longitude point 2
     * @return float Distance en mètres
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float 
    {
        // Validation des coordonnées
        if (!self::validateCoordinates($lat1, $lon1) || !self::validateCoordinates($lat2, $lon2)) {
            dol_syslog("LocationHelper::calculateDistance - Invalid coordinates provided", LOG_ERROR);
            return 0.0;
        }
        
        $earthRadius = 6371000; // Rayon de la Terre en mètres
        
        // Conversion en radians
        $latRad1 = deg2rad($lat1);
        $latRad2 = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);
        
        // Formule Haversine
        $a = sin($deltaLat/2) * sin($deltaLat/2) + 
             cos($latRad1) * cos($latRad2) * 
             sin($deltaLon/2) * sin($deltaLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        $distance = $earthRadius * $c;
        
        dol_syslog("LocationHelper::calculateDistance - Distance between ($lat1,$lon1) and ($lat2,$lon2): {$distance}m", LOG_DEBUG);
        
        return round($distance, 2);
    }
    
    /**
     * Formate coordonnées pour affichage
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param int $precision Nombre de décimales (défaut: 6)
     * @return string Coordonnées formatées "lat, lon"
     */
    public static function formatCoordinates(float $lat, float $lon, int $precision = 6): string 
    {
        if (!self::validateCoordinates($lat, $lon)) {
            return 'Coordonnées invalides';
        }
        
        return sprintf("%.{$precision}f, %.{$precision}f", $lat, $lon);
    }
    
    /**
     * Vérifie si un point GPS est dans une zone de travail autorisée
     * 
     * @param float $lat Latitude du point à vérifier
     * @param float $lon Longitude du point à vérifier
     * @param array $workAreas Tableau des zones autorisées [['lat' => float, 'lon' => float, 'radius' => int]]
     * @return bool True si le point est dans une zone autorisée
     */
    public static function isWithinWorkArea(float $lat, float $lon, array $workAreas): bool 
    {
        if (!self::validateCoordinates($lat, $lon)) {
            return false;
        }
        
        if (empty($workAreas)) {
            dol_syslog("LocationHelper::isWithinWorkArea - No work areas defined, allowing all locations", LOG_DEBUG);
            return true; // Aucune restriction si pas de zones définies
        }
        
        foreach ($workAreas as $area) {
            if (!isset($area['lat'], $area['lon'], $area['radius'])) {
                dol_syslog("LocationHelper::isWithinWorkArea - Invalid work area definition", LOG_WARNING);
                continue;
            }
            
            $distance = self::calculateDistance($lat, $lon, $area['lat'], $area['lon']);
            
            if ($distance <= $area['radius']) {
                dol_syslog("LocationHelper::isWithinWorkArea - Point ($lat,$lon) is within work area (distance: {$distance}m, radius: {$area['radius']}m)", LOG_DEBUG);
                return true;
            }
        }
        
        dol_syslog("LocationHelper::isWithinWorkArea - Point ($lat,$lon) is outside all work areas", LOG_INFO);
        return false;
    }
    
    /**
     * Récupère une adresse approximative à partir de coordonnées (geocoding inverse simple)
     * Note: Cette fonction nécessiterait une API externe pour un vrai geocoding
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return string Description approximative de la localisation
     */
    public static function getApproximateAddress(float $lat, float $lon): string 
    {
        if (!self::validateCoordinates($lat, $lon)) {
            return 'Localisation inconnue';
        }
        
        // Pour l'instant, retourne simplement les coordonnées formatées
        // Dans une implémentation future, on pourrait intégrer une API de geocoding
        return 'Position: ' . self::formatCoordinates($lat, $lon, 4);
    }
    
    /**
     * Valide la précision GPS et détermine si elle est acceptable
     * 
     * @param float $accuracy Précision en mètres
     * @param int $maxAccuracy Précision maximum acceptable (défaut depuis constantes)
     * @return bool True si la précision est acceptable
     */
    public static function isAccuracyAcceptable(float $accuracy, ?int $maxAccuracy = null): bool 
    {
        if ($maxAccuracy === null) {
            $maxAccuracy = TimeclockConstants::GPS_ACCURACY_THRESHOLD;
        }
        
        $isAcceptable = $accuracy > 0 && $accuracy <= $maxAccuracy;
        
        if (!$isAcceptable) {
            dol_syslog("LocationHelper::isAccuracyAcceptable - Poor GPS accuracy: {$accuracy}m (max: {$maxAccuracy}m)", LOG_WARNING);
        } else {
            dol_syslog("LocationHelper::isAccuracyAcceptable - Good GPS accuracy: {$accuracy}m", LOG_DEBUG);
        }
        
        return $isAcceptable;
    }
    
    /**
     * Nettoie et valide une chaîne de localisation textuelle
     * 
     * @param string $location Nom de lieu saisi par l'utilisateur
     * @return string Lieu nettoyé et validé
     */
    public static function sanitizeLocationName(string $location): string 
    {
        // Nettoyage basique
        $cleaned = trim($location);
        $cleaned = strip_tags($cleaned);
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
        
        // Limite de longueur
        if (strlen($cleaned) > 255) {
            $cleaned = substr($cleaned, 0, 255);
            dol_syslog("LocationHelper::sanitizeLocationName - Location name truncated", LOG_INFO);
        }
        
        return $cleaned;
    }
    
    /**
     * Détermine le fuseau horaire approximatif basé sur la longitude
     * Note: Approximation simple, ne tient pas compte des zones horaires complexes
     * 
     * @param float $lon Longitude
     * @return string Décalage UTC approximatif (ex: "+02:00")
     */
    public static function getApproximateTimezone(float $lon): string 
    {
        if (!self::validateCoordinates(0, $lon)) {
            return '+00:00';
        }
        
        // Calcul approximatif : 15° de longitude = 1h de décalage
        $timezoneOffset = round($lon / 15);
        
        // Limitation entre -12 et +12
        $timezoneOffset = max(-12, min(12, $timezoneOffset));
        
        $sign = $timezoneOffset >= 0 ? '+' : '';
        return sprintf('%s%02d:00', $sign, abs($timezoneOffset));
    }
    
    /**
     * Convertit coordonnées GPS vers format de stockage standardisé
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array Format ['latitude' => float, 'longitude' => float, 'formatted' => string]
     */
    public static function standardizeCoordinates(float $lat, float $lon): array 
    {
        if (!self::validateCoordinates($lat, $lon)) {
            return [
                'latitude' => null,
                'longitude' => null,
                'formatted' => null,
                'valid' => false
            ];
        }
        
        return [
            'latitude' => round($lat, 8),    // Précision suffisante pour le GPS
            'longitude' => round($lon, 8),
            'formatted' => self::formatCoordinates($lat, $lon),
            'valid' => true
        ];
    }
}