# AppMobTimeTouch

[![Dolibarr](https://img.shields.io/badge/Dolibarr-Module-blue.svg)](https://www.dolibarr.org)
[![OnsenUI](https://img.shields.io/badge/OnsenUI-Mobile-orange.svg)](https://onsen.io)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)
[![SOLID](https://img.shields.io/badge/Architecture-SOLID-green.svg)](https://en.wikipedia.org/wiki/SOLID)

Module Dolibarr pour la gestion du temps et pointage mobile des employés. Interface mobile-first utilisant OnsenUI avec architecture SOLID moderne.

## 📱 Fonctionnalités

- **Pointage mobile** : Clock-in/Clock-out avec géolocalisation
- **Types de travail** : Bureau, télétravail, mission configurables
- **Suivi temps réel** : Calcul automatique des heures travaillées
- **Résumés** : Journalier et hebdomadaire avec alertes heures sup.
- **Historique** : Consultation des enregistrements récents
- **Responsive** : Interface optimisée mobile/tablette/desktop

## 🏗️ Architecture SOLID

Le module utilise une **architecture SOLID moderne** respectant tous les principes :

### 📁 Structure des Dossiers

```
appmobtimetouch/
├── Constants/                    # Constantes métier (SRP)
│   └── TimeclockConstants.php   # Configuration centralisée
├── Controllers/                  # Contrôleurs MVC (SRP + OCP + DIP)
│   ├── BaseController.php       # Contrôleur de base abstrait
│   └── HomeController.php       # Logique page accueil
├── Helpers/                      # Utilitaires métier (SRP)
│   └── TimeHelper.php          # Calculs temporels
├── Services/                     # Services métier (DIP + ISP)
│   ├── Interfaces/              # Abstractions (DIP)
│   │   ├── TimeclockServiceInterface.php
│   │   └── DataServiceInterface.php
│   ├── TimeclockService.php     # Logique pointage
│   └── DataService.php          # Accès données
├── Views/                        # Templates modulaires (SRP + ISP)
│   └── components/              # Composants réutilisables
│       ├── Messages.tpl         # Affichage messages
│       ├── StatusCard.tpl       # Statut pointage
│       ├── SummaryCard.tpl      # Résumé journalier
│       ├── WeeklySummary.tpl    # Résumé hebdomadaire
│       ├── RecordsList.tpl      # Liste enregistrements
│       ├── ActiveStatus.tpl     # État actif
│       ├── InactiveStatus.tpl   # État inactif
│       ├── ClockInModal.tpl     # Modal pointage entrée
│       └── ClockOutModal.tpl    # Modal pointage sortie
├── class/                        # Entités Dolibarr
├── tpl/                         # Templates principaux
└── home.php                     # Point d'entrée principal
```

### 🎯 Principes SOLID Appliqués

#### **S - Single Responsibility Principle (SRP)**
- **Controllers** : Une responsabilité par contrôleur
- **Services** : Logique métier séparée par domaine
- **Components** : Un composant = une fonctionnalité UI
- **Helpers** : Utilitaires spécialisés

#### **O - Open/Closed Principle (OCP)**
- **Contrôleurs** : Extensibles via nouvelles actions
- **Services** : Ajout de nouveaux services sans modification
- **Templates** : Nouveaux composants sans impact existant

#### **L - Liskov Substitution Principle (LSP)**
- **BaseController** : Substitution transparente des contrôleurs
- **Interfaces** : Implémentations interchangeables

#### **I - Interface Segregation Principle (ISP)**
- **TimeclockServiceInterface** : Interface spécialisée pointage
- **DataServiceInterface** : Interface spécialisée données
- **Composants UI** : Interfaces dédiées par fonctionnalité

#### **D - Dependency Inversion Principle (DIP)**
- **Injection de dépendances** : Services injectés dans contrôleurs
- **Abstractions** : Dépendance sur interfaces, pas implémentations
- **Configuration** : Inversion via constantes externalisées

## 🚀 Installation

### Prérequis
- **Dolibarr** 16.0+
- **PHP** 8.0+
- **MySQL/MariaDB**
- **Navigateur moderne** (support HTML5/CSS3)

### Étapes d'installation

1. **Copier le module**
   ```bash
   cp -r appmobtimetouch/ /var/www/dolibarr/htdocs/custom/
   ```

2. **Activer le module**
   - Aller dans **Administration → Modules**
   - Chercher "AppMobTimeTouch"
   - Cliquer **Activer**

3. **Configuration**
   - Aller dans **RH → TimeTracking → Configuration**
   - Configurer les paramètres selon vos besoins

4. **Permissions**
   - Aller dans **Utilisateurs & Groupes**
   - Attribuer les permissions appropriées

## ⚙️ Configuration

### Paramètres Module

| Paramètre | Description | Défaut |
|-----------|-------------|---------|
| `REQUIRE_LOCATION` | Géolocalisation obligatoire | `0` |
| `MAX_HOURS_DAY` | Maximum heures/jour | `12` |
| `OVERTIME_THRESHOLD` | Seuil heures supplémentaires | `8` |
| `AUTO_BREAK_MINUTES` | Pause automatique (min) | `30` |
| `VALIDATION_REQUIRED` | Validation manager requise | `0` |

### Types de Pointage

Configurez les types dans **Configuration → Types de Pointage** :
- **Bureau** : Travail sur site
- **Télétravail** : Travail à distance  
- **Mission** : Déplacement client
- **Formation** : Sessions de formation

## 📱 Utilisation Mobile

### Interface Principale

L'interface mobile présente :

1. **Statut Pointage**
   - Bouton Clock-in/Clock-out
   - Durée session en cours
   - Type de travail actuel

2. **Résumé Journalier**
   - Heures travaillées aujourd'hui
   - Progression vers objectif
   - Alertes heures supplémentaires

3. **Résumé Hebdomadaire**
   - Total heures semaine
   - Jours travaillés
   - Heures supplémentaires

4. **Enregistrements Récents**
   - 5 dernières sessions
   - Statut et durées
   - Navigation vers détails

### Processus de Pointage

#### Clock-In
1. Appuyer sur **"Pointer Entrée"**
2. Sélectionner **type de travail**
3. Renseigner **localisation** (optionnel)
4. Ajouter **note** (optionnel)
5. Confirmer le pointage

#### Clock-Out
1. Appuyer sur **"Pointer Sortie"**
2. Vérifier **résumé de session**
3. Renseigner **localisation sortie**
4. Ajouter **note de fin**
5. Confirmer la fin

## 🔧 Développement

### Architecture de Développement

Le module suit les **bonnes pratiques PHP modernes** :

#### Structure des Classes

```php
// Services avec injection de dépendances
class TimeclockService implements TimeclockServiceInterface 
{
    public function __construct(
        private DoliDB $db,
        private DataServiceInterface $dataService
    ) {}
}

// Contrôleurs avec responsabilité unique
class HomeController extends BaseController 
{
    public function __construct(
        $db, $user, $langs, $conf,
        TimeclockServiceInterface $timeclockService,
        DataServiceInterface $dataService
    ) {
        parent::__construct($db, $user, $langs, $conf);
        $this->timeclockService = $timeclockService;
        $this->dataService = $dataService;
    }
}
```

#### Composants Templates

```php
<!-- Messages Component (SRP: Affichage messages) -->
<?php include 'Views/components/Messages.tpl'; ?>

<!-- Status Card Component (SRP: Statut timeclock) -->
<?php include 'Views/components/StatusCard.tpl'; ?>

<!-- Modal Components (ISP: Interfaces spécialisées) -->
<?php include 'Views/components/ClockInModal.tpl'; ?>
```

### Tests

#### Tests Unitaires
```bash
cd test/phpunit
phpunit timeclockrecordTest.php
```

#### Tests API
```bash
php test/api_timeclock_test.php
```

#### Tests Fonctionnels
```bash
cd test/phpunit
phpunit AppMobTimeTouchFunctionalTest.php
```

### Base de Données

#### Tables Principales

| Table | Description |
|-------|-------------|
| `llx_timeclock_records` | Enregistrements de pointage |
| `llx_timeclock_types` | Types de travail configurables |
| `llx_timeclock_config` | Configuration du module |
| `llx_timeclock_breaks` | Gestion des pauses |
| `llx_timeclock_weekly_summary` | Résumés hebdomadaires |

#### Schéma Données

```sql
-- Enregistrement de pointage
CREATE TABLE llx_timeclock_records (
    rowid int(11) PRIMARY KEY AUTO_INCREMENT,
    fk_user int(11) NOT NULL,
    clock_in_time datetime NOT NULL,
    clock_out_time datetime NULL,
    work_duration int(11) NULL,
    fk_timeclock_type int(11) NOT NULL,
    status int(11) NOT NULL DEFAULT 2,
    location_in varchar(255) NULL,
    location_out varchar(255) NULL,
    INDEX idx_user_date (fk_user, clock_in_time)
);
```

## 🛡️ Sécurité

### Validation des Données

- **Tokens CSRF** sur tous les formulaires
- **Validation stricte** des entrées utilisateur
- **Échappement HTML** systématique
- **Permissions Dolibarr** respectées

### Géolocalisation

- **Chiffrement** des coordonnées GPS
- **Consentement utilisateur** requis
- **Précision configurable** 
- **Stockage sécurisé** en base

## 📊 Rapports et Exports

### Rapports Disponibles

- **Feuilles de temps** individuelles
- **Synthèses équipes** par manager
- **Exports Excel/PDF** configurables
- **Tableaux de bord** temps réel

### API REST

#### Endpoints Principaux

```http
GET    /api/timeclock/status     # Statut utilisateur actuel
POST   /api/timeclock/clockin    # Pointer entrée
POST   /api/timeclock/clockout   # Pointer sortie
GET    /api/timeclock/records    # Historique enregistrements
GET    /api/timeclock/types      # Types de pointage
```

#### Authentification

```http
Authorization: Bearer <dolibarr_token>
Content-Type: application/json
```

## 🐛 Résolution de Problèmes

### Problèmes Courants

#### Module ne s'active pas
- Vérifier permissions fichiers (755/644)
- Contrôler logs Apache/PHP
- Vérifier version PHP compatible

#### Géolocalisation ne fonctionne pas
- Activer HTTPS (requis pour GPS)
- Vérifier permissions navigateur
- Contrôler configuration `REQUIRE_LOCATION`

#### Données manquantes
- Vérifier structure base de données
- Lancer scripts SQL de mise à jour
- Contrôler permissions utilisateur

### Logs et Debug

#### Activer le Debug
```php
// Dans lib/appmobtimetouch.lib.php
$conf->global->MAIN_FEATURES_LEVEL = 2;
$conf->global->SYSLOG_LEVEL = LOG_DEBUG;
```

#### Emplacements des Logs
- **Dolibarr** : `/var/log/dolibarr/dolibarr.log`
- **Apache** : `/var/log/apache2/error.log`
- **PHP** : `/var/log/php_errors.log`

## 🤝 Contribution

### Guide de Contribution

1. **Fork** le projet
2. **Créer** une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. **Respecter** l'architecture SOLID existante
4. **Ajouter** tests unitaires
5. **Commiter** (`git commit -m 'feat: Nouvelle fonctionnalité'`)
6. **Push** (`git push origin feature/nouvelle-fonctionnalite`)
7. **Créer** Pull Request

### Standards de Code

- **PSR-12** pour le style PHP
- **Architecture SOLID** obligatoire
- **Tests unitaires** pour nouvelle logique métier
- **Documentation** des interfaces publiques

## 📝 Changelog

### Version 1.0.6 (Actuelle)
- ✅ **Architecture SOLID complète** implémentée
- ✅ **Composants modulaires** pour templates
- ✅ **Injection de dépendances** dans contrôleurs
- ✅ **Interfaces ségrégées** pour services
- ✅ **Gestion d'erreurs robuste** 
- ✅ **Documentation complète** mise à jour

### Version 1.0.5
- ✅ Résumé session avec fuseau horaire utilisateur
- ✅ Déplacement boutons clock-out sous résumé
- ✅ Corrections timezone affichage

## 📄 Licence

Ce projet est sous licence **GPL v3+**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 👥 Support

- **Documentation** : Consultez ce README et le dossier `/doc`
- **Issues** : Signalez les problèmes via GitHub Issues
- **Forum** : [Communauté Dolibarr](https://www.dolibarr.org/forum)
- **Email** : Support technique disponible

---

**Développé avec ❤️ pour la communauté Dolibarr**