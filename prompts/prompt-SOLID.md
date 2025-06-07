# Instructions pour Claude Code - Développement SOLID et Incrémental

## Principes de base obligatoires

Pour chaque modification, implémentation ou nouvelle fonctionnalité, tu DOIS respecter les règles suivantes :

### 1. Approche SOLID stricte
Applique systématiquement les 5 principes SOLID :

- **S - Single Responsibility Principle** : Chaque classe/module a une seule responsabilité
- **O - Open/Closed Principle** : Ouvert à l'extension, fermé à la modification
- **L - Liskov Substitution Principle** : Les sous-classes doivent être substituables à leurs classes parentes
- **I - Interface Segregation Principle** : Plusieurs interfaces spécifiques plutôt qu'une interface générale
- **D - Dependency Inversion Principle** : Dépendre d'abstractions, pas de concrétions

### 2. Développement incrémental obligatoire

**DÉCOUPE CHAQUE MODIFICATION EN ÉTAPES TESTABLES :**

1. **Analyse préalable** : Explique comment tu vas découper la tâche
2. **Étapes atomiques** : Chaque étape doit pouvoir être testée indépendamment
3. **Point de contrôle** : Après chaque étape, l'application doit rester fonctionnelle
4. **Tests de non-régression** : Vérifie que les fonctionnalités existantes marchent toujours

### 3. Structure obligatoire de réponse

Pour chaque tâche, commence TOUJOURS par :

```
## Plan de développement SOLID

### Analyse :
[Explication de l'approche SOLID choisie]

### Découpage en étapes :
1. [Étape 1 - testable]
2. [Étape 2 - testable]
3. [Etc.]

### Points de contrôle :
- Après étape 1 : [ce qui doit fonctionner]
- Après étape 2 : [ce qui doit fonctionner]
- [Etc.]
```

## Règles strictes

- ❌ **INTERDIT** : Modifications monolithiques
- ❌ **INTERDIT** : Code qui casse les principes SOLID
- ❌ **INTERDIT** : Étapes non testables
- ✅ **OBLIGATOIRE** : Découpage en micro-étapes
- ✅ **OBLIGATOIRE** : Application testable à chaque étape
- ✅ **OBLIGATOIRE** : Respect strict des principes SOLID
- ✅ **OBLIGATOIRE** : Vérification de non-régression

## Template de validation

Avant chaque implémentation, réponds à :
1. "Cette modification respecte-t-elle les 5 principes SOLID ?"
2. "Puis-je tester l'application après cette étape ?"
3. "Les fonctionnalités existantes restent-elles intactes ?"

Si une réponse est "non", redécoupe l'étape.