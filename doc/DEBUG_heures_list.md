# DEBUG - Gestion des fuseaux horaires dans list.php

## Problématique identifiée

Dans le fichier `list.php`, les heures affichées correspondent aux heures enregistrées en base de données sans prise en compte du fuseau horaire de l'utilisateur. Dolibarr dispose de fonctions natives pour gérer les fuseaux horaires et assurer un affichage correct selon les préférences de l'utilisateur.

## Analyse du code actuel

### Localisation du problème (list.php:717-731)
```php
// Clock In - Lines 717-721
if ($obj->clock_in_time) {
    $clock_in_ts = is_string($obj->clock_in_time) ? strtotime($obj->clock_in_time) : $obj->clock_in_time;
    print dol_print_date($clock_in_ts, 'dayhour');
}

// Clock Out - Lines 729-731  
if ($obj->clock_out_time) {
    $clock_out_ts = is_string($obj->clock_out_time) ? strtotime($obj->clock_out_time) : $obj->clock_out_time;
    print dol_print_date($clock_out_ts, 'dayhour');
}
```

### Problème détecté
- Les timestamps sont convertis avec `strtotime()` qui utilise le fuseau serveur
- `dol_print_date()` est appelée avec un timestamp déjà converti, mais sans gestion du fuseau utilisateur
- Les heures affichées sont donc dans le fuseau du serveur et non de l'utilisateur connecté

## Solution recommandée : Utilisation des fonctions Dolibarr

### 1. Fonction dol_print_date() avec gestion des fuseaux

Dolibarr dispose de la fonction `dol_print_date($time, $format, $tzoutput, $outputlangs, $encodetooutput)` où :
- `$time` : timestamp ou string datetime
- `$format` : format d'affichage ('dayhour' pour date + heure)
- `$tzoutput` : fuseau horaire de sortie ('user' pour le fuseau de l'utilisateur)

### 2. Modifications à implémenter

#### Dans list.php, remplacer les lignes 717-721 par :
```php
// Clock In avec gestion fuseau utilisateur
if ($obj->clock_in_time) {
    // Utilisation directe de la valeur base sans conversion préalable
    print dol_print_date($obj->clock_in_time, 'dayhour', 'user');
}
```

#### Dans list.php, remplacer les lignes 729-731 par :
```php  
// Clock Out avec gestion fuseau utilisateur
if ($obj->clock_out_time) {
    // Utilisation directe de la valeur base sans conversion préalable
    print dol_print_date($obj->clock_out_time, 'dayhour', 'user');
}
```

### 3. Principe de la solution

- **Supprimer** les conversions manuelles avec `strtotime()`
- **Utiliser directement** la valeur de base de données dans `dol_print_date()`
- **Ajouter le paramètre** `'user'` pour le fuseau horaire de sortie
- Dolibarr gère automatiquement la conversion depuis le fuseau UTC/serveur vers le fuseau utilisateur

### 4. Avantages de cette approche

 **Conformité Dolibarr** : Utilise les fonctions natives du framework
 **Gestion automatique** : Dolibarr gère la conversion des fuseaux
 **Préférences utilisateur** : Respecte le fuseau configuré dans le profil utilisateur
 **Maintenabilité** : Code plus simple et standard
 **Cohérence** : Même affichage que les autres modules Dolibarr

### 5. Test de validation

Après modification :
1. **Configurer** un utilisateur avec un fuseau différent du serveur
2. **Créer** un enregistrement de temps
3. **Vérifier** que l'affichage dans list.php respecte le fuseau utilisateur
4. **Comparer** avec d'autres pages Dolibarr pour cohérence

### 6. Extension possible

Cette solution peut être étendue à d'autres fichiers du module où l'affichage des heures pose le même problème :
- `card.php`
- `reports.php`
- Templates de validation
- Interface mobile

## Conclusion

L'utilisation du paramètre `'user'` dans `dol_print_date()` est la solution la plus simple et la plus conforme aux standards Dolibarr pour résoudre le problème d'affichage des fuseaux horaires dans `list.php`.