# DEBUG - Incohérence d'affichage de l'heure "Depuis" entre page d'accueil et ClockOut Modal

## Problématique identifiée

L'heure de début de pointage s'affiche différemment selon le contexte :
- **Page d'accueil** : Affiche correctement **11:38** (✅)
- **ClockOut Modal** : Affiche incorrectement **14:38** (❌)

Pour le même enregistrement (ID 67) avec `clock_in_time = 2025-08-12 09:38:20` en base MySQL.

## Analyse technique réalisée

### 🔍 État de la base de données (Record ID 67)
```sql
rowid: 67
fk_user: 2  
clock_in_time: 2025-08-12 09:38:20
UNIX_TIMESTAMP(clock_in_time): 1754984300
status: 2 (STATUS_IN_PROGRESS)
```

### 🌐 Configuration du serveur
- **MySQL timezone** : `CEST` (UTC+2)
- **PHP timezone** : `CEST` (UTC+2)  
- **Système** : `CEST` (UTC+2)

### 📊 Analyse des conversions

#### MySQL UNIX_TIMESTAMP() Behavior
```
Raw MySQL: "2025-08-12 09:38:20" (local CEST)
UNIX_TIMESTAMP(): 1754984300 
PHP date() du timestamp: 2025-08-12 09:38:20 CEST ✓
```

#### Problème de double conversion identifié
1. **MySQL** stocke `09:38:20` en heure locale CEST
2. **UNIX_TIMESTAMP()** convertit en tenant compte du CEST → `1754984300`  
3. **dol_print_date(timestamp, format, 'tzuser')** ajoute encore le décalage utilisateur
4. **Résultat** : Double application du décalage = `09:38 + 2h + 3h = 14:38` ❌

## Code Analysis

### ✅ Page d'accueil (home.php:231) - Affichage correct
```php
<?php echo dol_print_date($clock_in_time, 'hour', 'tzuser'); ?>
```
- **Résultat** : 11:38 ✅  
- **Variable source** : `$clock_in_time` du HomeController

### ❌ ClockOut Modal (ClockOutModal.tpl:31) - Affichage incorrect  
```php
<?php echo dol_print_date($clock_in_time, 'hour', 'tzuser'); ?>
```
- **Résultat** : 14:38 ❌
- **Variable source** : `$clock_in_time` du HomeController (apparemment la même)

### 🔄 Chaîne de traitement des données

#### TimeclockService::getActiveRecord()
```php
// Services/TimeclockService.php:168-169
$activeRecord = new TimeclockRecord($this->db);  
$fetchResult = $activeRecord->fetch($obj->rowid);
```

#### TimeclockRecord::fetch() - Conversion automatique
```php
// class/timeclockrecord.class.php:348-355
if (is_string($this->clock_in_time)) {
    $sql = "SELECT UNIX_TIMESTAMP('" . $this->db->escape($this->clock_in_time) . "') as unix_ts";
    // ... 
    $this->clock_in_time = (int) $obj->unix_ts; // Conversion vers timestamp Unix
}
```

#### HomeController::preparePageData()
```php  
// Controllers/HomeController.php:183
$clockInTime = $activeRecord->clock_in_time; // Timestamp Unix converti
```

## Incohérence détectée

**Paradoxe** : Les deux contextes (page d'accueil et ClockOut modal) utilisent apparemment :
- La même variable `$clock_in_time`
- Le même code `dol_print_date($clock_in_time, 'hour', 'tzuser')`  
- Mais affichent des heures différentes !

### 🤔 Hypothèses à vérifier

1. **Variables homonymes** : Il pourrait y avoir deux variables `$clock_in_time` différentes dans `home.php`
2. **Contexte de template** : Les templates pourraient recevoir des données différentes
3. **Cache/Session** : Problème de mise en cache des données
4. **Ordre d'exécution** : Les variables pourraient être modifiées entre les deux affichages

### 📝 Observations supplémentaires

#### Variables détectées dans home.php
- Ligne 290 : `$clock_in_time = is_object($record) ? ($record->clock_in_time ?? '') : ($record['clock_in_time'] ?? '');` (pour liste récente)
- Variable du contrôleur : `$clock_in_time` (pour statut principal et modal)

## Solutions proposées

### Solution 1 : Utiliser 'tzserver' pour timestamps convertis
```php
// ClockOutModal.tpl:31
<?php echo dol_print_date($clock_in_time, 'hour', 'tzserver'); ?>
```

### Solution 2 : Passer la string MySQL brute (comme list.php)
```php
// HomeController.php - Passer la valeur brute au lieu du timestamp converti
$clockInTime = $activeRecord->getRawClockInTime(); // String MySQL
```

### Solution 3 : Corriger TimeclockRecord::fetch()
Éviter la double conversion en gérant mieux les formats de données.

## Actions à effectuer

1. **Vérifier quelle variable exacte** est utilisée dans chaque contexte
2. **Tracer le flux de données** de la base jusqu'à l'affichage
3. **Tester les solutions proposées** dans un environnement de développement
4. **Harmoniser** la logique d'affichage des heures dans tout le module

## État des corrections timezone précédentes

Les corrections précédentes ont été appliquées avec succès dans :
- ✅ `list.php` : Utilise `dol_print_date(..., 'tzuser')` avec string MySQL brute
- ✅ `card.php` : Utilise `dol_print_date(..., 'tzuser')` avec string MySQL brute  
- ✅ Templates de validation : Paramètre `'tzuser'` ajouté

Le problème actuel semble être spécifique au **pipeline de données** du HomeController vers les templates.

## Priorité

**ÉLEVÉE** - Cette incohérence peut créer de la confusion chez les utilisateurs et affecter la fiabilité perçue du système de pointage.