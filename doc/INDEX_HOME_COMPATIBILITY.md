# Documentation Compatibilité index.php ↔ home.php

## Vue d'ensemble

Le module AppMobTimeTouch utilise **deux points d'entrée** qui partagent les mêmes templates, nécessitant une gestion particulière lors du développement.

## Architecture des Points d'Entrée

### index.php - Interface Mobile Principale
```
index.php
├── Chargement Dolibarr (main.inc.php)
├── Chargement helpers SOLID (Constants, TimeHelper, LocationHelper)
├── Initialisation variables template par défaut
├── Build dynamique templates (opendir tpl/)
└── Include direct home.tpl (ligne 278)
```

### home.php - Page Logique Métier
```
home.php  
├── Chargement Dolibarr (main.inc.php)
├── Chargement helpers SOLID (Constants, TimeHelper, LocationHelper)
├── Traitement actions (clockin, clockout)
├── Récupération données utilisateur
├── Calculs temps/résumés
└── Include home.tpl avec données réelles
```

## Dépendances Partagées CRITIQUES

### 1. Helpers SOLID (OBLIGATOIRES)
**Requis dans les DEUX fichiers** :
```php
// Load SOLID architecture components
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';
```

**Raison** : Template `home.tpl` utilise `TimeHelper::convertSecondsToReadableTime()`

### 2. Variables Template Minimales
**Variables OBLIGATOIRES dans index.php** (valeurs par défaut) :
```php
// Variables essentielles pour compatibilité template
$is_clocked_in = false;
$clock_in_time = null;
$current_duration = 0;
$active_record = null;
$today_total_hours = 0;
$today_total_breaks = 0;
$weekly_summary = null;
$recent_records = array();
$timeclock_types = array();
$default_type_id = 1;
$overtime_threshold = 8;
$overtime_alert = false;
$errors = array();
$messages = array();
```

## Flux d'Utilisation

### Scénario 1 : Navigation Mobile (index.php)
1. Utilisateur accède via `/appmobtimetouch/index.php`
2. Interface mobile OnsenUI chargée
3. Templates buildés dynamiquement avec variables par défaut
4. **home.tpl affiché avec données statiques** (pas d'actions)

### Scénario 2 : Logique Métier (home.php)
1. Utilisateur accède via `/appmobtimetouch/home.php`
2. Actions traitées (clockin/clockout)
3. Données utilisateur récupérées
4. **home.tpl affiché avec données réelles**

## Règles de Développement SOLID

### ✅ OBLIGATOIRE - Ajout Nouvelle Classe Helper
**Si vous créez un nouveau helper (ex: ValidationHelper)** :

1. **Créer dans Utils/** :
```php
// Utils/ValidationHelper.php
class ValidationHelper {
    public static function validateInput($data): bool { ... }
}
```

2. **Ajouter dans les DEUX fichiers** :
```php
// Dans index.php ET home.php
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/ValidationHelper.php';
```

### ✅ OBLIGATOIRE - Ajout Variable Template
**Si le template utilise une nouvelle variable** :

1. **Définir dans home.php** (données réelles) :
```php
$new_variable = calculateRealData();
```

2. **Initialiser dans index.php** (valeur par défaut) :
```php
$new_variable = 'default_value'; // ou array(), null, false selon contexte
```

### ✅ OBLIGATOIRE - Extension Constants
**Si vous ajoutez des constantes** :

1. **Une seule modification** dans `Utils/Constants.php`
2. **Automatiquement disponible** dans les deux fichiers
3. **Respecte principe DRY**

## Points d'Attention Développement

### ⚠️ PIÈGE - Helper Non Chargé
**Symptôme** : `Class "NewHelper" not found` dans template
**Cause** : Helper ajouté seulement dans home.php
**Solution** : Ajouter include dans index.php aussi

### ⚠️ PIÈGE - Variable Undefined
**Symptôme** : `Undefined variable $new_var` dans template via index.php  
**Cause** : Variable définie seulement dans home.php
**Solution** : Initialiser valeur par défaut dans index.php

### ⚠️ PIÈGE - Fonction Template Non Accessible
**Symptôme** : `Call to undefined function` dans template
**Cause** : Fonction définie dans home.php mais pas index.php
**Solution** : Déplacer vers helper SOLID ou dupliquer

## Checklist Développement

### Avant Modification Template
- [ ] Template utilisé par index.php ET home.php ?
- [ ] Nouvelles variables définies dans les DEUX fichiers ?
- [ ] Nouveaux helpers inclus dans les DEUX fichiers ?
- [ ] Fonctions disponibles des DEUX côtés ?

### Avant Ajout Helper
- [ ] Helper créé dans Utils/ ?
- [ ] Include ajouté dans index.php ?
- [ ] Include ajouté dans home.php ?
- [ ] Tests compatibilité sur les deux points d'entrée ?

### Avant Push Git
- [ ] `php -l index.php` sans erreur ?
- [ ] `php -l home.php` sans erreur ?
- [ ] Test chargement `/index.php` OK ?
- [ ] Test chargement `/home.php` OK ?

## Exemples Concrets

### ✅ BON : Ajout DateHelper
```php
// 1. Créer Utils/DateHelper.php
class DateHelper {
    public static function formatDate($date): string { ... }
}

// 2. Ajouter dans index.php ET home.php
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/DateHelper.php';

// 3. Utiliser dans template
<?php echo DateHelper::formatDate($some_date); ?>
```

### ❌ MAUVAIS : Helper seulement dans home.php
```php
// 1. Helper créé et inclus seulement dans home.php
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/DateHelper.php';

// 2. Template utilise helper
<?php echo DateHelper::formatDate($some_date); ?>

// 3. ERREUR via index.php : "Class DateHelper not found"
```

### ✅ BON : Variable avec Défaut
```php
// Dans home.php - données réelles
$user_stats = calculateUserStats($user->id);

// Dans index.php - valeur par défaut
$user_stats = ['total' => 0, 'average' => 0];

// Template fonctionne dans les deux cas
<?php echo $user_stats['total']; ?>
```

## Tests de Validation

### Script Test Automatique
```bash
# Vérifier syntaxe
php -l index.php && php -l home.php

# Vérifier helpers accessibles
php -r "
require_once 'Utils/Constants.php';
require_once 'Utils/TimeHelper.php'; 
echo 'Helpers OK';
"

# Vérifier variables essentielles index.php
grep -q 'today_total_hours.*=' index.php && echo 'Variables OK'
```

## Impact Architecture SOLID

### Respect Principes
- **SRP** : Chaque helper a responsabilité unique
- **OCP** : Extension sans modification (nouveaux helpers)
- **DRY** : Pas de duplication code entre fichiers
- **Consistency** : Même comportement template partout

### Évolutivité
- Nouveaux helpers automatiquement compatibles
- Variables centralisées via Constants
- Maintenance simplifiée (un seul endroit)

## Conclusion

La **compatibilité index.php ↔ home.php** est CRITIQUE pour l'architecture SOLID du module. Tout développement DOIT respecter cette dualité pour éviter les erreurs de classe/variable non trouvée.

**Règle d'or** : Tout ce qui est utilisé dans les templates DOIT être disponible depuis les DEUX points d'entrée.