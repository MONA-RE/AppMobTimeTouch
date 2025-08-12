# DEBUG - Gestion des fuseaux horaires dans list.php

## Probl�matique identifi�e

Dans le fichier `list.php`, les heures affich�es correspondent aux heures enregistr�es en base de donn�es sans prise en compte du fuseau horaire de l'utilisateur. Dolibarr dispose de fonctions natives pour g�rer les fuseaux horaires et assurer un affichage correct selon les pr�f�rences de l'utilisateur.

## Analyse du code actuel

### Localisation du probl�me (list.php:717-731)
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

### Probl�me d�tect�
- Les timestamps sont convertis avec `strtotime()` qui utilise le fuseau serveur
- `dol_print_date()` est appel�e avec un timestamp d�j� converti, mais sans gestion du fuseau utilisateur
- Les heures affich�es sont donc dans le fuseau du serveur et non de l'utilisateur connect�

## Solution recommand�e : Utilisation des fonctions Dolibarr

### 1. Fonction dol_print_date() avec gestion des fuseaux

Dolibarr dispose de la fonction `dol_print_date($time, $format, $tzoutput, $outputlangs, $encodetooutput)` o� :
- `$time` : timestamp ou string datetime
- `$format` : format d'affichage ('dayhour' pour date + heure)
- `$tzoutput` : fuseau horaire de sortie ('user' pour le fuseau de l'utilisateur)

### 2. Modifications � impl�menter

#### Dans list.php, remplacer les lignes 717-721 par :
```php
// Clock In avec gestion fuseau utilisateur
if ($obj->clock_in_time) {
    // Utilisation directe de la valeur base sans conversion pr�alable
    print dol_print_date($obj->clock_in_time, 'dayhour', 'user');
}
```

#### Dans list.php, remplacer les lignes 729-731 par :
```php  
// Clock Out avec gestion fuseau utilisateur
if ($obj->clock_out_time) {
    // Utilisation directe de la valeur base sans conversion pr�alable
    print dol_print_date($obj->clock_out_time, 'dayhour', 'user');
}
```

### 3. Principe de la solution

- **Supprimer** les conversions manuelles avec `strtotime()`
- **Utiliser directement** la valeur de base de donn�es dans `dol_print_date()`
- **Ajouter le param�tre** `'user'` pour le fuseau horaire de sortie
- Dolibarr g�re automatiquement la conversion depuis le fuseau UTC/serveur vers le fuseau utilisateur

### 4. Avantages de cette approche

 **Conformit� Dolibarr** : Utilise les fonctions natives du framework
 **Gestion automatique** : Dolibarr g�re la conversion des fuseaux
 **Pr�f�rences utilisateur** : Respecte le fuseau configur� dans le profil utilisateur
 **Maintenabilit�** : Code plus simple et standard
 **Coh�rence** : M�me affichage que les autres modules Dolibarr

### 5. Test de validation

Apr�s modification :
1. **Configurer** un utilisateur avec un fuseau diff�rent du serveur
2. **Cr�er** un enregistrement de temps
3. **V�rifier** que l'affichage dans list.php respecte le fuseau utilisateur
4. **Comparer** avec d'autres pages Dolibarr pour coh�rence

### 6. Extension possible

Cette solution peut �tre �tendue � d'autres fichiers du module o� l'affichage des heures pose le m�me probl�me :
- `card.php`
- `reports.php`
- Templates de validation
- Interface mobile

## Conclusion

L'utilisation du param�tre `'user'` dans `dol_print_date()` est la solution la plus simple et la plus conforme aux standards Dolibarr pour r�soudre le probl�me d'affichage des fuseaux horaires dans `list.php`.