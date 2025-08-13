# Sprint 44 - Saisie des heures supplémentaires payées

## Analyse du besoin

### Contexte actuel
- Les rapports montrent les heures supplémentaires réalisées : `heures_réelles - heures_théoriques = heures_sup`
- Exemple : 150h réelles vs 140h théoriques = 10h supplémentaires
- Il manque la possibilité pour les managers de saisir les heures supplémentaires **effectivement payées**

### Objectif Sprint 44
En tant que manager, je dois pouvoir saisir simplement le nombre d'heures supplémentaires payées à mes salariés pour un mois donné.

Une fois saisies, les rapports doivent afficher les heures supplémentaires **restantes** :
`(heures_réelles - heures_payées) - heures_théoriques = heures_sup_restantes`

Exemple : (150h - 10h payées) vs 140h théoriques = 0h supplémentaire restante

---

## Plan de développement MVP

### Analyse :
Après étude du code existant, l'approche la plus rapide consiste à :
1. Créer une table dédiée pour stocker les heures supplémentaires payées
2. Ajouter une interface simple de saisie pour les managers
3. Modifier les rapports existants pour intégrer cette donnée

L'architecture SOLID existante permet d'ajouter facilement cette fonctionnalité sans impacter le code existant.

### Découpage en MVPs :

#### **MVP 44.1** : Base de données et modèle (Infrastructure)
- **Fonctionnalité core** : Modification
  -  de la  table `llx_timeclock_overtime_paid` (lire fichier sql/llx_appmobtimetouch_timeclockovertimepaid.sql)
  - Classe PHP `Timeclock_OvertimePaid` avec CRUD standard Dolibarr (lire fichier class/timeclockovertimepaid.class.php)

- **Interface graphique** : 
les pages suivantes ont été créé par module builder. 
dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_agenda.php
dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_card.php
dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_contact.php
dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_document.php
dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_list.php
dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_note.php

il faut ajouter les entrées dans le menu de dolibarr
- **Critères de validation** : 
  - Table créée et accessible
  - accès aux pages depuis dolibarr (timeclockovertimepaid_list.php et timeclockovertimepaid_card.php )
  - Aucune régression sur l'existant

#### **MVP 44.2** : Interface de saisie manager (Fonctionnalité métier)
- **Fonctionnalité core** :
  - Page de saisie des heures supplémentaires payées
  - Validation des données (utilisateur, mois, heures)
  - Intégration avec le système de permissions existant
- **Interface graphique** :
  - Formulaire standard dolibarr  : [Salarié] [Mois/Année] [Heures payées] [Valider]
  - dans le fomulaire de création et modification permettre au manager de selectionner le mois et l'année avec la souris
  - supprimer le champs (ref) et afficher l'id de l'enregistrement en base d donnée à la place. Le champ ID ne doit pas être saisisable ou modifiable par l'utilisateur dans les fomulaire de création et modification 
  - dans le fomulaire de création le champ manager doit automatiquement est selectionné sur l'utilisateur connecté
- **Critères de validation** :
  - Manager peut saisir des heures pour ses équipes
  - Données persistées correctement
  - Interface intuitive et responsive

TODO MVP 44.2 

- gérer la numérotation automatique du champ ref : 
  - lire htdocs/core/modules/facture/mod_facture_terre.php pour s'inspirer 
  - fix problem : quand je cliquer sur le bouton créer: ([Wed Aug 13 17:33:54.076794 2025] [php:error] [pid 627854] [client ::1:56066] PHP Fatal error:  Uncaught Error: Failed opening required '/var/www/html/dev-smta/htdocs/core/class/commonnumrefgenerator.class.php' (include_path='/var/www/html/htdocs') in /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/core/modules/appmobtimetouch/modules_timeclockovertimepaid.php:24\nStack trace:\n#0 /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/core/modules/appmobtimetouch/mod_timeclockovertimepaid_standard.php(24): require_once()\n#1 /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/class/timeclockovertimepaid.class.php(984): include_once('...')\n#2 /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/class/timeclockovertimepaid.class.php(248): TimeclockOvertimePaid->getNextNumRef()\n#3 /var/www/html/dev-smta/htdocs/core/actions_addupdatedelete.inc.php(137): TimeclockOvertimePaid->create()\n#4 /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_card.php(183): include('...')\n#5 {main}\n  thrown in /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/core/modules/appmobtimetouch/modules_timeclockovertimepaid.php on line 24, referer: http://localhost/dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_card.php?action=create&backtopage=%2Fdev-smta%2Fhtdocs%2Fcustom%2Fappmobtimetouch%2Ftimeclockovertimepaid_list.php)

-séparer le champs 'month_year varchar(7) ) dans 2 champs distincts en base de données par un champ pour la valeur du mois et un champ pour la valeur de l'année
  - modifier le fichier /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/sql/llx_appmobtimetouch_timeclockovertimepaid.sql
  - modifier le fichier /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_card.php
  - modifier le fichier /var/www/html/dev-smta/htdocs/custom/appmobtimetouch/timeclockovertimepaid_list.php


#### **MVP 44.3** : Intégration dans les rapports (Valeur métier)
- **Fonctionnalité core** :
  - Méthodes de calcul intégrées dans les rapports
  - Modification des calculs dans `getMonthlyReports()` et `getAnnualReports()`
  - Nouvelle colonne "Heures payées" et "Heures sup. restantes"
  - Conservation de la logique existante pour compatibilité
- **Interface graphique** :
  - Colonnes supplémentaires dans les rapports mensuels/annuels
  - Indicateurs visuels (couleurs) pour les heures restantes
  
- **Critères de validation** :
  - Rapports affichent les heures supplémentaires restantes
  - Calcul correct : (réelles - payées) - théoriques
  

### Points de contrôle MVP :
- **Après MVP 44.1** : Base de données opérationnelle, tests d'insertion/lecture via interface basique
- **Après MVP 44.2** : Managers peuvent saisir et gérer les heures payées via interface dédiée
- **Après MVP 44.3** : Rapports intègrent les heures payées, calcul des heures restantes fonctionnel

### Validation interface :
- **MVP 44.1** : Page de test technique avec formulaire d'insertion simple
- **MVP 44.2** : Interface manager complète intégrée au menu principal
- **MVP 44.3** : Rapports étendus avec nouvelles colonnes et indicateurs visuels

---

### Intégration avec l'existant
- **Permissions** : Utilise `timeclock->readall` et `timeclock->validate` existantes
- **Rapports** : Extension des fonctions `getMonthlyReports()` et `getAnnualReports()`
- **Navigation** : Nouveau menu sous TimeManagement
- **Architecture** : Respect SOLID avec nouveau service `OvertimePaidService`

### Méthode d'implémentation
Cette approche est **la plus rapide** car :
1. ✅ Réutilise l'architecture existante (SOLID, services, templates)
2. ✅ S'appuie sur les patterns Dolibarr standard (CommonObject, actions_addupdatedelete.inc.php)
3. ✅ Exploite les permissions et menus déjà en place
4. ✅ Extension naturelle des rapports existants sans refactoring majeur
5. ✅ Chaque MVP apporte une valeur testable immédiatement

**Estimation** : 3-4h de développement pour les 3 MVPs, testable à chaque étape.