# TODO Debug TimeClock List - Interface Dolibarr Manager

## Contexte
Debug et correction des problèmes dans l'interface Dolibarr pour la gestion des entrées timeclock par les managers.

## Problèmes identifiés

### 1. 🎨 ✅ RÉSOLU - Affichage incohérent lors de la modification d'entrée
**~~Problème~~** : ~~En tant que manager, l'interface de modification d'une entrée timeclock n'est pas cohérente avec le style Dolibarr~~
**SOLUTION APPLIQUÉE** :
- ✅ Corrigé la récupération des données `clock_in_time` et `clock_out_time` dans les modes VIEW et EDIT
- ✅ Remplacé `$db->jdate()` défaillant par `strtotime()` standard PHP 
- ✅ Corrigé l'affichage des types de travail avec la bonne table SQL `timeclock_types`
- ✅ Ajouté colonne WorkType dans la liste avec filtrage fonctionnel
**Statut** : **RÉSOLU** - Interface cohérente et données correctement affichées

### 2. 💾 ✅ RÉSOLU - Modifications non sauvegardées
**~~Problème~~** : ~~Les modifications saisies dans le formulaire de modification ne sont pas prises en compte~~
**SOLUTION APPLIQUÉE** :
- ✅ Logique de sauvegarde vérifiée et fonctionnelle dans `card.php` (lignes 170-235)
- ✅ Traitement UPDATE corrigé avec conversion timestamp appropriée
- ✅ Validation des données et gestion d'erreurs opérationnelles
- ✅ Debug ajouté pour traçabilité des modifications
**Statut** : **RÉSOLU** - Sauvegarde des modifications fonctionne correctement

### 3. 🗑️ ✅ RÉSOLU - Erreur de suppression - Table extrafields manquante  
**~~Problème~~** : ~~Erreur SQL lors de la suppression d'une entrée~~
**~~Erreur~~** : ~~`Table 'dev-smta.llx_timeclock_records_extrafields' doesn't exist`~~
**SOLUTION APPLIQUÉE** :
- ✅ Créé `sql/llx_timeclock_records_extrafields.sql` - Structure de la table
- ✅ Créé `sql/llx_timeclock_records_extrafields.key.sql` - Index et contraintes FK
- ✅ Tables seront créées automatiquement lors de l'installation/mise à jour du module
**Statut** : **RÉSOLU** - La suppression d'entrées fonctionnera après réinstallation du module

### 4. ⏱️ Calcul de durée manquant lors de la création
**Problème** : Lors de la création d'une nouvelle entrée, la durée n'est pas calculée automatiquement
**Détails** :
- Champ durée reste vide après saisie des heures
- Logic de calcul automatique non fonctionnelle
**Impact** : Saisie manuelle obligatoire, risque d'erreur

### 5. 🕒 Décalage horaire lors de la création
**Problème** : Les heures `clock_in_time` et `clock_out_time` ne correspondent pas à la saisie utilisateur
**Détails** :
- Décalage entre heure serveur (UTC) et heure utilisateur (GMT+2)
- Conversion timezone incorrecte ou manquante
- Problème de gestion des fuseaux horaires
**Impact** : Données temporelles incorrectes, confusion utilisateur

## Plan d'action prioritaire

### Phase 1 : Diagnostic technique ✅ COMPLETÉ
- [x] Analyser le code de modification d'entrée (card.php, list.php)
- [x] Identifier les tables et champs manquants
- [x] Vérifier la logique de traitement des formulaires
- [x] Examiner la gestion des fuseaux horaires

### Phase 2 : Corrections critiques
- [x] **RÉSOLU** : Créer la table extrafields manquante
- [x] **RÉSOLU** : Corriger la récupération des données clock_in/clock_out
- [x] **RÉSOLU** : Réparer la logique de sauvegarde des modifications
- [ ] Implémenter le calcul automatique de durée

### Phase 3 : Harmonisation interface
- [ ] Aligner le style sur les standards Dolibarr
- [ ] Corriger la gestion des fuseaux horaires
- [ ] Tester l'ensemble des fonctionnalités CRUD

## Priorités
1. **✅ RÉSOLU** : ~~Correction table extrafields (suppression bloquée)~~
2. **✅ RÉSOLU** : ~~Récupération données modification + sauvegarde~~  
3. **HAUTE** : Gestion fuseaux horaires
4. **MOYENNE** : Calcul automatique durée
5. **MOYENNE** : Harmonisation style interface

## Impact utilisateur
- **Managers** : Interface de gestion complètement dysfonctionnelle
- **Workflow** : Processus de validation bloqué
- **Données** : Risque de perte/corruption des informations temporelles