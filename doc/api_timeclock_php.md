api/timeclock.php avec l'implémentation complète des endpoints API.
Fonctionnalités implémentées :
Endpoints disponibles :

GET /api/timeclock.php?action=status - Statut de pointage actuel
POST /api/timeclock.php?action=clockin - Pointage d'arrivée
POST /api/timeclock.php?action=clockout - Pointage de sortie
GET /api/timeclock.php?action=records - Enregistrements récents
GET /api/timeclock.php?action=types - Types de pointage disponibles
GET /api/timeclock.php?action=today - Résumé du jour
GET /api/timeclock.php?action=weekly - Résumé hebdomadaire

Caractéristiques principales :

✅ Authentification Dolibarr intégrée
✅ Gestion des permissions par rôle utilisateur
✅ Format JSON standardisé pour toutes les réponses
✅ Validation des données d'entrée (géolocalisation, types)
✅ Gestion d'erreurs complète avec codes HTTP appropriés
✅ Utilisation des classes existantes (TimeclockRecord, TimeclockType, etc.)
✅ Support RESTful et paramètres GET/POST