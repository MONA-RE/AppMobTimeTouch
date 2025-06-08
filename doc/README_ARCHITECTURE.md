# Architecture AppMobTimeTouch - Vue d'ensemble

## Objectifs SOLID

Cette architecture respecte strictement les 5 principes SOLID pour garantir maintenabilité, testabilité et évolutivité.

### Principe de Responsabilité Unique (SRP)
- **Controllers/** : Une seule responsabilité par contrôleur
- **Services/** : Logique métier isolée
- **Utils/** : Fonctions utilitaires spécialisées
- **Views/** : Templates avec responsabilité unique

### Principe Ouvert/Fermé (OCP)
- **Interfaces** définies pour chaque service
- **Extension** par nouveaux services sans modification existants
- **Configuration** externalisée pour paramétrage

### Principe de Substitution de Liskov (LSP)
- Tous les services implémentent leurs interfaces
- Contrôleurs substituables via interface commune
- Helpers interchangeables selon besoin

### Principe de Ségrégation d'Interface (ISP)
- Interfaces spécifiques par fonctionnalité
- Templates composés de composants atomiques
- Services découplés par responsabilité

### Principe d'Inversion de Dépendance (DIP)
- Contrôleurs dépendent d'abstractions (interfaces)
- Services injectés via constructeur
- Configuration externalisée

## Structure détaillée

```
Controllers/
├── BaseController.php          # Interface commune, gestion erreurs
├── HomeController.php          # Logique page accueil uniquement
└── AuthController.php          # Authentification Dolibarr isolée

Services/
├── Interfaces/                 # Contrats d'interfaces
│   ├── TimeclockServiceInterface.php
│   ├── DataServiceInterface.php
│   └── AuthServiceInterface.php
├── TimeclockService.php        # Logique métier timeclock
├── DataService.php            # Accès données centralisé
└── AuthService.php            # Service authentification

Utils/
├── TimeHelper.php             # Fonctions conversion temps
├── LocationHelper.php         # Gestion GPS/géolocalisation
└── Constants.php              # Configuration applicative

Views/
├── layouts/main.tpl           # Structure page principale
├── components/                # Composants réutilisables
│   ├── StatusCard.tpl         # Statut clock in/out
│   ├── SummaryCard.tpl        # Résumé journalier/hebdo
│   ├── RecordsList.tpl        # Liste historique
│   ├── ClockInModal.tpl       # Modal pointage entrée
│   └── ClockOutModal.tpl      # Modal pointage sortie
└── pages/home.tpl             # Assemblage composants
```

## Avantages de cette architecture

### Maintenabilité
- **Fichiers 200-500 lignes** : Plus faciles à comprendre et modifier
- **Responsabilités claires** : Chaque fichier a un rôle précis
- **Couplage faible** : Modifications isolées sans effet de bord

### Testabilité
- **Services isolés** : Tests unitaires simples
- **Interfaces mockables** : Tests sans dépendances
- **Logique séparée** : Tests métier indépendants de l'UI

### Évolutivité
- **Nouveaux services** : Ajout sans modification existants
- **Nouvelles vues** : Composants réutilisables
- **Configuration flexible** : Paramètres externalisés

## Points d'Entrée Multiples

⚠️ **CONTRAINTE CRITIQUE** : Le module a **deux points d'entrée** partageant les templates :

### index.php - Interface Mobile
- Build dynamique OnsenUI
- Variables par défaut pour templates
- Point d'entrée principal application mobile

### home.php - Logique Métier  
- Traitement actions (clockin/clockout)
- Données utilisateur réelles
- Include direct templates avec données

### Règle Architecture SOLID
**Tout helper/service utilisé dans templates DOIT être disponible dans les DEUX fichiers.**

📖 **Documentation détaillée** : [INDEX_HOME_COMPATIBILITY.md](INDEX_HOME_COMPATIBILITY.md)

## Migration progressive

La migration suit une approche incrémentale avec validation à chaque étape :

1. **Extraction constantes** → Application stable
2. **Services métier** → Logique testable  
3. **Contrôleurs SOLID** → Actions isolées
4. **Templates modulaires** → UI composable

Chaque étape maintient la compatibilité et permet rollback si nécessaire.
**Validation supplémentaire** : Tests sur index.php ET home.php.