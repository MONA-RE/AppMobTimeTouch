# Instructions pour Claude Code - Développement SOLID, MVP et Incrémental avec Interface

## Principes de base obligatoires

Pour chaque modification, implémentation ou nouvelle fonctionnalité, tu DOIS respecter les règles suivantes :

### 1. Approche SOLID stricte
Applique systématiquement les 5 principes SOLID :

- **S - Single Responsibility Principle** : Chaque classe/module a une seule responsabilité
- **O - Open/Closed Principle** : Ouvert à l'extension, fermé à la modification
- **L - Liskov Substitution Principle** : Les sous-classes doivent être substituables à leurs classes parentes
- **I - Interface Segregation Principle** : Plusieurs interfaces spécifiques plutôt qu'une interface générale
- **D - Dependency Inversion Principle** : Dépendre d'abstractions, pas de concrétions

### 2. Approche MVP (Minimum Viable Product) obligatoire

**CHAQUE ÉTAPE DOIT PRODUIRE UN MVP TESTABLE :**

- **Fonctionnalité core** : Implémente d'abord la fonctionnalité minimale mais complète
- **Interface utilisable** : Chaque étape doit avoir une interface graphique permettant de tester
- **Validation utilisateur** : L'utilisateur doit pouvoir valider la fonctionnalité via l'interface
- **Valeur ajoutée** : Chaque MVP apporte une valeur concrète et mesurable

### 3. Développement incrémental avec interface

**DÉCOUPE CHAQUE MODIFICATION EN ÉTAPES MVP TESTABLES :**

1. **Analyse préalable** : Explique comment tu vas découper en MVPs successifs
2. **MVPs atomiques** : Chaque MVP doit être fonctionnel et testable via l'interface
3. **Interface graphique** : Créée à chaque étape pour permettre la validation
4. **Point de contrôle** : Après chaque MVP, l'application complète reste fonctionnelle
5. **Tests de non-régression** : Vérifie que les fonctionnalités existantes marchent toujours

### 4. Structure obligatoire de réponse

Pour chaque tâche, commence TOUJOURS par :

```
## Plan de développement SOLID + MVP

### Analyse :
[Explication de l'approche SOLID choisie]
[Identification des MVPs successifs]

### Découpage en MVPs :
1. **MVP 1** : [Fonctionnalité minimale + interface]
   - Fonctionnalité core : [description]
   - Interface graphique : [éléments UI à créer]
   - Critères de validation : [comment tester]

2. **MVP 2** : [Amélioration suivante + interface]
   - Fonctionnalité core : [description]
   - Interface graphique : [éléments UI à ajouter/modifier]
   - Critères de validation : [comment tester]

3. [Etc.]

### Points de contrôle MVP :
- Après MVP 1 : [ce qui doit être testable via l'interface]
- Après MVP 2 : [ce qui doit être testable via l'interface]
- [Etc.]

### Validation interface :
- Éléments UI créés à chaque étape
- Interactions utilisateur possibles
- Feedback visuel pour validation
```

## Règles strictes

### Interdictions
- ❌ **INTERDIT** : Modifications monolithiques
- ❌ **INTERDIT** : Code qui casse les principes SOLID
- ❌ **INTERDIT** : MVPs non testables via l'interface
- ❌ **INTERDIT** : Étapes sans interface graphique
- ❌ **INTERDIT** : Fonctionnalités non validables par l'utilisateur

### Obligations
- ✅ **OBLIGATOIRE** : Découpage en micro-MVPs
- ✅ **OBLIGATOIRE** : Interface graphique à chaque étape
- ✅ **OBLIGATOIRE** : Application testable via UI à chaque étape
- ✅ **OBLIGATOIRE** : Respect strict des principes SOLID
- ✅ **OBLIGATOIRE** : Vérification de non-régression
- ✅ **OBLIGATOIRE** : Critères de validation clairs pour chaque MVP
- ✅ **OBLIGATOIRE** : Possibilité de démonstration utilisateur à chaque étape

## Template de validation MVP

Avant chaque implémentation, réponds à :

### Validation SOLID :
1. "Cette modification respecte-t-elle les 5 principes SOLID ?"
2. "L'architecture reste-t-elle maintenable et extensible ?"

### Validation MVP :
3. "Ce MVP apporte-t-il une valeur concrète testable ?"
4. "L'interface permet-elle de valider la fonctionnalité ?"
5. "L'utilisateur peut-il comprendre et tester cette étape ?"

### Validation Incrémentale :
6. "Les fonctionnalités existantes restent-elles intactes ?"
7. "L'application est-elle stable après cette étape ?"

**Si une réponse est "non", redécoupe l'étape en MVPs plus petits.**

## Spécifications Interface

### Chaque MVP doit inclure :

- **Éléments visuels** : Boutons, formulaires, affichages nécessaires au test
- **Interactions** : Actions utilisateur pour valider la fonctionnalité
- **Feedback** : Retours visuels (messages, animations, états)
- **Navigation** : Liens vers les autres fonctionnalités existantes
- **État** : Indication claire de ce qui fonctionne/ne fonctionne pas encore

### Types d'interfaces MVP :

1. **Interface de démonstration** : Pour montrer la fonctionnalité
2. **Interface de test** : Pour que l'utilisateur puisse tester
3. **Interface de configuration** : Pour paramétrer la fonctionnalité
4. **Interface de monitoring** : Pour suivre le comportement

## Exemple de workflow

```
Tâche : "Ajouter un système de notifications"

MVP 1 : Notification simple
- Code : Service de notification basique
- Interface : Bouton "Tester notification" + zone d'affichage
- Validation : Clic = notification apparaît

MVP 2 : Types de notifications
- Code : Extension avec différents types
- Interface : Sélecteur de type + boutons de test
- Validation : Chaque type s'affiche différemment

MVP 3 : Persistance des notifications
- Code : Sauvegarde des notifications
- Interface : Historique des notifications
- Validation : Les notifications restent après rechargement
```

Cette approche garantit que chaque étape de développement produit un résultat tangible, testable et validable par l'utilisateur final.