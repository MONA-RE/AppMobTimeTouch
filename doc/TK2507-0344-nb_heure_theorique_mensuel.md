# TK2507-0344 - Implémentation des Heures Théoriques Mensuelles

## Plan de développement SOLID + MVP

### Analyse :

Cette implémentation suit strictement les principes SOLID pour ajouter la gestion des heures théoriques mensuelles au système AppMobTimeTouch :

- **S - Single Responsibility Principle** : Chaque composant a une responsabilité unique (configuration, affichage, calculs)
- **O - Open/Closed Principle** : Extension du système existant sans modification des classes de base
- **L - Liskov Substitution Principle** : Les nouvelles interfaces respectent les contrats existants
- **I - Interface Segregation Principle** : Interfaces spécifiques pour les nouvelles fonctionnalités
- **D - Dependency Inversion Principle** : Dépendance sur des abstractions pour la configuration

### Identification des MVPs successifs :

Le développement est découpé en 4 MVPs atomiques et testables, chacun apportant une valeur concrète à l'utilisateur final.

## Découpage en MVPs :

### MVP 1 : Configuration système des paramètres théoriques
**Objectif** : Ajouter la capacité de configurer les heures théoriques mensuelles

#### Fonctionnalité core :
- Ajout de la variable `nb_heure_theorique_mensuel` (valeur par défaut : 140 heures)
- Extension de la classe de module pour supporter cette configuration
- Ajout dans setup.php pour interface de configuration Dolibarr

#### Interface graphique :
- Page de configuration dans l'administration Dolibarr
- Champ de saisie pour nb_heure_theorique_mensuel
- Sauvegarde et validation des paramètres

#### Critères de validation :
- ✅ Paramètre sauvegardable via l'interface admin Dolibarr
- ✅ Valeur par défaut de 140 heures appliquée
- ✅ Validation des saisies (nombre positif uniquement)

#### Fichiers modifiés :
- `core/modules/modAppMobTimeTouch.class.php`
- `admin/setup.php`

---

### MVP 2 : Contrôle d'affichage WeekSummary
**Objectif** : Permettre d'activer/désactiver l'affichage du résumé hebdomadaire

#### Fonctionnalité core :
- Ajout du booléen `show_week_summary` dans la configuration du module
- Logique conditionnelle dans home.php pour afficher/masquer WeekSummary
- Interface de configuration dans setup.php

#### Interface graphique :
- Checkbox dans la page de configuration Dolibarr
- Affichage conditionnel du WeekSummary sur la page d'accueil mobile
- Feedback visuel immédiat lors du changement de paramètre

#### Critères de validation :
- ✅ Toggle fonctionnel depuis l'administration
- ✅ WeekSummary visible/invisible selon paramètre
- ✅ Comportement cohérent sur l'interface mobile

#### Fichiers modifiés :
- `core/modules/modAppMobTimeTouch.class.php`
- `admin/setup.php`
- `home.php`

---

### MVP 3 : Template MonthSummary avec calculs théoriques
**Objectif** : Créer l'affichage du résumé mensuel basé sur les heures théoriques

#### Fonctionnalité core :
- Création du template `Views/components/MonthSummary.tpl`
- Calculs de progression basés sur `nb_heure_theorique_mensuel`
- Logique d'affichage similaire à WeekSummary mais pour le mois
- Ajout du booléen `show_month_summary` pour contrôle d'affichage

#### Interface graphique :
- Card mensuelle avec 3 colonnes : Heures Totales, Jours Travaillés, Statut
- Barre de progression basée sur heures théoriques mensuelles
- Code couleur selon l'avancement (vert >100%, orange 80-100%, rouge <80%)
- Gestion des heures supplémentaires avec indicateur visuel

#### Critères de validation :
- ✅ Template MonthSummary fonctionnel et responsive
- ✅ Calculs de pourcentage corrects basés sur nb_heure_theorique_mensuel
- ✅ Interface cohérente avec WeekSummary (mêmes patterns UX)
- ✅ Affichage conditionnel via paramètre de configuration

#### Fichiers créés/modifiés :
- `Views/components/MonthSummary.tpl` (nouveau)
- `core/modules/modAppMobTimeTouch.class.php`
- `admin/setup.php`
- `home.php`

---

### MVP 4 : Enhanced Reports avec colonnes théoriques
**Objectif** : Enrichir les rapports avec analyse théorique vs réel

#### Fonctionnalité core :
- Modification de `reports.php` pour afficher 3 colonnes supplémentaires
- Colonnes : Heures Travaillées, Heures Théoriques, Delta (réel - théorique)
- Calculs automatiques des écarts par utilisateur et par mois
- Code couleur pour les deltas (positif=vert, négatif=rouge)

#### Interface graphique :
- Table enrichie dans l'interface reports
- Code couleur visuel pour les deltas :
  - ✅ Vert : Delta positif (heures supplémentaires)
  - ❌ Rouge : Delta négatif (heures manquantes)
- Totaux et moyennes par équipe
- Filtrage par période avec analyse comparative

#### Critères de validation :
- ✅ 3 colonnes affichées correctement dans les rapports
- ✅ Calculs de delta précis (réel - théorique)
- ✅ Code couleur fonctionnel selon le signe du delta
- ✅ Interface responsive et lisible sur mobile

#### Fichiers modifiés :
- `reports.php`
- `Views/reports/monthly.tpl`
- `Services/DataService.php` (pour nouveaux calculs)

---

## Points de contrôle MVP :

### Après MVP 1 : Configuration de base
**Ce qui doit être testable :**
- Page de configuration accessible via Dolibarr admin
- Paramètre nb_heure_theorique_mensuel sauvegardable
- Valeur par défaut de 140 heures appliquée

### Après MVP 2 : Contrôle WeekSummary  
**Ce qui doit être testable :**
- Checkbox de configuration pour WeekSummary
- Affichage/masquage immédiat sur la page d'accueil mobile
- Persistance du paramètre après redémarrage

### Après MVP 3 : MonthSummary fonctionnel
**Ce qui doit être testable :**
- Template MonthSummary affiché sur home.php
- Calculs de progression basés sur heures théoriques
- Interface cohérente avec WeekSummary existant

### Après MVP 4 : Reports enrichis
**Ce qui doit être testable :**
- Colonnes théoriques visibles dans les rapports
- Code couleur des deltas fonctionnel
- Calculs précis et cohérents avec les données

---

## Validation interface :

### Éléments UI créés à chaque étape :

#### MVP 1 :
- **Page de configuration Dolibarr** : Champ nb_heure_theorique_mensuel
- **Validation** : Saisie numérique avec contrôles

#### MVP 2 :
- **Checkbox** : Affichage WeekSummary (admin)
- **Interface mobile** : WeekSummary conditionnel

#### MVP 3 :
- **Template MonthSummary** : Card responsive avec progression
- **Checkbox** : Affichage MonthSummary (admin)

#### MVP 4 :
- **Table enrichie** : 3 colonnes avec code couleur
- **Interface responsive** : Adaptation mobile/desktop

### Interactions utilisateur possibles :

1. **Administrateur** :
   - Configure les heures théoriques mensuelles
   - Active/désactive WeekSummary et MonthSummary
   - Consulte les rapports enrichis avec analyse théorique

2. **Manager** :
   - Visualise les écarts théorique/réel dans les rapports
   - Analyse les performances d'équipe avec code couleur
   - Exporte les données enrichies

3. **Employé** :
   - Consulte son avancement mensuel via MonthSummary
   - Visualise sa progression par rapport aux objectifs théoriques
   - Interface mobile optimisée

### Feedback visuel pour validation :

- **Code couleur progression** : Rouge/Orange/Vert selon avancement
- **Indicateurs visuels** : Barres de progression avec pourcentages
- **Messages de configuration** : Confirmation de sauvegarde des paramètres
- **Responsive design** : Interface adaptée mobile/desktop

---

## Architecture SOLID Respectée :

### Single Responsibility Principle (SRP) :
- **Configuration** : Classe dédiée aux paramètres
- **Affichage** : Templates séparés (Week/Month)
- **Calculs** : Services spécialisés pour les heures théoriques

### Open/Closed Principle (OCP) :
- **Extension** du système existant sans modification des classes de base
- **Nouveaux templates** sans altération de l'existant
- **Ajout de fonctionnalités** par composition

### Interface Segregation Principle (ISP) :
- **Interfaces spécifiques** pour configuration théorique
- **Séparation** affichage/calcul/sauvegarde

### Dependency Inversion Principle (DIP) :
- **Services abstraits** pour les calculs théoriques
- **Configuration** via interfaces, pas implémentation concrète

---

## Tests de validation :

### Tests unitaires requis :
1. **Configuration** : Sauvegarde/lecture paramètres
2. **Calculs** : Progression mensuelle vs théorique
3. **Affichage** : Templates conditionnels
4. **Reports** : Calculs de delta précis

### Tests fonctionnels requis :
1. **Navigation** : Configuration → Interface mobile
2. **Workflow** : Admin configure → Utilisateur visualise
3. **Responsive** : Interface adaptée tous écrans
4. **Performance** : Calculs rapides même avec gros volumes

Cette implémentation respecte intégralement les principes SOLID et la méthodologie MVP, garantissant un développement incrémental, testable et maintenable.