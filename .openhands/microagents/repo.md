# AppMobTimeTouch

## Description du Repository

AppMobTimeTouch est un module pour Dolibarr permettant aux salariés de pointer leur temps de présence. Ce module est conçu pour être utilisable depuis un smartphone et utilise le framework OnsenUI pour l'interface utilisateur mobile.

### Objectifs du projet

- Permettre aux salariés de pointer leur temps de présence via une application mobile
- S'intégrer avec le système ERP/CRM Dolibarr
- Offrir une interface utilisateur responsive et intuitive grâce au framework OnsenUI

## Structure du projet

Le projet est structuré comme un module standard Dolibarr avec les répertoires suivants :

- `admin/` : Fichiers d'administration du module (setup.php, about.php)
- `backport/` : Fichiers de compatibilité pour les versions antérieures de Dolibarr
- `core/modules/` : Contient la classe principale du module (modAppMobTimeTouch.class.php)
- `img/` : Images et icônes du module
- `langs/` : Fichiers de traduction (en_US)
- `lib/` : Bibliothèques et fonctions utilitaires
- `sql/` : Scripts SQL pour l'installation et la mise à jour
- `test/` : Tests unitaires et fonctionnels

Fichiers principaux :
- `appmobtimetouchindex.php` : Page d'accueil du module
- `core/modules/modAppMobTimeTouch.class.php` : Définition du module (menus, permissions, etc.)
- `lib/appmobtimetouch.lib.php` : Fonctions utilitaires du module

## Comment exécuter le code

### Prérequis

1. Installation de Dolibarr
   - Télécharger et installer Dolibarr version 11.0 ou supérieure depuis [le site officiel](https://www.dolibarr.org/)
   - Configurer une base de données MySQL/MariaDB
   - PHP 5.6 ou supérieur (PHP 7.x ou 8.x recommandé)
   - Serveur web Apache ou Nginx

2. Installation des dépendances pour le développement frontend
   - Node.js et npm pour le développement frontend
   - OnsenUI pour l'interface utilisateur mobile

### Installation du module

1. Cloner ce repository dans le dossier `htdocs/custom/` de votre installation Dolibarr :
   ```bash
   cd /chemin/vers/dolibarr/htdocs/custom/
   git clone https://github.com/MONA-RE/AppMobTimeTouch.git
   ```

2. Exécuter les scripts SQL d'installation (si nécessaire) :
   ```bash
   # Si vous utilisez la ligne de commande MySQL
   mysql -u username -p dolibarr_db < /chemin/vers/dolibarr/htdocs/custom/AppMobTimeTouch/sql/dolibarr_allversions.sql
   ```
   Alternativement, vous pouvez utiliser l'interface d'administration de Dolibarr pour exécuter les scripts SQL.

3. Activer le module dans l'interface d'administration de Dolibarr :
   - Se connecter à Dolibarr en tant qu'administrateur
   - Aller dans "Accueil > Configuration > Modules/Applications"
   - Rechercher "AppMobTimeTouch" dans la catégorie "Autres modules"
   - Cliquer sur le bouton pour activer le module

4. Configurer le module :
   - Aller dans "Accueil > Configuration > Modules/Applications > AppMobTimeTouch"
   - Configurer les paramètres selon vos besoins

### Développement

Pour travailler sur le développement du module :

1. Configurer l'environnement de développement :
   ```bash
   # Cloner le repository dans le dossier custom de Dolibarr
   cd /chemin/vers/dolibarr/htdocs/custom/
   git clone https://github.com/MONA-RE/AppMobTimeTouch.git
   
   # Si vous utilisez OnsenUI, vous pouvez l'installer via npm
   cd AppMobTimeTouch
   npm install onsenui
   ```

2. Activer le mode développeur dans Dolibarr :
   - Aller dans "Accueil > Configuration > Autre"
   - Activer le mode développeur

3. Accéder au module via l'URL :
   ```
   http://localhost/dolibarr/custom/AppMobTimeTouch/appmobtimetouchindex.php
   ```

### Déploiement en production

Pour déployer en production :

1. S'assurer que tous les fichiers sont correctement transférés sur le serveur :
   ```bash
   # Depuis le serveur de production
   cd /chemin/vers/dolibarr/htdocs/custom/
   git clone https://github.com/MONA-RE/AppMobTimeTouch.git
   
   # Ou si vous mettez à jour une installation existante
   cd /chemin/vers/dolibarr/htdocs/custom/AppMobTimeTouch
   git pull
   ```

2. S'assurer que les permissions des fichiers sont correctement configurées :
   ```bash
   # Ajuster selon l'utilisateur de votre serveur web (www-data, apache, etc.)
   chown -R www-data:www-data /chemin/vers/dolibarr/htdocs/custom/AppMobTimeTouch
   chmod -R 755 /chemin/vers/dolibarr/htdocs/custom/AppMobTimeTouch
   ```

3. Exécuter les scripts SQL de mise à jour si nécessaire :
   ```bash
   mysql -u username -p dolibarr_db < /chemin/vers/dolibarr/htdocs/custom/AppMobTimeTouch/sql/dolibarr_allversions.sql
   ```

4. Activer et configurer le module via l'interface d'administration de Dolibarr comme décrit dans la section Installation.

## Tests

Le module inclut des tests PHPUnit pour vérifier son bon fonctionnement. Pour exécuter les tests :

1. Installer PHPUnit si ce n'est pas déjà fait :
   ```bash
   composer require --dev phpunit/phpunit
   ```

2. Exécuter les tests depuis le répertoire racine de Dolibarr :
   ```bash
   cd /chemin/vers/dolibarr
   ./vendor/bin/phpunit htdocs/custom/AppMobTimeTouch/test/phpunit/AppMobTimeTouchFunctionalTest.php
   ```

## Intégration avec OnsenUI

Ce module utilise le framework OnsenUI pour créer une interface utilisateur mobile responsive. Pour intégrer OnsenUI dans le développement :

1. Installer OnsenUI via npm :
   ```bash
   npm install onsenui
   ```

2. Inclure les fichiers CSS et JavaScript d'OnsenUI dans vos pages :
   ```html
   <!-- Dans votre template HTML -->
   <link rel="stylesheet" href="node_modules/onsenui/css/onsenui.css">
   <link rel="stylesheet" href="node_modules/onsenui/css/onsen-css-components.css">
   <script src="node_modules/onsenui/js/onsenui.js"></script>
   ```

3. Utiliser les composants OnsenUI pour créer votre interface mobile.

## Notes supplémentaires

- Ce module est en cours de développement et n'est pas encore prêt pour la production.
- Le module nécessite Dolibarr version 11.0 ou supérieure.
- Pour les problèmes connus ou les limitations, consultez la section Issues du dépôt GitHub.
- Pour contribuer au projet, veuillez créer une pull request sur le dépôt GitHub.