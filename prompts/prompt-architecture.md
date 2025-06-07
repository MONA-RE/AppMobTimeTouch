Contexte
Analyse le repository courant, en particulier les fichiers home.php et tpl/home.tpl, pour proposer un découpage modulaire respectant la logique métier.
Objectifs

Éviter les limitations de tokens en découpant les gros fichiers
Respecter la logique métier avec une architecture claire
Faciliter la maintenance et l'ajout de fonctionnalités
Documenter l'architecture pour une navigation rapide

Instructions détaillées
1. Analyse initiale
bash# Analyser la structure actuelle
find . -name "*.php" -o -name "*.tpl" | head -20
wc -l home.php tpl/home.tpl
Examine les fichiers principaux :

home.php : logique métier, contrôleurs, traitements
tpl/home.tpl : templates, vues, présentation
index.php
(../js/navigation.js)
(../js/timeclock-api.js)
Identifie les dépendances et imports

2. Analyse du contenu
Pour chaque fichier, identifie :

Responsabilités multiples (violations du principe de responsabilité unique)
Blocs de code réutilisables
Logique métier vs logique de présentation
Configuration et constantes
Fonctions utilitaires

3. Proposition d'architecture modulaire
Propose un découpage suivant cette structure :
appmobtimetouch/
├── Controllers/
│   ├── HomeController.php          # Logique principale de home.php
│   └── BaseController.php          # Fonctionnalités communes
├── Services/
│   ├── DataService.php             # Accès aux données
│   ├── AuthService.php             # Authentification
│   └── ValidationService.php       # Validation des données
├── Models/
│   ├── UserModel.php               # Modèles de données
│   └── ContentModel.php
├── Utils/
│   ├── Helpers.php                 # Fonctions utilitaires
│   └── Constants.php               # Constantes globales

├── core
│   ├── actions_appmobtimetouch.php
│   └── modules
│       └── modAppMobTimeTouch.class.php
├── css
│   ├── font_awesome
│   ├── index.css
│   ├── ionicons
│   │   ├── css
│   ├── onsen-css-components.min.css
│   ├── onsenui-core.min.css
│   └── onsenui.min.css
├── doc
│   ├── COMPONENT_MAP.md
│   ├── DEVELOPMENT_GUIDE.md
│   ├── MIGRATION_GUIDE.md
│   ├── README_ARCHITECTURE.md
│   ├── home-php.md
│   ├── home-tpl.md
│   ├── index_php.md
│   ├── js_timeclock-api_js.md
│   ├── lib_appmobtimetouch_lib_php.md

├── Config/
│   └── AppConfig.php               # Configuration
├── index.php
├── js
│   ├── lib
│   ├── navigation.js
│   ├── navigation.js.bkp
│   ├── onsenui.min.js
│   └── timeclock-api.js
├── langs
│   ├── en_US
│   │   └── appmobtimetouch.lang
│   └── fr_FR
│       └── appmobtimetouch.lang
├── lib
│   └── appmobtimetouch.lib.php
├── LICENSE

└── tpl/
    ├── layouts/
    │   └── main.tpl                # Layout principal
    ├── components/
    │   ├── header.tpl              # Composants réutilisables
    │   ├── footer.tpl
    │   └── navigation.tpl
    └── pages/
        └── home.tpl                # Template home refactorisé
├── sql
│   ├── dolibarr_allversions.sql
│   ├── llx_appmobtimetouch_timeclockbreak.key.sql
│   ├── llx_appmobtimetouch_timeclockbreak.sql
│   ├── llx_appmobtimetouch_timeclockconfig.key.sql
│   ├── llx_appmobtimetouch_timeclockconfig.sql
│   ├── llx_appmobtimetouch_timeclockrecord.key.sql
│   ├── llx_appmobtimetouch_timeclockrecord.sql
│   ├── llx_appmobtimetouch_timeclocktype.key.sql
│   ├── llx_appmobtimetouch_timeclocktype.sql
│   ├── llx_appmobtimetouch_weeklysummary.key.sql
│   ├── llx_appmobtimetouch_weeklysummary.sql
│   ├── sql_data_default.sql
│   └── sql_timeclock_keys.sql
├── test
│   ├── api_timeclock_test.php
│   └── phpunit
│       ├── AppMobTimeTouchFunctionalTest.php
│       └── timeclockrecordTest.php


4. Plan de migration
Crée un plan étape par étape :

Extraction des constantes et configuration
Séparation des fonctions utilitaires
Création des services métier
Refactoring du contrôleur principal
Découpage des templates

5. Documentation requise
Génère :

README_ARCHITECTURE.md : Vue d'ensemble de l'architecture
COMPONENT_MAP.md : Cartographie des composants et leurs responsabilités
MIGRATION_GUIDE.md : Guide de migration pas à pas qui respecte l'Approche SOLID stricte et permettre de tester l'application après chaque étape
DEVELOPMENT_GUIDE.md : Guide pour ajouter de nouvelles fonctionnalités

6. Critères de découpage
Respecte ces principes :

Cohésion forte : chaque fichier a une responsabilité claire
Couplage faible : minimise les dépendances entre modules
Taille optimale : 200-500 lignes max par fichier
Réutilisabilité : composants réutilisables dans d'autres parties
Testabilité : code facilement testable

7. Livrables attendus

Analyse détaillée des fichiers actuels
Proposition d'architecture avec justifications
Fichiers refactorisés suivant la nouvelle structure
Documentation complète de l'architecture
Scripts de migration si nécessaire
Guide de développement pour futures modifications

Questions spécifiques à traiter

Quelles sont les principales responsabilités dans home.php ?
Comment séparer la logique métier de la présentation ?
Quels composants peuvent être réutilisés ailleurs ?
Comment organiser les templates pour éviter la duplication ?
Quelle stratégie pour gérer les dépendances entre modules ?

Format de réponse
Structure ta réponse ainsi :

Résumé exécutif (2-3 phrases)
Analyse actuelle (problèmes identifiés)
Architecture proposée (avec diagramme textuel)
Plan de migration (étapes concrètes)
Documentation (fichiers à créer dans le repertoire doc/)
Bénéfices attendus (maintenabilité, évolutivité)

Commence l'analyse maintenant !