# SPRINT 1 - MVP Core - Plan Détaillé

## Vue d'Ensemble
**Durée:** 2-3 semaines  
**Objectif:** Créer un système de pointage fonctionnel minimal  
**Livrables:** Application mobile permettant le pointage de base avec interface utilisateur complète

## User Stories Détaillées

### US1: Pointage Arrivée/Départ
**En tant qu'employé, je veux pouvoir pointer mon arrivée et départ**

**Critères d'acceptation:**
- Je peux cliquer sur "Pointer Arrivée" quand je ne suis pas pointé
- Je peux cliquer sur "Pointer Départ" quand je suis pointé
- Le système enregistre l'heure exacte de pointage
- Le système sélectionne automatiquement un type de travail par défaut
- Je reçois une confirmation visuelle de mon action
- En cas d'erreur, je reçois un message explicite

**Contraintes techniques:**
- Utilisation de l'horodatage serveur pour éviter les manipulations
- Validation côté serveur des règles métier
- Interface mobile responsive

### US2: Visualisation du Statut
**En tant qu'employé, je veux voir mon statut actuel (pointé/non pointé)**

**Critères d'acceptation:**
- L'écran d'accueil affiche clairement si je suis pointé ou non
- Si pointé: affichage de l'heure de pointage et durée écoulée
- Si non pointé: affichage du dernier pointage ou message d'accueil
- Le statut se met à jour automatiquement
- Design différencié (couleurs/icônes) selon le statut

### US3: Suivi des Heures Quotidiennes
**En tant qu'employé, je veux voir mes heures du jour**

**Critères d'acceptation:**
- Affichage du temps travaillé aujourd'hui
- Affichage des heures de début et fin si applicable
- Historique des pointages du jour (entrées/sorties multiples)
- Calcul automatique du temps total
- Mise à jour en temps réel

## Tâches Techniques Détaillées

### 1. Interface Mobile de Base

#### 1.1 Finalisation de home.php
**Fichiers concernés:** `home.php`

**Modifications nécessaires:**
- Récupération du statut de pointage utilisateur actuel
- Calcul des heures travaillées aujourd'hui
- Récupération des types de pointage disponibles
- Gestion des erreurs et messages utilisateur

**APIs à appeler:**
```php
// Pseudo-code des fonctions à implémenter
$active_record = $timeclockrecord->getActiveRecord($user->id);
$today_summary = $timeclockrecord->getTodaySummary($user->id);
$available_types = TimeclockType::getActiveTypes($db);
```

#### 1.2 Finalisation de home.tpl
**Fichiers concernés:** `tpl/home.tpl`

**Améliorations nécessaires:**
- Fonctions JavaScript opérationnelles pour clockIn() et clockOut()
- Interface dynamique selon le statut
- Gestion des états de chargement
- Messages d'erreur/succès
- Sélection du type de pointage

**Composants UI à finaliser:**
- Boutons de pointage avec états visuels différents
- Compteur temps réel pour la durée de travail
- Modal de sélection du type de travail
- Indicateurs de statut (couleurs, icônes)

#### 1.3 Fonctions JavaScript de Pointage
**Fichiers concernés:** `js/timeclock.js` (nouveau fichier)

**Fonctions à implémenter:**
```javascript
// Fonctions principales
async function clockIn(typeId)
async function clockOut()
async function getCurrentStatus()
async function getTodaySummary()

// Fonctions utilitaires
function updateStatusDisplay()
function updateDurationTimer()
function showMessage(message, type)
function validateClockAction()
```

#### 1.4 Gestion des États UI
**Composants à implémenter:**
- État "Non pointé" : bouton vert "Pointer Arrivée"
- État "Pointé" : bouton rouge "Pointer Départ" + timer
- État "Chargement" : spinner + boutons désactivés
- État "Erreur" : message + possibilité de retry

### 2. APIs Backend Essentielles

#### 2.1 Endpoint Clock In/Out
**Fichier:** `api/timeclock.php` (nouveau fichier)

**Endpoints à créer:**
```php
POST /api/timeclock/clockin
{
    "timeclock_type_id": 1,
    "location": "Bureau principal",
    "latitude": 48.8566,
    "longitude": 2.3522,
    "note": ""
}

POST /api/timeclock/clockout
{
    "location": "Bureau principal", 
    "latitude": 48.8566,
    "longitude": 2.3522,
    "note": ""
}
```

**Logique métier:**
- Validation des permissions utilisateur
- Vérification des règles de pointage (pas déjà pointé, etc.)
- Enregistrement en base avec TimeclockRecord
- Retour JSON avec statut et données

#### 2.2 Endpoint Statut Utilisateur
**Endpoint:**
```php
GET /api/timeclock/status
```

**Réponse:**
```json
{
    "is_clocked_in": true,
    "current_record": {
        "id": 123,
        "clock_in_time": "2025-01-15 08:30:00",
        "type": "OFFICE",
        "type_label": "Travail au bureau",
        "duration_seconds": 7200
    },
    "today_summary": {
        "total_hours": 6.5,
        "records_count": 1,
        "start_time": "08:30",
        "end_time": null
    }
}
```

#### 2.3 Endpoint Résumé Journalier  
**Endpoint:**
```php
GET /api/timeclock/today-summary
```

**Réponse:**
```json
{
    "date": "2025-01-15",
    "total_hours": 7.5,
    "total_breaks": 60,
    "records": [
        {
            "clock_in": "08:30:00",
            "clock_out": "17:00:00", 
            "type": "OFFICE",
            "duration": "8h30"
        }
    ],
    "status": "completed"
}
```

### 3. Tests MVP

#### 3.1 Tests Fonctionnels
**Scénarios de test:**

**Test 1: Pointage nominal**
1. Utilisateur non pointé accède à l'app
2. Clique sur "Pointer Arrivée"
3. Sélectionne type "Bureau"
4. Confirme → Vérification statut "Pointé"
5. Clique sur "Pointer Départ" 
6. Confirme → Vérification statut "Non pointé"

**Test 2: Gestion d'erreurs**
1. Tentative double pointage arrivée
2. Tentative pointage départ sans arrivée
3. Perte de connexion pendant pointage
4. Données invalides

**Test 3: Calculs de durée**
1. Vérification calcul temps en cours
2. Vérification calcul temps total journée
3. Vérification affichage formaté (heures/minutes)

#### 3.2 Tests Techniques
**Performance:**
- Temps de réponse API < 2 secondes
- Chargement page < 3 secondes
- Responsive sur mobile/tablette

**Sécurité:**
- Validation tokens CSRF
- Contrôle permissions utilisateur
- Sanitisation données entrée

#### 3.3 Tests d'Intégration
- Compatibilité navigateurs mobiles
- Test offline/online
- Intégration avec l'authentification Dolibarr

## Planning Sprint 1

### Semaine 1
**Jours 1-2:** Backend APIs
- Création fichier api/timeclock.php
- Implémentation endpoints clock in/out/status
- Tests unitaires APIs

**Jours 3-5:** Interface Mobile
- Finalisation home.php (récupération données)
- Modifications home.tpl (UI dynamique)
- JavaScript de base (appels API)

### Semaine 2  
**Jours 1-3:** Intégration et Tests
- Connexion frontend/backend
- Gestion erreurs et états
- Tests fonctionnels

**Jours 4-5:** Finitions MVP
- Polish interface utilisateur
- Optimisations performance
- Documentation technique

### Semaine 3 (si nécessaire)
**Jours 1-2:** Tests et corrections
- Tests utilisateurs
- Corrections bugs
- Optimisations finales

## Critères de Succès Sprint 1

### Critères Fonctionnels ✅
- [ ] Un employé peut pointer son arrivée
- [ ] Un employé peut pointer son départ  
- [ ] Le statut actuel est affiché correctement
- [ ] Les heures du jour sont calculées et affichées
- [ ] L'interface est responsive sur mobile

### Critères Techniques ✅
- [ ] APIs sécurisées et performantes
- [ ] Code respectant les standards Dolibarr
- [ ] Gestion d'erreurs robuste
- [ ] Interface utilisateur intuitive
- [ ] Tests fonctionnels passants

### Critères de Qualité ✅
- [ ] Code documenté et maintenable
- [ ] Respect de l'architecture existante
- [ ] Compatibilité multi-navigateurs
- [ ] Performance acceptable (< 3s chargement)

## Risques et Mitigation

### Risques Identifiés
1. **Complexité intégration Dolibarr**
   - *Mitigation:* Tests d'intégration précoces
   
2. **Interface mobile non responsive**
   - *Mitigation:* Tests sur devices réels
   
3. **Performance APIs**
   - *Mitigation:* Optimisation requêtes SQL

4. **Gestion des erreurs réseau**
   - *Mitigation:* Implémentation retry et offline

## Livrables Sprint 1

### Code
- [ ] Fichier `api/timeclock.php` complet
- [ ] Fichier `home.php` finalisé  
- [ ] Template `home.tpl` avec UI dynamique
- [ ] JavaScript `timeclock.js` opérationnel

### Documentation
- [ ] Documentation API endpoints
- [ ] Guide utilisation interface
- [ ] Procédures de test

### Tests
- [ ] Suite de tests fonctionnels
- [ ] Rapports de tests performance
- [ ] Validation cross-browser